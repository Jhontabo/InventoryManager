<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Filament\Widgets\CalendarWidget;
use App\Models\Laboratory;
use Filament\Resources\Pages\Page;

class ScheduleCalendar extends Page
{
    protected static string $resource = ScheduleResource::class;

    protected string $view = 'filament.pages.calendar';

    protected static ?string $title = 'Gestión de Horarios';

    public ?int $laboratoryId = null;

    public function mount()
    {
        $labParam = request()->query('laboratory');
        $this->laboratoryId = is_numeric($labParam) ? (int) $labParam : null;
        session()->put('lab', $this->laboratoryId);
    }

    public function getFooterWidgets(): array
    {
        return [CalendarWidget::class];
    }

    public function getDropdownOptions(): array
    {
        $laboratories = cache()->remember('schedule-calendar-laboratories', 1800, fn (): array => Laboratory::orderBy('name')
            ->pluck('name', 'id')
            ->toArray());

        return ['All' => 'Todos los laboratorios'] + $laboratories;
    }
}
