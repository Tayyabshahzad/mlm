<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public float  $amount,
        public float  $fee,
        public string $requestType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name    = $notifiable->name ?? $notifiable->username;
        $net     = round($this->amount - $this->fee, 2);

        return (new MailMessage)
            ->subject('Withdrawal Request Submitted — Global Visioners International')
            ->view('emails.withdrawal-submitted', [
                'name'        => $name,
                'username'    => $notifiable->username,
                'amount'      => '$' . number_format($this->amount, 2),
                'fee'         => '$' . number_format($this->fee, 2),
                'net'         => '$' . number_format($net, 2),
                'requestType' => ucfirst(str_replace('_', ' ', $this->requestType)),
            ]);
    }
}
