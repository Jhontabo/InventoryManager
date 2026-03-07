<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingApproved extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reserva Aprobada',
            'body' => "Tu reserva para el laboratorio {$this->booking->laboratory->name} fue aprobada.",
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'success',
        ];
    }
}
