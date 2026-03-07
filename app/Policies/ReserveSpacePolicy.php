<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReserveSpacePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('ADMIN') ||
            $user->hasPermissionTo('ver panel reservar espacios');
    }

    public function view(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('ADMIN') ||
            $user->hasPermissionTo('ver cualquier reservar espacio');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN') ||
            $user->hasPermissionTo('crear reservar espacio');
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('ADMIN') ||
            $user->hasPermissionTo('actualizar resevar espacios');
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('ADMIN') ||
            $user->hasPermissionTo('eliminar reservar espacio');
    }
}
