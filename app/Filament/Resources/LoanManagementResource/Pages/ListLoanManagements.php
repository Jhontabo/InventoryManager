<?php

namespace App\Filament\Resources\LoanManagementResource\Pages;

use App\Filament\Resources\LoanManagementResource;
use Filament\Resources\Pages\ListRecords;

class ListLoanManagements extends ListRecords
{
    protected static string $resource = LoanManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
