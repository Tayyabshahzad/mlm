<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DigitalCardException;
use App\Http\Controllers\Controller;
use App\Models\DigitalCard;
use App\Models\DigitalCardEvent;
use App\Services\DigitalCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DigitalCardAdminController extends Controller
{
    public function __construct(private DigitalCardService $cards) {}

    public function index(Request $request)
    {
        $query = DigitalCard::with(['generator:id,name,username', 'recipient:id,name,username', 'instalment:id,instalment_number'])
            ->latest('id');

        if ($search = trim((string) $request->get('search'))) {
            $digits = preg_replace('/\D/', '', $search);
            $query->where(function ($q) use ($search, $digits) {
                $q->where('reference', $search);
                if (strlen($digits) >= 4) {
                    $q->orWhere('card_number', 'like', "%{$digits}%");
                }
            });
        }
        if ($generator = trim((string) $request->get('generator'))) {
            $query->whereHas('generator', fn ($q) => $q->where('username', 'like', "%{$generator}%")->orWhere('name', 'like', "%{$generator}%"));
        }
        if ($recipient = trim((string) $request->get('recipient'))) {
            $query->whereHas('recipient', fn ($q) => $q->where('username', 'like', "%{$recipient}%")->orWhere('name', 'like', "%{$recipient}%"));
        }
        if (in_array($status = $request->get('status'), DigitalCard::STATUSES, true)) {
            $query->where('status', $status);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $cards = $query->paginate(25)->withQueryString();

        $summary = DigitalCard::selectRaw('status, COUNT(*) as total, SUM(amount) as amount')
            ->groupBy('status')->get()->keyBy('status');

        $failedLast7 = DigitalCardEvent::where('event', DigitalCardEvent::VERIFICATION_FAILED)
            ->where('created_at', '>=', now()->subDays(7))->count();

        return view('admin.cards.index', compact('cards', 'summary', 'failedLast7'));
    }

    public function show(DigitalCard $card)
    {
        $card->load([
            'generator:id,name,username,email', 'recipient:id,name,username,email', 'sharer:id,name,username',
            'canceller:id,name,username', 'instalment.user:id,name,username', 'generationWallet', 'refundWallet',
        ]);

        return view('admin.cards.show', [
            'card'    => $card,
            'history' => $this->cards->historyFor($card, Auth::user()),
            'pinLock' => $this->cards->pinLockStatus($card),
        ]);
    }

    public function resetPinLock(Request $request, DigitalCard $card): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $this->cards->resetPinLock($card, Auth::user(), $request->reason);

        return back()->with('success', 'PIN lock reset. The recipient can try the PIN again now.');
    }

    public function cancel(Request $request, DigitalCard $card): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->cards->adminCancel($card, Auth::user(), $request->reason);
        } catch (DigitalCardException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Card cancelled and refunded to the generator.');
    }

    public function failedAttempts(Request $request)
    {
        $events = DigitalCardEvent::with(['card:id,reference,card_number,recipient_id,generated_by', 'actor:id,name,username'])
            ->where('event', DigitalCardEvent::VERIFICATION_FAILED)
            ->when($request->get('user'), fn ($q, $u) => $q->whereHas('actor', fn ($a) => $a->where('username', 'like', "%{$u}%")))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.cards.failed-attempts', compact('events'));
    }

    public function runExpiry(): RedirectResponse
    {
        $count = $this->cards->expireDueCards();

        return back()->with('success', "{$count} card(s) expired and refunded.");
    }
}
