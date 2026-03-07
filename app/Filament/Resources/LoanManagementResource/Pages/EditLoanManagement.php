<?php

namespace App\Filament\Resources\LoanManagementResource\Pages;

use App\Filament\Resources\LoanManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoanManagement extends EditRecord
{
    protected static string $resource = LoanManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
