<?php

namespace App\Filament\Concerns;

trait HasPanelRoleAccess
{
    protected static function userHasAnyRole(array $roles): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole($roles);
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
