<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\LaboratoryResource\Pages;
use App\Models\Laboratory;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LaboratoryResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = Laboratory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = null;

    protected static string | \UnitEnum | null $navigationGroup = null;

    protected static ?int $navigationSort = 100;

    protected static ?string $pluralModelLabel = null;

    protected static ?string $modelLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.laboratory.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.laboratory.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.laboratory.plural');
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'LABORATORISTA']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Cache::remember(
            'nav-badge:laboratories:total',
            60,
            fn () => static::getModel()::count()
        );
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user:id,name,last_name');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Información del Laboratorio')
                    ->icon('heroicon-o-building-office-2')
                    ->description('Ingrese los datos básicos del laboratorio')
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Laboratorio')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: Laboratorio de Química')
                                    ->helperText('Nombre oficial del laboratorio')
                                    ->prefixIcon('heroicon-o-beaker'),

                                Forms\Components\TextInput::make('capacity')
                                    ->label('Capacidad')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->step(1)
                                    ->placeholder('20')
                                    ->helperText('Número máximo de personas que pueden estar simultáneamente')
                                    ->prefixIcon('heroicon-o-users'),

                                Forms\Components\TextInput::make('location')
                                    ->label('Ubicación')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Edificio Principal, Piso 2, Aula 201')
                                    ->helperText('Dirección exacta del laboratorio')
                                    ->prefixIcon('heroicon-o-map-pin'),
                            ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Inventario del Laboratorio')
                    ->icon('heroicon-o-cube')
                    ->description('Seleccione los productos/equipos que estarán disponibles en este laboratorio')
                    ->schema([
                        Select::make('product_ids')
                            ->label('Productos y Equipos')
                            ->multiple()
                            ->options(fn () => Product::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->afterStateHydrated(function (Select $component, ?Laboratory $record): void {
                                if (! $record) {
                                    return;
                                }

                                $component->state($record->products()->pluck('products.id')->all());
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Seleccione los productos que pertenecerán a este laboratorio'),
                    ]),

                \Filament\Schemas\Components\Section::make('Responsable del Laboratorio')
                    ->icon('heroicon-o-user')
                    ->description('Persona encargada de administrar el laboratorio')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Encargado')
                            ->options(fn () => User::role('LABORATORISTA')->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Seleccione un encargado')
                            ->helperText('El encargado será responsable del inventario y préstamos')
                            ->prefixIcon('heroicon-o-user-circle'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Laboratorio')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Laboratory $record) => $record->location)
                    ->icon('heroicon-o-beaker'),

                Tables\Columns\TextColumn::make('capacity')
                    ->badge()
                    ->label('Capacidad')
                    ->formatStateUsing(fn ($state): string => "{$state} personas")
                    ->color(fn ($state): string => match (true) {
                        $state > 30 => 'success',
                        $state > 15 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Productos')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Encargado')
                    ->getStateUsing(function (Laboratory $record): ?string {
                        if (! $record->user) {
                            return null;
                        }

                        return trim($record->user->name.' '.$record->user->last_name);
                    })
                    ->sortable()
                    ->searchable()
                    ->placeholder('Sin asignar'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Encargado')
                    ->options(fn () => User::role('LABORATORISTA')->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->tooltip('Editar laboratorio'),

                Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->tooltip('Eliminar laboratorio')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Laboratorio eliminado')
                            ->body('El laboratorio ha sido eliminado correctamente.'),
                    ),
            ])

            ->emptyStateHeading('No hay laboratorios')
            ->emptyStateDescription('Crea el primer laboratorio para comenzar')
            ->emptyStateIcon('heroicon-o-beaker')
            ->emptyStateActions([
                Actions\CreateAction::make()
                    ->label('Crear laboratorio')
                    ->icon('heroicon-o-plus'),
            ])
            ->defaultSort('name', 'asc')
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaboratories::route('/'),
            'create' => Pages\CreateLaboratory::route('/create'),
            'edit' => Pages\EditLaboratory::route('/{record}/edit'),
        ];
    }
}
