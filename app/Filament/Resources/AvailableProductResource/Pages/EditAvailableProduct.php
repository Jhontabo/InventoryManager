<?php

namespace App\Filament\Resources\AvailableProductResource\Pages;

use App\Filament\Resources\AvailableProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAvailableProduct extends EditRecord
{
    protected static string $resource = AvailableProductResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
