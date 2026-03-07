<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingRejected extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reason = $this->booking->rejection_reason ? " Motivo: {$this->booking->rejection_reason}" : '';

        return [
            'title' => 'Reserva Rechazada',
            'body' => "Tu reserva para el laboratorio {$this->booking->laboratory->name} fue rechazada.{$reason}",
            'icon' => 'heroicon-o-x-circle',
            'iconColor' => 'danger',
        ];
    }
}
