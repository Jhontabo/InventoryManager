<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanRejected extends Notification
{
    use Queueable;

    public function __construct(public Loan $loan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Préstamo Rechazado',
            'body' => "Tu solicitud para {$this->loan->product->name} ha sido rechazada.",
            'icon' => 'heroicon-o-x-circle',
            'iconColor' => 'danger',
        ];
    }
}
