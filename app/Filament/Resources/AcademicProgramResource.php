<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\AcademicProgramResource\Pages;
use App\Models\AcademicProgram;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AcademicProgramResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = AcademicProgram::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 102;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.academic_program.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.academic_program.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.academic_program.plural');
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'LABORATORISTA']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Cache::remember(
            'nav-badge:academic-programs:total',
            60,
            fn () => static::getModel()::count()
        );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Información del Programa')
                    ->icon('heroicon-o-academic-cap')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre del Programa')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ejemplo: Ingeniería de Sistemas'),

                                Forms\Components\TextInput::make('code')
                                    ->label('Código')
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ejemplo: IS'),

                                Forms\Components\TextInput::make('faculty')
                                    ->label('Facultad')
                                    ->maxLength(255)
                                    ->placeholder('Ejemplo: Ingeniería'),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Programa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('faculty')
                    ->label('Facultad')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Activo')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
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
            ->defaultSort('name', 'asc')
            ->emptyStateHeading('No hay programas académicos')
            ->emptyStateDescription('Crea el primer programa académico')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicPrograms::route('/'),
            'create' => Pages\CreateAcademicProgram::route('/create'),
            'edit' => Pages\EditAcademicProgram::route('/{record}/edit'),
        ];
    }
}
