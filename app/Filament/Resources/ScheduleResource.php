<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPanelRoleAccess;
use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Filament\Resources\Resource;

class ScheduleResource extends Resource
{
    use HasPanelRoleAccess;

    protected static ?string $model = Schedule::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static string | \UnitEnum | null $navigationGroup = 'Gestión de Reservas';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Gestión de Horarios';

    protected static ?string $modelLabel = 'Horario';

    protected static ?string $pluralLabel = 'Horarios';

    public static function canAccess(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(['ADMIN', 'COORDINADOR', 'LABORATORISTA']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ScheduleCalendar::route('/'),
        ];
    }
}
