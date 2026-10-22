<?php

namespace App\Notifications;

use App\Models\DigitalCard;
use App\Models\User;
use App\Services\DigitalCardImageRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DigitalCardReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DigitalCard $card,
        public User $sender,
        public bool $viaEmail = false,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->viaEmail ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'digital_card_received',
            'card_reference' => $this->card->reference,
            'card_last4'     => substr($this->card->card_number, -4),
            'amount'         => (float) $this->card->amount,
            'sender_id'      => $this->sender->id,
            'sender_name'    => $this->sender->name,
            'message'        => "You have received a new card from {$this->sender->name}.",
        ];
    }

    /**
     * The email carries the card image with the PIN hidden. The email is only
     * a notice: the card can be used solely from the recipient's portal.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $png = app(DigitalCardImageRenderer::class)->render($this->card, showPin: false);

        return (new MailMessage)
            ->subject("You have received a digital card from {$this->sender->name}")
            ->view('emails.digital-card-received', [
                'name'      => $notifiable->name ?? $notifiable->username,
                'sender'    => $this->sender->name . ' (@' . $this->sender->username . ')',
                'amount'    => '$' . number_format((float) $this->card->amount, 2),
                'masked'    => $this->card->maskedNumber(),
                'expiresAt' => $this->card->expires_at->format('d M Y'),
                'url'       => route('cards.show', $this->card),
            ])
            ->attachData($png, 'digital-card-' . substr($this->card->card_number, -4) . '.png', ['mime' => 'image/png']);
    }
}
