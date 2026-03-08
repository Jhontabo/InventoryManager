<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\MaintenanceResource\Pages;
use App\Models\Maintenance;
use App\Models\Product;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = Maintenance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 103;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.maintenance.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.maintenance.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.maintenance.plural');
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'LABORATORISTA']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Equipo')
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('maintenance_type')
                            ->label('Tipo de mantenimiento')
                            ->options([
                                'preventive' => 'Preventivo',
                                'corrective' => 'Correctivo',
                                'calibration' => 'Calibración',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programado',
                                'in_progress' => 'En progreso',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->required()
                            ->default('scheduled'),
                    ]),

                Grid::make(3)
                    ->schema([
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Fecha programada')
                            ->required(),

                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Inicio'),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Finalización'),
                    ]),

                Grid::make(3)
                    ->schema([
                        Forms\Components\DatePicker::make('next_maintenance_at')
                            ->label('Próximo mantenimiento'),

                        Forms\Components\Select::make('performed_by')
                            ->label('Responsable')
                            ->options(fn () => User::query()
                                ->selectRaw("id, CONCAT(name, ' ', last_name) as full_name")
                                ->orderBy('name')
                                ->pluck('full_name', 'id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('provider')
                            ->label('Proveedor / Técnico')
                            ->maxLength(255),
                    ]),

                Forms\Components\TextInput::make('cost')
                    ->label('Costo')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),

                Forms\Components\Textarea::make('notes')
                    ->label('Notas')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Equipo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maintenance_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'preventive' => 'Preventivo',
                        'corrective' => 'Correctivo',
                        'calibration' => 'Calibración',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Programado',
                        'in_progress' => 'En progreso',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Programado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_maintenance_at')
                    ->label('Próximo')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Costo')
                    ->money('COP')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Programado',
                        'in_progress' => 'En progreso',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),
                SelectFilter::make('maintenance_type')
                    ->label('Tipo')
                    ->options([
                        'preventive' => 'Preventivo',
                        'corrective' => 'Correctivo',
                        'calibration' => 'Calibración',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
