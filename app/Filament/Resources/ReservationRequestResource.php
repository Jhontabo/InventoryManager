<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\ReservationRequestResource\Pages;
use App\Models\Booking;
use App\Models\Product;
use App\Notifications\BookingApproved;
use App\Notifications\BookingRejected;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class ReservationRequestResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = Booking::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.bookings');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.reservation_requests.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.reservation_requests.model');
    }

    public static function getPluralLabel(): string
    {
        return __('panel.resources.reservation_requests.plural');
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'LABORATORISTA']);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'laboratory:id,name,location',
                'schedule:id,start_at,end_at',
                'user:id,name,last_name,email',
            ])
            ->select([
                'id',
                'laboratory_id',
                'schedule_id',
                'user_id',
                'status',
                'created_at',
                'updated_at',
                'products',
                'project_type',
                'academic_program',
                'semester',
                'research_name',
                'advisor',
                'applicants',
                'rejection_reason',
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember(
            'nav-badge:reservation-requests:pending',
            60,
            fn () => static::getModel()::where('status', Booking::STATUS_PENDING)->count()
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        $count = Cache::remember(
            'nav-badge:reservation-requests:pending',
            60,
            fn () => static::getModel()::where('status', Booking::STATUS_PENDING)->count()
        );

        return $count > 3 ? 'warning' : 'success';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->actionsPosition(RecordActionsPosition::BeforeColumns)
            ->columns([
                TextColumn::make('laboratory.name')
                    ->label('Laboratorio')
                    ->description(fn ($record) => $record->laboratory?->location ?? 'Sin ubicación')
                    ->searchable()
                    ->icon('heroicon-o-building-office'),

                TextColumn::make('user.name')
                    ->label('Solicitante')
                    ->formatStateUsing(fn ($record) => "{$record->user->name} {$record->user->last_name}")
                    ->description(fn ($record) => $record->user->email ?? 'Sin correo')
                    ->searchable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('interval')
                    ->label('Horario')
                    ->getStateUsing(fn ($record) => $record->schedule && $record->schedule->start_at && $record->schedule->end_at
                        ? $record->schedule->start_at->format('d M Y, H:i').' - '.$record->schedule->end_at->format('H:i')
                        : 'No asignado')
                    ->icon('heroicon-o-clock'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        Booking::STATUS_PENDING => 'Pendiente',
                        Booking::STATUS_APPROVED => 'Aprobado',
                        Booking::STATUS_REJECTED => 'Rechazado',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->colors([
                        'warning' => Booking::STATUS_PENDING,
                        'success' => Booking::STATUS_APPROVED,
                        'danger' => Booking::STATUS_REJECTED,
                    ])
                    ->icon(fn ($state) => match ($state) {
                        Booking::STATUS_PENDING => 'heroicon-o-clock',
                        Booking::STATUS_APPROVED => 'heroicon-o-check-circle',
                        Booking::STATUS_REJECTED => 'heroicon-o-x-circle',
                        default => null,
                    }),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        Booking::STATUS_PENDING => 'Pendiente',
                        Booking::STATUS_APPROVED => 'Aprobado',
                        Booking::STATUS_REJECTED => 'Rechazado',
                    ]),
                SelectFilter::make('laboratory')
                    ->label('Laboratorio')
                    ->relationship('laboratory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(2)
            ->actions([
                Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->size('sm')
                    ->action(function (Booking $record) {
                        $record->status = Booking::STATUS_APPROVED;
                        $record->save();

                        $record->user?->notify(new BookingApproved($record));

                        Notification::make()
                            ->success()
                            ->title('Reserva aprobada')
                            ->body("La solicitud de {$record->user->name} {$record->user->last_name} ha sido aprobada.")
                            ->send();
                    })
                    ->visible(fn (Booking $record) => $record->status === Booking::STATUS_PENDING)
                    ->tooltip('Aprobar esta solicitud'),

                Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->size('sm')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->placeholder('Indique la razón del rechazo')
                            ->maxLength(503),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->status = Booking::STATUS_REJECTED;
                        $record->rejection_reason = $data['rejection_reason'];
                        $record->save();

                        $record->user?->notify(new BookingRejected($record));

                        Notification::make()
                            ->danger()
                            ->title('Reserva rechazada')
                            ->body("La solicitud de {$record->user->name} {$record->user->last_name} fue rechazada.")
                            ->send();
                    })
                    ->visible(fn (Booking $record) => $record->status === Booking::STATUS_PENDING)
                    ->tooltip('Rechazar esta solicitud'),

                Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->size('sm')
                    ->modalHeading(fn (Booking $record) => "Reserva #{$record->id}")
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(fn (Booking $record) => static::getDetailModalSchema($record)),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('No hay solicitudes de reserva')
            ->emptyStateDescription('Aquí aparecerán las solicitudes enviadas por los usuarios.')
            ->emptyStateIcon('heroicon-o-calendar');
    }

    protected static function getDetailModalSchema(Booking $record): array
    {
        $productNames = [];
        if (! empty($record->products)) {
            $raw = $record->products;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $ids = is_array($decoded) ? $decoded : explode(',', $raw);
            } else {
                $ids = (array) $raw;
            }
            $ids = array_filter(array_map('intval', $ids));
            $productNames = Product::whereIn('id', $ids)->pluck('name')->toArray();
        }

        $statusBadge = match ($record->status) {
            Booking::STATUS_PENDING => '<span style="color: #f59e0b; font-weight: 600;">Pendiente</span>',
            Booking::STATUS_APPROVED => '<span style="color: #10b981; font-weight: 600;">Aprobada</span>',
            Booking::STATUS_REJECTED => '<span style="color: #ef4444; font-weight: 600;">Rechazada</span>',
            default => ucfirst($record->status),
        };

        $schema = [
            Section::make('Estado de la solicitud')
                ->icon('heroicon-o-information-circle')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('status_display')
                            ->label('Estado')
                            ->content(new HtmlString($statusBadge)),
                        Placeholder::make('created_display')
                            ->label('Fecha de solicitud')
                            ->content($record->created_at->format('d/m/Y H:i')),
                        Placeholder::make('updated_display')
                            ->label('Última actualización')
                            ->content($record->updated_at->format('d/m/Y H:i')),
                    ]),
                ]),

            Section::make('Solicitante')
                ->icon('heroicon-o-user')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('name_display')
                            ->label('Nombre')
                            ->content(trim(($record->user->name ?? '').' '.($record->user->last_name ?? ''))),
                        Placeholder::make('email_display')
                            ->label('Correo')
                            ->content($record->user->email ?? 'Sin correo'),
                        Placeholder::make('applicants_display')
                            ->label('Otros solicitantes')
                            ->content($record->applicants ?: 'Ninguno'),
                    ]),
                ]),

            Section::make('Información del proyecto')
                ->icon('heroicon-o-academic-cap')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('project_type_display')
                            ->label('Tipo de proyecto')
                            ->content($record->project_type ?? 'No especificado'),
                        Placeholder::make('academic_program_display')
                            ->label('Programa académico')
                            ->content($record->academic_program ?? 'No especificado'),
                        Placeholder::make('semester_display')
                            ->label('Semestre')
                            ->content($record->semester ?? 'No especificado'),
                    ]),
                    Grid::make(2)->schema([
                        Placeholder::make('research_display')
                            ->label('Investigación')
                            ->content($record->research_name ?? 'No especificado'),
                        Placeholder::make('advisor_display')
                            ->label('Asesor')
                            ->content($record->advisor ?? 'No especificado'),
                    ]),
                ]),

            Section::make('Espacio y horario')
                ->icon('heroicon-o-calendar-days')
                ->compact()
                ->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('lab_display')
                            ->label('Laboratorio')
                            ->content(($record->laboratory->name ?? 'No asignado').($record->laboratory?->location ? " ({$record->laboratory->location})" : '')),
                        Placeholder::make('schedule_display')
                            ->label('Horario')
                            ->content($record->schedule && $record->schedule->start_at
                                ? $record->schedule->start_at->format('d/m/Y H:i').' - '.$record->schedule->end_at->format('H:i')
                                : 'No asignado'),
                        Placeholder::make('duration_display')
                            ->label('Duración')
                            ->content($record->schedule && $record->schedule->start_at
                                ? $record->schedule->start_at->diffInHours($record->schedule->end_at).' horas'
                                : 'N/A'),
                    ]),
                ]),

            Section::make('Materiales y equipos')
                ->icon('heroicon-o-beaker')
                ->compact()
                ->schema([
                    Placeholder::make('products_display')
                        ->label('Productos solicitados')
                        ->content(! empty($productNames)
                            ? new HtmlString('<ul class="list-disc ml-4">'.implode('', array_map(fn ($n) => "<li>{$n}</li>", $productNames)).'</ul>')
                            : 'No se especificaron materiales.'),
                ]),
        ];

        if ($record->status === Booking::STATUS_REJECTED && $record->rejection_reason) {
            $schema[] = Section::make('Motivo del rechazo')
                ->icon('heroicon-o-x-circle')
                ->compact()
                ->schema([
                    Placeholder::make('rejection_display')
                        ->label('')
                        ->content(new HtmlString('<p class="p-3 rounded-lg bg-danger-50 dark:bg-danger-900/50 text-danger-700 dark:text-danger-300">'.$record->rejection_reason.'</p>')),
                ]);
        }

        return $schema;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservationRequests::route('/'),
        ];
    }
}
