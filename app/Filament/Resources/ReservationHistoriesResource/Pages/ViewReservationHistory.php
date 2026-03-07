<?php

namespace App\Filament\Resources\ReservationHistoriesResource\Pages;

use Filament\Resources\Pages\ViewRecord;

class ViewReservationHistory extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\ReservationHistoriesResource::class;

    protected string $view = 'filament.pages.view-reservation-history';
}
