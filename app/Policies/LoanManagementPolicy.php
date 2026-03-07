<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanManagementPolicy
{
    use HandlesAuthorization;

    /**
     * ¿Puede ver el listado de préstamos (panel o “mis préstamos”)?
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('ADMIN')
            || $user->hasPermissionTo('ver panel administrar prestamos');
    }

    /**
     * ¿Puede ver un préstamo específico?
     * - Admin y Coordinador: cualquiera.
     * - Resto: solo el propio.
     */
    public function view(User $user, Loan $loan): bool
    {
        return $user->hasRole('ADMIN')
            || $user->hasPermissionTo('ver cualquier administrar prestamo')
            || $loan->user_id === $user->id;
    }

    /**
     * ¿Puede crear un préstamo?
     */
    public function create(User $user): bool
    {
        return $user->hasRole('ADMIN')
            || $user->hasPermissionTo('crear administrar prestamo');
    }

    /**
     * ¿Puede actualizar un préstamo?
     * (Por ejemplo: cambiar estado, fechas, etc.)
     */
    public function update(User $user, Loan $loan): bool
    {
        return $user->hasRole('ADMIN')
            || $user->hasPermissionTo('actualizar administrar prestamo');
    }

    /**
     * ¿Puede eliminar un préstamo?
     */
    public function delete(User $user, Loan $loan): bool
    {
        return $user->hasRole('ADMIN')
            || $user->hasPermissionTo('eliminar administrar prestamo');
    }
}
