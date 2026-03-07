<?php

namespace App\Filament\Concerns;

trait HasPanelRoleAccess
{
    protected static function superAdminRoleNames(): array
    {
        $configuredSuperAdminRole = (string) config('filament-shield.super_admin.name', 'SUPER-ADMIN');

        return array_unique([$configuredSuperAdminRole, 'SUPER-ADMIN', 'super_admin']);
    }

    protected static function userHasAnyRole(array $roles): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole(static::superAdminRoleNames())) {
            return true;
        }

        return $user->hasRole($roles);
    }

    protected static function userHasNoneRole(array $roles): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return false;
            }
        }

        return true;
    }
}
