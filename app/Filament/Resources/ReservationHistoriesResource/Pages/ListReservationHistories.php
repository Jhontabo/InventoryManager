<?php

namespace App\Filament\Resources\ReservationHistoriesResource\Pages;

use App\Filament\Resources\ReservationHistoriesResource;
use Filament\Resources\Pages\ListRecords;

class ListReservationHistories extends ListRecords
{
    protected static string $resource = ReservationHistoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
