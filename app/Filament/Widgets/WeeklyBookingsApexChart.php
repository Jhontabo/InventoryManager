<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class WeeklyBookingsApexChart extends ApexChartWidget
{
    protected static ?string $chartId = 'weeklyBookingsApexChart';

    protected static ?string $heading = 'Reservas por semana';

    protected static ?int $sort = 5;

    protected string|int|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    protected function getOptions(): array
    {
        return cache()->remember('weekly-bookings-apex-chart', 300, function (): array {
            $start = now()->startOfWeek()->subWeeks(7);
            $end = now()->endOfWeek();

            $bookings = Booking::query()
                ->whereBetween('created_at', [$start, $end])
                ->get(['created_at']);

            $weeklyTotals = [];
            for ($i = 0; $i < 8; $i++) {
                $weekStart = $start->copy()->addWeeks($i)->startOfWeek();
                $key = $weekStart->toDateString();
                $weeklyTotals[$key] = 0;
            }

            foreach ($bookings as $booking) {
                $weekKey = Carbon::parse($booking->created_at)->startOfWeek()->toDateString();
                if (array_key_exists($weekKey, $weeklyTotals)) {
                    $weeklyTotals[$weekKey]++;
                }
            }

            $categories = [];
            $series = [];

            foreach ($weeklyTotals as $weekStart => $count) {
                $startDate = Carbon::parse($weekStart);
                $endDate = $startDate->copy()->endOfWeek();
                $categories[] = $startDate->format('d M').' - '.$endDate->format('d M');
                $series[] = $count;
            }

            return [
                'chart' => [
                    'type' => 'area',
                    'height' => 320,
                    'toolbar' => [
                        'show' => false,
                    ],
                ],
                'series' => [
                    [
                        'name' => 'Reservas',
                        'data' => $series,
                    ],
                ],
                'xaxis' => [
                    'categories' => $categories,
                    'labels' => [
                        'rotate' => -20,
                    ],
                ],
                'yaxis' => [
                    'min' => 0,
                    'forceNiceScale' => true,
                ],
                'stroke' => [
                    'curve' => 'smooth',
                    'width' => 3,
                ],
                'fill' => [
                    'type' => 'gradient',
                    'gradient' => [
                        'shadeIntensity' => 1,
                        'opacityFrom' => 0.4,
                        'opacityTo' => 0.08,
                        'stops' => [0, 90, 100],
                    ],
                ],
                'colors' => ['#2563eb'],
                'dataLabels' => [
                    'enabled' => false,
                ],
                'grid' => [
                    'strokeDashArray' => 4,
                ],
                'tooltip' => [
                    'y' => [
                        'formatter' => 'function (value) { return value + " reservas"; }',
                    ],
                ],
            ];
        });
    }
}
