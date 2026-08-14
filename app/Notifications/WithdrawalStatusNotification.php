<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public float  $amount,
        public string $status,   // 'approved' or 'rejected'
        public string $requestType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name       = $notifiable->name ?? $notifiable->username;
        $isApproved = $this->status === 'approved';
        $subject    = $isApproved
            ? 'Withdrawal Approved — Global Visioners International'
            : 'Withdrawal Request Rejected — Global Visioners International';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.withdrawal-status', [
                'name'        => $name,
                'username'    => $notifiable->username,
                'amount'      => '$' . number_format($this->amount, 2),
                'status'      => $this->status,
                'isApproved'  => $isApproved,
                'requestType' => ucfirst(str_replace('_', ' ', $this->requestType)),
            ]);
    }
}
