<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\Page;

class ViewCalendar extends Page
{
    protected static string $resource = BookingResource::class;

    // Blade que renderiza SOLO el calendario
    protected string $view = 'filament.pages.booking-calendar';

    protected static ?string $title = 'Vista de calendario';
}
