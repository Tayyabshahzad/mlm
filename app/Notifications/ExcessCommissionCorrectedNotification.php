<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExcessCommissionCorrectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public float $excessAmount,
        public float $currentBalance,
        public string $periodFrom,
        public string $periodTo,
        public int    $withdrawalCount,
        public float  $totalWithdrawn,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name    = $notifiable->name ?? $notifiable->username;
        $excess  = '$' . number_format($this->excessAmount, 2);
        $balance = '$' . number_format($this->currentBalance, 2);

        return (new MailMessage)
            ->subject('Account Balance Adjustment — Global Visioners International')
            ->view('emails.excess-commission-correction', [
                'name'             => $name,
                'username'         => $notifiable->username,
                'excessAmount'     => $excess,
                'currentBalance'   => $balance,
                'periodFrom'       => $this->periodFrom,
                'periodTo'         => $this->periodTo,
                'withdrawalCount'  => $this->withdrawalCount,
                'totalWithdrawn'   => '$' . number_format($this->totalWithdrawn, 2),
            ]);
    }
}
