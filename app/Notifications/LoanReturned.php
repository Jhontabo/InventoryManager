<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanReturned extends Notification
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
            'title' => 'Préstamo Devuelto',
            'body' => "El préstamo de {$this->loan->product->name} se registró como devuelto.",
            'icon' => 'heroicon-o-archive-box-arrow-down',
            'iconColor' => 'info',
        ];
    }
}
