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

    protected static ?string $navigationLabel = 'Reportes';

    protected static string | \UnitEnum | null $navigationGroup = 'Administración';

    protected static ?string $title = 'Reportes del Sistema';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.reports';

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
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(route('reports.dashboard.download')),

            Action::make('downloadExcel')
                ->label('Descargar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->url(route('reports.excel.download')),
        ];
    }
}
