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

    protected static string | \UnitEnum | null $navigationGroup = null;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = null;

    protected static ?string $modelLabel = null;

    protected static ?string $pluralLabel = null;

    public static function getNavigationGroup(): ?string
    {
        return __('panel.nav.bookings');
    }

    public static function getNavigationLabel(): string
    {
        return __('panel.resources.schedule.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('panel.resources.schedule.model');
    }

    public static function getPluralLabel(): string
    {
        return __('panel.resources.schedule.plural');
    }

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
