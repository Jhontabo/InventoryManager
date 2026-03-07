<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanApproved extends Notification
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
            'title' => 'Préstamo Aprobado',
            'body' => "Tu solicitud para {$this->loan->product->name} ha sido aprobada.",
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'success',
        ];
    }
}
