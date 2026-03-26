<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LoanStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    protected ?string $placeholderHeight = '160px';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('LABORATORISTA') ?? false;
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('LABORATORISTA')) {
            return [];
        }

        $stats = cache()->remember('loan-stats-widget-'.now()->format('Y-m-d-H'), 300, function (): array {
            return [
                'pending' => Loan::where('status', Loan::STATUS_PENDING)->count(),
                'approved' => Loan::where('status', Loan::STATUS_APPROVED)->count(),
                'overdue' => Loan::where('status', Loan::STATUS_APPROVED)
                    ->where('estimated_return_at', '<', now())
                    ->count(),
                'returned' => Loan::where('status', Loan::STATUS_RETURNED)
                    ->whereMonth('actual_return_at', now()->month)
                    ->count(),
            ];
        });

        $pending = $stats['pending'];
        $approved = $stats['approved'];
        $overdue = $stats['overdue'];
        $returned = $stats['returned'];

        return [
            Stat::make('Pendientes de Aprobación', $pending)
                ->description('Préstamos esperando respuesta')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pending > 0 ? 'warning' : 'success')
                ->chart([$pending, $approved, $overdue]),

            Stat::make('Préstamos Activos', $approved)
                ->description('Equipos en uso')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Vencidos', $overdue)
                ->description('Devoluciones pendientes')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'gray'),

            Stat::make('Devueltos este mes', $returned)
                ->description('Préstamos completados')
                ->descriptionIcon('heroicon-o-archive-box')
                ->color('success'),
        ];
    }
}
