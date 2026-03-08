<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\LoanManagementResource\Pages;
use App\Models\Loan;
use App\Notifications\LoanApproved;
use App\Notifications\LoanRejected;
use App\Notifications\LoanReturned;
use App\Services\LoanService;
use DomainException;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Filament\Actions;

class LoanManagementResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = Loan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = null;

    protected static string | \UnitEnum | null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.loans');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.loan_management.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.loan_management.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.loan_management.plural');
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'LABORATORISTA']);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::remember(
            'nav-badge:loan-management:pending',
            60,
            fn () => static::getModel()::where('status', Loan::STATUS_PENDING)->count()
        );

        return $count ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $pending = Cache::remember(
            'nav-badge:loan-management:pending',
            60,
            fn () => static::getModel()::where('status', Loan::STATUS_PENDING)->count()
        );

        return $pending > 0 ? 'warning' : 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['product:id,name,image,available_quantity', 'user:id,name,last_name,email'])
            ->select(['id', 'product_id', 'user_id', 'status', 'requested_at', 'approved_at', 'estimated_return_at', 'actual_return_at'])
            ->whereIn('status', [Loan::STATUS_PENDING, Loan::STATUS_APPROVED, Loan::STATUS_RETURNED])
            ->whereNotNull('user_id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->actionsPosition(Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->columns([
                ImageColumn::make('product.image')
                    ->label('Imagen')
                    ->size(51)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraImgAttributes(['class' => 'rounded-lg']),

                TextColumn::make('product.name')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => "ID: {$record->product_id}", position: 'above'),

                TextColumn::make('product.available_quantity')
                    ->label('Disponibles')
                    ->sortable()
                    ->color(fn ($state) => $state > 3 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->icon(fn ($state) => $state > 3 ? 'heroicon-o-check-circle' : ($state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle')),

                TextColumn::make('user.name')
                    ->label('Solicitante')
                    ->formatStateUsing(fn ($state, $record) => "{$record->user->name} {$record->user->last_name}")
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Loan::STATUS_PENDING => 'warning',
                        Loan::STATUS_APPROVED => 'success',
                        Loan::STATUS_REJECTED => 'danger',
                        Loan::STATUS_RETURNED => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Loan::STATUS_PENDING => 'Pendiente',
                        Loan::STATUS_APPROVED => 'Aprobado',
                        Loan::STATUS_REJECTED => 'Rechazado',
                        Loan::STATUS_RETURNED => 'Devuelto',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        Loan::STATUS_PENDING => 'heroicon-o-clock',
                        Loan::STATUS_APPROVED => 'heroicon-o-check',
                        Loan::STATUS_REJECTED => 'heroicon-o-x-circle',
                        Loan::STATUS_RETURNED => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                TextColumn::make('requested_at')
                    ->label('Solicitud')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label('Aprobación')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->placeholder('Pendiente')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-badge' : 'heroicon-o-clock')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('estimated_return_at')
                    ->label('Devolución Estimada')
                    ->dateTime('d M Y')
                    ->color(
                        fn ($record) => $record->status === Loan::STATUS_APPROVED && $record->estimated_return_at < now()
                          ? 'danger'
                          : ($record->estimated_return_at ? 'info' : 'gray')
                    )
                    ->icon(
                        fn ($record) => $record->status === Loan::STATUS_APPROVED && $record->estimated_return_at < now()
                          ? 'heroicon-o-exclamation-triangle'
                          : 'heroicon-o-calendar'
                    )
                    ->sortable()
                    ->placeholder('No asignada')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('actual_return_at')
                    ->label('Devuelto')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->placeholder('Pendiente')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-archive-box' : 'heroicon-o-truck')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->actions([
                Actions\Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->size('sm')
                    ->modalHeading('Aprobar Préstamo')
                    ->modalDescription('Confirma la aprobación de este préstamo.')
                    ->form([
                        Forms\Components\DatePicker::make('estimated_return_at')
                            ->label('Fecha de devolución')
                            ->required()
                            ->minDate(now()->addDay())
                            ->default(now()->addWeek())
                            ->displayFormat('d M Y'),
                    ])
                    ->action(function (Loan $record, array $data) {
                        try {
                            $loan = app(LoanService::class)->approve($record, $data['estimated_return_at'] ?? null);

                            Notification::make()
                                ->success()
                                ->title('Préstamo aprobado')
                                ->body('Fecha límite: '.optional($loan->estimated_return_at)?->format('d/m/Y'))
                                ->send();

                            $loan->user?->notify(new LoanApproved($loan));
                        } catch (DomainException $e) {
                            Notification::make()
                                ->danger()
                                ->title('No fue posible aprobar el préstamo')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn (Loan $record) => $record->status === Loan::STATUS_PENDING),

                Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->button()
                    ->size('sm')
                    ->modalHeading('Rechazar Préstamo')
                    ->form([
                        Forms\Components\Textarea::make('observations')
                            ->label('Motivo')
                            ->maxLength(500),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Loan $record, array $data) {
                        try {
                            $loan = app(LoanService::class)->reject($record, $data['observations'] ?? null);

                            Notification::make()
                                ->danger()
                                ->title('Préstamo rechazado')
                                ->body('La solicitud fue rechazada correctamente.')
                                ->send();

                            $loan->user?->notify(new LoanRejected($loan));
                        } catch (DomainException $e) {
                            Notification::make()
                                ->danger()
                                ->title('No fue posible rechazar el préstamo')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn (Loan $record) => $record->status === Loan::STATUS_PENDING),

                Actions\Action::make('return')
                    ->label('Devuelto')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('info')
                    ->button()
                    ->size('sm')
                    ->modalHeading('Registrar Devolución')
                    ->requiresConfirmation()
                    ->action(function (Loan $record) {
                        try {
                            $loan = app(LoanService::class)->markAsReturned($record);

                            Notification::make()
                                ->success()
                                ->title('Equipo devuelto')
                                ->body('La devolución se registró correctamente.')
                                ->send();

                            $loan->user?->notify(new LoanReturned($loan));
                        } catch (DomainException $e) {
                            Notification::make()
                                ->danger()
                                ->title('No fue posible registrar la devolución')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn (Loan $record) => $record->status === Loan::STATUS_APPROVED),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No hay préstamos registrados')
            ->emptyStateDescription('Cuando existan solicitudes activas o devoluciones pendientes, aparecerán aquí.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->defaultSort('requested_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanManagements::route('/'),
        ];
    }
}
