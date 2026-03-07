<?php

namespace App\Filament\Resources\ReservationHistoriesResource\Pages;

use App\Filament\Resources\ReservationHistoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservationHistory extends EditRecord
{
    protected static string $resource = ReservationHistoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
