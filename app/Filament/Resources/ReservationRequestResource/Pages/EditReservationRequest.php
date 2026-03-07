<?php

namespace App\Filament\Resources\ReservationRequestResource\Pages;

use App\Filament\Resources\ReservationRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservationRequest extends EditRecord
{
    protected static string $resource = ReservationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
