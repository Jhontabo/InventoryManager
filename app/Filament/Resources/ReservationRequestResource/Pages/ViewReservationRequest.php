<?php

namespace App\Filament\Resources\ReservationRequestResource\Pages;

use Filament\Resources\Pages\ViewRecord;

class ViewReservationRequest extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\ReservationRequestResource::class;

    protected string $view = 'filament.pages.view-reservation-request';
}
