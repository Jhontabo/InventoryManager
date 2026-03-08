<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Loan;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class Reports extends Page
{
    use HasPanelRoleAccess;

    protected static bool $shouldRegisterNavigation = false;

    public array $stats = [];

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = null;

    protected static string | \UnitEnum | null $navigationGroup = null;

    protected static ?string $title = null;

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.reports';

    public static function getNavigationLabel(): string
    {
        return __('panel.pages.reports.navigation');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.admin');
    }

    public function getTitle(): string
    {
        return __('panel.pages.reports.title');
    }

    protected static function canView(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    public function mount(): void
    {
        $this->stats = Cache::remember('reports:quick-stats', 300, fn (): array => [
            'products' => Product::count(),
            'laboratories' => Laboratory::count(),
            'bookings' => Booking::count(),
            'loans' => Loan::count(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label(__('panel.actions.download_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(route('reports.dashboard.download')),

            Action::make('downloadExcel')
                ->label(__('panel.actions.download_excel'))
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(route('reports.excel.download')),
        ];
    }
}
