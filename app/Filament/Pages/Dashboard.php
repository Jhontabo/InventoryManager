<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdfReport')
                ->label(__('panel.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->visible(fn (): bool => Reports::canDownloadReports(auth()->user()))
                ->url(route('reports.dashboard.download')),

            Action::make('downloadExcelReport')
                ->label(__('panel.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn (): bool => Reports::canDownloadReports(auth()->user()))
                ->url(route('reports.excel.download')),
        ];
    }
}
