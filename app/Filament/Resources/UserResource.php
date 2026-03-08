<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
// Necesario para hashear contraseñas
// Necesario para lógica condicional en formularios
use Illuminate\Database\Eloquent\Model; // Para agrupar campos visualmente
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class UserResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = null;

    protected static ?string $pluralModelLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.admin');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.user.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.user.model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel.resources.user.plural');
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'LABORATORISTA']);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('roles:id,name');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Cache::remember(
            'nav-badge:users:total',
            60,
            fn () => static::getModel()::count()
        );
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = Cache::remember(
            'nav-badge:users:total',
            60,
            fn () => static::getModel()::count()
        );

        return $count > 0 ? 'primary' : 'gray';
    }

    public static function form(Schema $schema): Schema
    {

        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Identificación')
                        ->icon('heroicon-o-user-circle')
                        ->description('Información básica y foto del usuario.')
                        ->schema([
                            Section::make() // Quitamos el título de la sección si el título del paso es suficiente
                                ->columns(12) // Usamos un grid de 12 columnas para mayor flexibilidad
                                ->schema([
                                    Forms\Components\FileUpload::make('avatar_url')
                                        ->label('Foto de Perfil')
                                        ->image()
                                        ->avatar()
                                        ->imageEditor()
                                        ->directory('avatars')
                                        ->preserveFilenames()
                                        ->helperText('Opcional.')
                                        ->columnSpan([ // Ocupa 4 de 12 columnas
                                            'default' => 12, // En pantallas pequeñas, ocupa todo el ancho
                                            'md' => 4,      // En medianas y grandes, ocupa 4 columnas
                                        ]),

                                    Grid::make(1) // Grid anidado para nombre y apellido uno debajo del otro
                                        ->columnSpan([ // Ocupa 8 de 12 columnas
                                            'default' => 12,
                                            'md' => 8,
                                        ])
                                        ->schema([
                                            Forms\Components\TextInput::make('name')
                                                ->label('Nombre')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ej: Carlos Alberto')
                                                ->live(onBlur: true),
                                            Forms\Components\TextInput::make('last_name')
                                                ->label('Apellido')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ej: Pérez Rodríguez')
                                                ->live(onBlur: true),
                                        ]),
                                ]),
                        ]),
                    Step::make('Información de Contacto')
                        ->icon('heroicon-o-identification')
                        ->description('Correo, teléfono y dirección.')
                        ->schema([
                            Section::make()
                                ->columns(2) // Dos columnas para email y teléfono
                                ->schema([
                                    Forms\Components\TextInput::make('email')
                                        ->label('Correo Electrónico')
                                        ->email()
                                        ->required()
                                        ->maxLength(255)
                                        ->unique(ignoreRecord: true)
                                        ->placeholder('usuario@ejemplo.com')
                                        ->live(onBlur: true)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Teléfono')
                                        ->tel()
                                        ->maxLength(20)
                                        ->placeholder('Ej: +57 310 123 4567')
                                        ->helperText('Opcional.')
                                        ->columnSpan(1),
                                ]),
                            Section::make() // Sección separada para la dirección o dentro de la misma
                                ->columns(1)
                                ->schema([
                                    Forms\Components\Textarea::make('address')
                                        ->label('Dirección')
                                        ->maxLength(500)
                                        ->rows(3)
                                        ->placeholder('Ej: Calle 10 # 20-30, Apto 101, Barrio, Ciudad')
                                        ->helperText('Opcional.')
                                        ->columnSpanFull(), // Ocupa todo el ancho de su sección/grid padre
                                ]),
                        ]),
                    Wizard\Step::make('Configuración de Cuenta')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->description('Roles y estado de la cuenta.')
                        ->schema([
                            Section::make()
                                ->columns(2) // Dos columnas para roles y status
                                ->schema([
                                    Forms\Components\TextInput::make('password')
                                        ->label('Contraseña')
                                        ->password()
                                        ->maxLength(255)
                                        ->required(fn (?User $record): bool => $record === null)
                                        ->dehydrated(fn (?string $state): bool => filled($state))
                                        ->confirmed(),
                                    Forms\Components\TextInput::make('password_confirmation')
                                        ->label('Confirmar contraseña')
                                        ->password()
                                        ->maxLength(255)
                                        ->required(fn (?User $record): bool => $record === null)
                                        ->dehydrated(false),
                                    Forms\Components\Select::make('roles')
                                        ->label('Roles')
                                        ->multiple()
                                        ->relationship(name: 'roles', titleAttribute: 'name')
                                        ->preload()
                                        ->searchable()
                                        ->required()
                                        ->helperText('Asigna uno o más roles.'),
                                    Forms\Components\Toggle::make('status')
                                        ->label('Usuario Activo')
                                        ->default(true)
                                        ->afterStateHydrated(function (Forms\Components\Toggle $component, mixed $state): void {
                                            $component->state($state === 'active');
                                        })
                                        ->dehydrateStateUsing(fn (bool $state): string => $state ? 'active' : 'inactive')
                                        ->onColor('success')
                                        ->offColor('danger')
                                        ->helperText('Permite o deniega el acceso.'),
                                ]),
                        ]),
                ])
                    ->columnSpanFull() // Asegura que el Wizard ocupe todo el ancho
                    ->persistStepInQueryString() // Mantiene el paso actual en la URL
                    ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular() // Muestra la imagen como círculo
                    ->defaultImageUrl(url('/images/default-avatar.png')), // Imagen por defecto si no hay avatar
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Apellido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-s-envelope') // Añade un icono
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge() // Muestra los roles como badges
                    ->formatStateUsing(fn ($state) => ucwords($state)) // Capitaliza el nombre del rol
                    ->color(fn (string $state): string => match (strtolower($state)) { // Colores dinámicos para roles (ejemplo)
                        'admin' => 'danger',
                        'editor' => 'warning',
                        'user' => 'success',
                        default => 'primary',
                    })
                    ->sortable(),
                // Importante devolver el estado
                Tables\Columns\ToggleColumn::make('status')
                    ->label('Activo')
                    ->onColor('success')
                    ->offColor('danger')
                    ->updateStateUsing(function (Model $record, $state) {
                        $record->status = $state ? 'active' : 'inactive';
                        $record->save();

                        // Forzar la actualización del estado en el frontend
                        $record->refresh();

                        Notification::make()
                            ->title('Estado actualizado')
                            ->body('El usuario ahora está '.($state ? 'activo' : 'inactivo'))
                            ->success()
                            ->send();

                        return $state;
                    })
                    ->getStateUsing(fn ($record): bool => $record->status === 'active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto, pero se puede mostrar
                Tables\Columns\TextColumn::make('updated_at') // Columna añadida
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->native(false), // Usar el estilo de Filament
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->native(false),
                Tables\Filters\Filter::make('created_at') // Filtro de fecha
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Creado desde'),
                        Forms\Components\DatePicker::make('created_until')->label('Creado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square') // Icono actualizado
                    ->color('primary'),
                Actions\DeleteAction::make(), // Acción de eliminar individual (si es necesaria además de la masiva)
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => true]);
                            Notification::make()->title(count($records).' Usuarios Activados')->success()->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => false]);
                            Notification::make()->title(count($records).' Usuarios Desactivados')->success()->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->icon('heroicon-o-trash'),
                ]),
            ])
            ->emptyStateHeading('No hay usuarios registrados')
            ->emptyStateDescription('Puedes crear tu primer usuario ahora mismo.')
            ->emptyStateIcon('heroicon-o-user-plus') // Icono más sugestivo
            ->emptyStateActions([
                Actions\CreateAction::make()
                    ->label('Crear Nuevo Usuario')
                    ->icon('heroicon-o-plus-circle'), // Icono actualizado
            ])
            ->defaultSort('created_at', 'desc') // Ordenar por defecto por los más recientes
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            // Aquí puedes agregar Relaciones si es necesario, por ejemplo:
            // RelationManagers\PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
