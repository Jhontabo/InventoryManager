<?php

namespace App\Filament\Widgets;

use App\Models\AcademicProgram;
use App\Models\Laboratory;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model|string|null $model = Schedule::class;

    public ?int $laboratoryId = null;

    public function mount(): void
    {
        $this->laboratoryId = session('lab');
    }

    public static function canView(): bool
    {
        if (request()->routeIs('filament.admin.pages.dashboard')) {
            return false;
        }

        return Auth::check() && Auth::user()->hasAnyRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '17:00:00',
            'locale' => 'es',
            'initialView' => 'timeGridWeek',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'eventOverlap' => true,
            'slotEventOverlap' => true,
            'height' => 601,
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $start = Carbon::parse($fetchInfo['start']);
        $end = Carbon::parse($fetchInfo['end']);

        return Schedule::query()
            ->with('booking')
            ->when(
                $this->laboratoryId,
                fn ($q) => $q->where('laboratory_id', $this->laboratoryId)
            )
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->whereNotNull('recurrence_until')
                            ->where('recurrence_until', '>=', $start)
                            ->where('start_at', '<=', $end);
                    });
            })
            ->get()
            ->flatMap(function (Schedule $s) use ($start, $end) {
                return $s->recurrence_days
                    ? $this->generateRecurringEvents($s, $start, $end)
                    : [$this->formatEvent($s)];
            })
            ->values()
            ->toArray();
    }

    protected function formatEvent(Schedule $schedule): array
    {
        if ($schedule->type === 'unstructured') {
            $isReserved = $schedule->booking->where('status', 'approved')->isNotEmpty();

            return [
                'id' => $schedule->id,
                'title' => $isReserved ? 'Reservado' : 'Disponible',
                'start' => $schedule->start_at,
                'end' => $schedule->end_at,
                'color' => $isReserved ? '#ef4444' : '#25c55e',
                'extendedProps' => [
                    'type' => $schedule->type,
                    'blocked' => $isReserved,
                ],
            ];
        }

        return [
            'id' => $schedule->id,
            'title' => $schedule->title,
            'start' => $schedule->start_at,
            'end' => $schedule->end_at,
            'color' => $schedule->color,
            'extendedProps' => [
                'type' => $schedule->type,
                'blocked' => $schedule->type === 'structured',
            ],
        ];
    }

    protected function generateRecurringEvents(Schedule $schedule, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $events = [];
        $startDate = Carbon::parse($schedule->start_at);
        $endDate = Carbon::parse($schedule->end_at);
        $length = $startDate->diffInMinutes($endDate);
        $until = Carbon::parse($schedule->recurrence_until);
        $days = array_filter(array_map('intval', explode(',', $schedule->recurrence_days ?? '')));

        foreach (CarbonPeriod::create($startDate, $until) as $date) {
            if (! in_array($date->dayOfWeekIso, $days, true)) {
                continue;
            }

            $s = $date->copy()->setTime($startDate->hour, $startDate->minute);
            $e = $s->copy()->addMinutes($length);

            if ($e->lte($rangeStart) || $s->gte($rangeEnd)) {
                continue;
            }

            $events[] = [
                'id' => "{$schedule->id}-{$s->toDateString()}",
                'title' => $schedule->title,
                'start' => $s,
                'end' => $e,
                'color' => $schedule->color,
                'extendedProps' => ['type' => 'structured', 'isRecurring' => true],
            ];
        }

        return $events;
    }

    protected function processRecurrenceData(array $data): array
    {
        $recurring = $data['is_recurring'] ?? false;

        return [
            'recurrence_days' => $recurring ? implode(',', $data['recurrence_days'] ?? []) : null,
            'recurrence_until' => $recurring ? $data['recurrence_until'] : null,
        ];
    }

    protected function headerActions(): array
    {
        return [
            $this->makeCreatePracticeAction(),
        ];
    }

    private function makeCreatePracticeAction(): CreateAction
    {
        return CreateAction::make()
            ->label('Crear práctica')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->mountUsing(function (Schema $form, array $arguments): void {
                $form->fill([
                    'is_structured' => true,
                    'is_recurring' => false,
                    'recurrence_days' => [],
                    'recurrence_until' => null,
                    'start_at' => $arguments['start'] ?? null,
                    'end_at' => $arguments['end'] ?? null,
                    'laboratory_id' => $this->laboratoryId,
                    'color' => '#7b82f6',
                    'title' => null,
                    'academic_program_name' => null,
                    'semester' => null,
                    'student_count' => null,
                    'group_count' => null,
                    'project_type' => null,
                    'academic_program' => null,
                    'applicants' => null,
                    'research_name' => null,
                    'advisor' => null,
                ]);
            })
            ->form($this->getFormSchema())
            ->using(fn (array $data) => $this->persistSchedule($data));
    }

    private function persistSchedule(array $data): ?Schedule
    {
        if (! $data['start_at'] || ! $data['end_at']) {
            Notification::make()->title('Datos incompletos')->body('Debes indicar inicio y fin.')->danger()->send();

            return null;
        }

        $start = Carbon::parse($data['start_at']);
        $end = Carbon::parse($data['end_at']);
        $isStructured = $this->normalizeStructuredValue($data['is_structured'] ?? true);

        if ($end->lte($start) || $end->hour > 20) {
            Notification::make()->title('Horario inválido')->body('Revisa rango y límite de hora.')->danger()->send();

            return null;
        }

        $recurrence = $this->processRecurrenceData($data);

        $schedule = Schedule::create([
            'type' => $isStructured ? 'structured' : 'unstructured',
            'title' => $isStructured ? $data['title'] : 'Disponible para reserva',
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'color' => $data['color'],
            'laboratory_id' => $data['laboratory_id'] ?? null,
            'user_id' => Auth::id(),
            'recurrence_days' => $recurrence['recurrence_days'],
            'recurrence_until' => $recurrence['recurrence_until'],
        ]);

        if ($isStructured) {
            $schedule->structured()->create([
                'academic_program_name' => $data['academic_program_name'] ?? null,
                'semester' => $data['semester'] ?? null,
                'student_count' => $data['student_count'] ?? null,
                'group_count' => $data['group_count'] ?? null,
            ]);
        } else {
            $schedule->unstructured()->create([
                'project_type' => $data['project_type'] ?? null,
                'academic_program' => $data['academic_program'] ?? null,
                'semester' => $data['semester'] ?? null,
                'applicants' => $data['applicants'] ?? null,
                'research_name' => $data['research_name'] ?? null,
                'advisor' => $data['advisor'] ?? null,
            ]);
        }

        return $schedule;
    }

    protected function modalActions(): array
    {
        return [
            $this->makeFreeUpSlotAction(),
            $this->makeEditAction(),
            $this->makeDeleteAction(),
        ];
    }

    private function makeFreeUpSlotAction(): Action
    {
        return Action::make('freeUpSlot')
            ->label('Liberar Horario')
            ->icon('heroicon-o-lock-open')
            ->color('success')
            ->visible(function (?Schedule $record): bool {
                if (! $record) {
                    return false;
                }

                return $record->booking()->where('status', 'approved')->exists();
            })
            ->requiresConfirmation()
            ->modalHeading('¿Liberar este horario?')
            ->modalDescription('Esta acción eliminará la reserva actual y el espacio volverá a estar disponible. Esta acción no se puede deshacer.')
            ->action(function (Schedule $record): void {
                $record->booking()->where('status', 'approved')->delete();

                Notification::make()
                    ->title('Horario Liberado')
                    ->body('El espacio ahora está disponible para nuevas reservas.')
                    ->success()
                    ->send();
            });
    }

    private function makeEditAction(): EditAction
    {
        return EditAction::make()
            ->label('Editar')
            ->visible(fn (?Schedule $r) => $r instanceof Schedule)
            ->mountUsing(function (Schedule $record, Form $form, array $arguments): void {
                $form->fill($this->mapRecordToFormData($record, $arguments));
            })
            ->form($this->getFormSchema())
            ->action(function (Schedule $record, array $data): void {
                if (! $data['start_at'] || ! $data['end_at']) {
                    Notification::make()->title('Datos incompletos')->body('Debes indicar inicio y fin.')->danger()->send();

                    return;
                }

                $start = Carbon::parse($data['start_at']);
                $end = Carbon::parse($data['end_at']);
                $isStructured = $this->normalizeStructuredValue($data['is_structured'] ?? true);

                if ($end->lte($start) || $end->hour > 24) {
                    Notification::make()->title('Horario inválido')->body('Revisa hora de fin.')->danger()->send();

                    return;
                }

                $recurrence = $this->processRecurrenceData($data);

                $record->update([
                    'type' => $isStructured ? 'structured' : 'unstructured',
                    'title' => $isStructured ? $data['title'] : $record->title,
                    'laboratory_id' => $data['laboratory_id'] ?? $record->laboratory_id,
                    'start_at' => $data['start_at'],
                    'end_at' => $data['end_at'],
                    'color' => $data['color'],
                    'recurrence_days' => $recurrence['recurrence_days'],
                    'recurrence_until' => $recurrence['recurrence_until'],
                ]);

                if ($isStructured) {
                    $record->structured()->updateOrCreate([], [
                        'academic_program_name' => $data['academic_program_name'] ?? null,
                        'semester' => $data['semester'] ?? null,
                        'student_count' => $data['student_count'] ?? null,
                        'group_count' => $data['group_count'] ?? null,
                    ]);
                } else {
                    $record->unstructured()->updateOrCreate([], [
                        'project_type' => $data['project_type'] ?? null,
                        'academic_program' => $data['academic_program'] ?? null,
                        'semester' => $data['semester'] ?? null,
                        'applicants' => $data['applicants'] ?? null,
                        'research_name' => $data['research_name'] ?? null,
                        'advisor' => $data['advisor'] ?? null,
                    ]);
                }
            });
    }

    private function makeDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Eliminar')
            ->visible(fn (?Schedule $r) => $r instanceof Schedule)
            ->before(function (Schedule $record): void {
                optional($record->{$record->type})->delete();
                $record->delete();
            });
    }

    private function mapRecordToFormData(Schedule $record, array $arguments): array
    {
        return [
            'laboratory_id' => $record->laboratory_id,
            'is_structured' => $record->type === 'structured',
            'title' => $record->title,
            'start_at' => $arguments['event']['start'] ?? $record->start_at,
            'end_at' => $arguments['event']['end'] ?? $record->end_at,
            'color' => $record->color,
            'is_recurring' => (bool) $record->recurrence_days,
            'recurrence_days' => $record->recurrence_days ? explode(',', $record->recurrence_days) : [],
            'recurrence_until' => $record->recurrence_until,
            'academic_program_name' => $record->structured->academic_program_name ?? null,
            'semester' => $record->structured->semester ?? null,
            'student_count' => $record->structured->student_count ?? null,
            'group_count' => $record->structured->group_count ?? null,
            'project_type' => $record->unstructured->project_type ?? null,
            'academic_program' => $record->unstructured->academic_program ?? null,
            'applicants' => $record->unstructured->applicants ?? null,
            'research_name' => $record->unstructured->research_name ?? null,
            'advisor' => $record->unstructured->advisor ?? null,
        ];
    }

    public function getFormSchema(): array
    {
        return [
            Radio::make('is_structured')
                ->label('Tipo de práctica')
                ->options([
                    true => 'Estructurada',
                    false => 'No estructurada',
                ])
                ->helperText('Selecciona primero el tipo de práctica.')
                ->reactive()
                ->default(true)
                ->inline(),

            Section::make('Horario')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(4)->schema([
                        Select::make('laboratory_id')
                            ->label('Espacio académico')
                            ->options(Laboratory::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->default(fn () => $this->laboratoryId)
                            ->disabled(fn () => filled($this->laboratoryId))
                            ->dehydrated()
                            ->required(),
                        DateTimePicker::make('start_at')
                            ->label('Inicio')
                            ->required()
                            ->seconds(false),
                        DateTimePicker::make('end_at')
                            ->label('Fin')
                            ->required()
                            ->seconds(false)
                            ->after('start_at'),
                        ColorPicker::make('color')
                            ->label('Color del evento')
                            ->default('#7b82f6'),
                    ]),
                ]),

            Section::make('Datos de la práctica')
                ->icon('heroicon-o-academic-cap')
                ->visible(fn ($get) => $get('is_structured'))
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('academic_program_name')
                            ->label('Programa académico')
                            ->options(fn () => AcademicProgram::where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'name'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('semester')
                            ->label('Semestre')
                            ->options(array_combine(range(1, 10), range(1, 10)))
                            ->required(),
                        TextInput::make('title')
                            ->label('Nombre de la práctica')
                            ->placeholder('Ej: Laboratorio de Química Orgánica')
                            ->required(),
                    ]),
                ]),

            Section::make('Participantes')
                ->icon('heroicon-o-user-group')
                ->visible(fn ($get) => $get('is_structured'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('student_count')
                            ->label('Número de estudiantes')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('group_count')
                            ->label('Número de grupos')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ]),
                ]),

            Section::make('Espacio libre para reserva')
                ->icon('heroicon-o-calendar-days')
                ->visible(fn ($get) => ! $get('is_structured'))
                ->schema([
                    TextInput::make('slot_hint')
                        ->label('Descripción')
                        ->disabled()
                        ->default('Este horario se creará como espacio libre reservable (no estructurado).')
                        ->dehydrated(false),
                ]),

            Section::make('Recurrencia')
                ->icon('heroicon-o-arrow-path')
                ->schema([
                    Toggle::make('is_recurring')
                        ->label('Evento recurrente')
                        ->helperText('Repite este horario en los días seleccionados hasta la fecha indicada.')
                        ->reactive()
                        ->inline(false),

                    CheckboxList::make('recurrence_days')
                        ->label('Días de la semana')
                        ->options([
                            '1' => 'Lunes',
                            '2' => 'Martes',
                            '3' => 'Miércoles',
                            '4' => 'Jueves',
                            '5' => 'Viernes',
                            '6' => 'Sábado',
                        ])
                        ->columns(6)
                        ->visible(fn ($get) => $get('is_recurring')),

                    DatePicker::make('recurrence_until')
                        ->label('Repetir hasta')
                        ->displayFormat('d/m/Y')
                        ->minDate(
                            fn ($get) => $get('start_at') ? Carbon::parse($get('start_at'))->addDay() : null
                        )
                        ->visible(fn ($get) => $get('is_recurring')),
                ]),
        ];
    }

    private function normalizeStructuredValue(mixed $value): bool
    {
        return in_array((string) $value, ['1', 'true'], true);
    }
}
