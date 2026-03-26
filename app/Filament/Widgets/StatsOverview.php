<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    protected ?string $placeholderHeight = '160px';

    protected ?string $heading = 'Resumen de estadísticas';

    protected ?string $description = 'Métricas clave del sistema';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Productos Registrados', cache()->remember('stats-products', 300, fn () => Product::count()))
                ->description('Productos en el inventario')
                ->descriptionIcon('heroicon-o-cube')
                ->color('primary'),

            Stat::make('Laboratorios', cache()->remember('stats-laboratories', 300, fn () => Laboratory::count()))
                ->description('Espacios disponibles')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('warning'),

            Stat::make('Usuarios disponibles', cache()->remember('stats-users-active', 300, fn () => User::where('status', 'active')->count()))
                ->description('Total: '.cache()->remember('stats-users-total', 300, fn () => User::count()))
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),

            Stat::make('Total Reservas', cache()->remember('stats-bookings', 300, fn () => Booking::count()))
                ->description('Total reservas en el sistema')
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('primary'),
        ];
    }
}
