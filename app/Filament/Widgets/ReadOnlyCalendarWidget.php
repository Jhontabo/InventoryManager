<?php

namespace App\Filament\Widgets;

use App\Models\Schedule;
use Carbon\Carbon;

class ReadOnlyCalendarWidget extends CalendarWidget
{
    public ?int $laboratoryId = null;

    public function mount(): void
    {
        $this->laboratoryId = request()->query('laboratory') ? (int) request()->query('laboratory') : null;
    }

    public static function canView(): bool
    {
        if (request()->routeIs('filament.pages.dashboard')) {
            return false;
        }

        return parent::canView();
    }

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }

    public function config(): array
    {
        return array_merge(parent::config(), [
            'selectable' => false,
            'editable' => false,
            'eventClick' => null,
            'eventDrop' => null,
            'eventResize' => null,
            'hiddenDays' => [0, 6],
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
        ]);
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $start = Carbon::parse($fetchInfo['start']);
        $end = Carbon::parse($fetchInfo['end']);

        $laboratoryId = $this->laboratoryId;

        $cacheKey = sprintf(
            'readonly-calendar-events:%s:%s:%s',
            $laboratoryId ?? 'all',
            $start->toDateString(),
            $end->toDateString(),
        );

        return cache()->remember($cacheKey, 120, function () use ($start, $end, $laboratoryId): array {
            $query = Schedule::query()
                ->select(['id', 'title', 'start_at', 'end_at', 'laboratory_id'])
                ->with('laboratory:id,name')
                ->withCount(['booking as approved_bookings_count' => fn ($bookingQuery) => $bookingQuery->where('status', 'approved')])
                ->where('type', 'unstructured')
                ->whereBetween('start_at', [$start, $end]);

            if ($laboratoryId) {
                $query->where('laboratory_id', $laboratoryId);
            }

            return $query->get()
                ->map(function (Schedule $schedule): array {
                    $hasApprovedBooking = (int) ($schedule->approved_bookings_count ?? 0) > 0;

                    return [
                        'id' => $schedule->id,
                        'title' => $hasApprovedBooking
                            ? 'Ocupado: '.($schedule->laboratory->name ?? '')
                            : 'Libre: '.($schedule->laboratory->name ?? ''),
                        'start' => $schedule->start_at,
                        'end' => $schedule->end_at,
                        'backgroundColor' => $hasApprovedBooking ? '#ef4444' : '#22c55e',
                        'borderColor' => $hasApprovedBooking ? '#dc2626' : '#16a34a',
                        'textColor' => '#ffffff',
                    ];
                })
                ->all();
        });
    }
}
