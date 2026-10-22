<?php

namespace App\Http\Controllers;

use App\Exceptions\DigitalCardException;
use App\Models\DigitalCard;
use App\Models\User;
use App\Notifications\DigitalCardReceivedNotification;
use App\Services\DigitalCardImageRenderer;
use App\Services\DigitalCardService;
use App\Support\DigitalCardSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DigitalCardController extends Controller
{
    public function __construct(
        private DigitalCardService $cards,
        private DigitalCardImageRenderer $renderer,
    ) {}

    // =========================================================================
    // My Cards — generate + list cards I generated
    // =========================================================================

    public function index()
    {
        $user = Auth::user();

        $generated = DigitalCard::with(['recipient:id,name,username', 'instalment:id,instalment_number'])
            ->where('generated_by', $user->id)
            ->latest('id')
            ->paginate(15);

        $stats = DigitalCard::where('generated_by', $user->id)
            ->selectRaw('status, COUNT(*) as total, SUM(amount) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('cards.index', [
            'generated'  => $generated,
            'stats'      => $stats,
            'balance'    => $this->cards->onlineBalance($user->id),
            'minAmount'  => DigitalCardSettings::minAmount(),
            'maxAmount'  => DigitalCardSettings::maxAmount(),
            'expiryDays' => DigitalCardSettings::expiryDays(),
            'enabled'    => DigitalCardSettings::enabled(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|decimal:0,2',
        ]);

        try {
            $card = $this->cards->generate(Auth::user(), (float) $request->amount);
        } catch (DigitalCardException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Digital card generation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Card generation failed. No amount was deducted.');
        }

        return redirect()->route('cards.show', $card)
            ->with('success', 'Card generated successfully. $' . number_format((float) $card->amount, 2) . ' was deducted from your online wallet.');
    }

    // =========================================================================
    // Received Cards
    // =========================================================================

    public function received()
    {
        $user = Auth::user();

        $cards = DigitalCard::with(['generator:id,name,username', 'sharer:id,name,username', 'instalment:id,instalment_number'])
            ->where('recipient_id', $user->id)
            ->latest('shared_at')
            ->paginate(15);

        $unreadRefs = $user->unreadNotifications()
            ->where('type', DigitalCardReceivedNotification::class)
            ->get()
            ->pluck('data.card_reference')
            ->all();

        // Visiting the page acknowledges the "new card" alerts.
        $user->unreadNotifications()
            ->where('type', DigitalCardReceivedNotification::class)
            ->update(['read_at' => now()]);

        return view('cards.received', compact('cards', 'unreadRefs'));
    }

    // =========================================================================
    // Card detail, image, PDF
    // =========================================================================

    public function show(DigitalCard $card)
    {
        $user = Auth::user();
        abort_unless($this->cards->canView($user, $card), 404);

        $card->load(['generator:id,name,username', 'recipient:id,name,username', 'sharer:id,name,username', 'instalment']);

        $isGenerator = (int) $card->generated_by === (int) $user->id;
        $isRecipient = (int) $card->recipient_id === (int) $user->id;

        $nextInstalment = null;
        $requiredAmount = null;
        if ($isRecipient && $card->isUsable() && $this->cards->userHasSavingPlan($user)) {
            $nextInstalment = app(\App\Services\SavingAccountService::class)->nextDueInstalment($user);
            $requiredAmount = $nextInstalment
                ? app(\App\Services\SavingAccountService::class)->requiredInstalmentTotal($nextInstalment)
                : null;
        }

        return view('cards.show', [
            'card'           => $card,
            'history'        => $this->cards->historyFor($card, $user),
            'isGenerator'    => $isGenerator,
            'isRecipient'    => $isRecipient,
            'canShare'       => $isGenerator && $card->status === DigitalCard::STATUS_AVAILABLE && !$card->isPastExpiry(),
            'canCancel'      => $isGenerator && $card->status === DigitalCard::STATUS_AVAILABLE,
            'nextInstalment' => $nextInstalment,
            'requiredAmount' => $requiredAmount,
        ]);
    }

    public function image(Request $request, DigitalCard $card)
    {
        $user = Auth::user();
        abort_unless($this->cards->canView($user, $card), 404);

        $png      = $this->renderer->render($card, showPin: $this->maySeePin($user, $card));
        $download = $request->boolean('download');

        if ($download) {
            $this->cards->recordDownload($card, $user, 'png');
        }

        return response($png, 200, [
            'Content-Type'        => 'image/png',
            'Cache-Control'       => 'no-store, private',
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="digital-card-' . substr($card->card_number, -4) . '.png"',
        ]);
    }

    public function pdf(DigitalCard $card)
    {
        $user = Auth::user();
        abort_unless($this->cards->canView($user, $card), 404);

        $card->load(['generator:id,name,username', 'recipient:id,name,username']);
        $png = $this->renderer->render($card, showPin: $this->maySeePin($user, $card));

        $this->cards->recordDownload($card, $user, 'pdf');

        return Pdf::loadView('cards.pdf', [
            'card'  => $card,
            'image' => 'data:image/png;base64,' . base64_encode($png),
        ])->setPaper('a4')->download('digital-card-' . substr($card->card_number, -4) . '.pdf');
    }

    private function maySeePin(User $user, DigitalCard $card): bool
    {
        return (int) $card->generated_by === (int) $user->id
            || ($card->recipient_id !== null && (int) $card->recipient_id === (int) $user->id);
    }

    // =========================================================================
    // Share / cancel
    // =========================================================================

    public function recipients(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        $query = $this->cards->eligibleRecipientsQuery(Auth::user())->select('id', 'name', 'username');
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('username', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%"));
        }

        return response()->json([
            'results' => $query->orderBy('name')->limit(25)->get()->map(fn ($u) => [
                'id'   => $u->id,
                'text' => "{$u->name} (@{$u->username})",
            ]),
        ]);
    }

    public function share(Request $request, DigitalCard $card): RedirectResponse
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'method'       => 'required|in:portal,email',
        ]);

        $sender = Auth::user();
        abort_unless((int) $card->generated_by === (int) $sender->id, 404);

        try {
            $this->cards->share($card, $sender, User::findOrFail($request->recipient_id), $request->method);
        } catch (DigitalCardException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Card shared successfully. The recipient has been notified.');
    }

    public function cancel(DigitalCard $card): RedirectResponse
    {
        $user = Auth::user();
        abort_unless((int) $card->generated_by === (int) $user->id, 404);

        try {
            $this->cards->cancelByGenerator($card, $user);
        } catch (DigitalCardException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Card cancelled and $' . number_format((float) $card->amount, 2) . ' refunded to your online wallet.');
    }

    // =========================================================================
    // Pay an instalment with a received card
    // =========================================================================

    public function pay(Request $request): RedirectResponse
    {
        $request->validate([
            'card_reference' => 'required|string|max:40',
            'pin'            => 'required|digits:4',
        ]);

        try {
            $instalment = $this->cards->payInstalment(Auth::user(), $request->card_reference, $request->pin);
        } catch (DigitalCardException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Digital card payment failed: ' . $e->getMessage());
            return back()->with('error', 'Payment could not be processed. Your card was not used.');
        }

        return redirect()->route('saving.user.instalments')
            ->with('success', "Instalment #{$instalment->instalment_number} submitted with your digital card. It will be finalised after admin confirmation.");
    }
}
