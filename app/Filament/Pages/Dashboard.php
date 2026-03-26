<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BookingByLaboratoryChart;
use App\Filament\Widgets\BookingStatusDonutChart;
use App\Filament\Widgets\LoanStatsWidget;
use App\Filament\Widgets\PendingLoansWidget;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\WeeklyBookingsApexChart;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $widgets = [
            StatsOverview::class,
            BookingStatusDonutChart::class,
            WeeklyBookingsApexChart::class,
        ];

        if ($user->hasAnyRole(['ADMIN', 'COORDINADOR'])) {
            $widgets[] = BookingByLaboratoryChart::class;
        }

        if ($user->hasRole('LABORATORISTA')) {
            $widgets[] = LoanStatsWidget::class;
            $widgets[] = PendingLoansWidget::class;
        }

        return $widgets;
    }

    public function getColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

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
