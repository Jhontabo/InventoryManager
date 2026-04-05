<?php

namespace App\Filament\Resources\ReservationRequestResource\Pages;

use App\Filament\Resources\ReservationRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewReservationRequest extends ViewRecord
{
    protected static string $resource = ReservationRequestResource::class;

    protected string $view = 'filament.pages.view-reservation-request';
}
