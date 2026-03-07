<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class BookingReportGenerator
{
    private function sanitize($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

            return $value;
        }

        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        return $value;
    }

    public function getRecentBookings(): array
    {
        $recentBookingsRaw = Booking::select([
            'bookings.id',
            'bookings.status',
            'bookings.project_type',
            'bookings.start_at',
            'bookings.end_at',
            'bookings.created_at',
            'laboratories.name as laboratory_name',
            'users.name as user_first_name',
            'users.last_name as user_last_name',
            'users.email as user_email',
        ])
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('laboratories', 'bookings.laboratory_id', '=', 'laboratories.id')
            ->orderBy('bookings.created_at', 'desc')
            ->limit(20)
            ->get();

        $recentBookings = [];
        foreach ($recentBookingsRaw as $booking) {
            $recentBookings[] = [
                'id' => $booking->id,
                'name' => $this->sanitize($booking->user_first_name),
                'last_name' => $this->sanitize($booking->user_last_name),
                'email' => $this->sanitize($booking->user_email),
                'status' => $this->sanitize($booking->status),
                'project_type' => $this->sanitize($booking->project_type),
                'start_at' => $booking->start_at ? Carbon::parse($booking->start_at)->format('d/m/Y H:i') : null,
                'end_at' => $booking->end_at ? Carbon::parse($booking->end_at)->format('d/m/Y H:i') : null,
                'created_at' => Carbon::parse($booking->created_at)->format('d/m/Y H:i'),
                'laboratory_name' => $this->sanitize($booking->laboratory_name),
                'user_name' => $this->sanitize(trim(($booking->user_first_name ?? '').' '.($booking->user_last_name ?? ''))),
            ];
        }

        return $recentBookings;
    }

    public function getPendingBookings(): array
    {
        $pendingBookings = Booking::with('user:id,name,last_name,email')
            ->where('status', Booking::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingBookingsArray = [];
        foreach ($pendingBookings as $booking) {
            $pendingBookingsArray[] = [
                'id' => $booking->id,
                'name' => $this->sanitize($booking->name),
                'last_name' => $this->sanitize($booking->last_name),
                'email' => $this->sanitize($booking->email),
                'project_type' => $this->sanitize($booking->project_type),
                'start_at' => $booking->start_at ? Carbon::parse($booking->start_at)->format('d/m/Y H:i') : null,
                'created_at' => Carbon::parse($booking->created_at)->format('d/m/Y H:i'),
            ];
        }

        return $pendingBookingsArray;
    }
}
