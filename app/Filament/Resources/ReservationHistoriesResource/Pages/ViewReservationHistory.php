<?php

namespace App\Filament\Resources\ReservationHistoriesResource\Pages;

use App\Filament\Resources\ReservationHistoriesResource;
use Filament\Resources\Pages\ViewRecord;

class ViewReservationHistory extends ViewRecord
{
    protected static string $resource = ReservationHistoriesResource::class;

    protected string $view = 'filament.pages.view-reservation-history';
}
