<?php

namespace App\Filament\Resources\ReservationRequestResource\Pages;

use App\Filament\Resources\ReservationRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListReservationRequests extends ListRecords
{
    protected static string $resource = ReservationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
