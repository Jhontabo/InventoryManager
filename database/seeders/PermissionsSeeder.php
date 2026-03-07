<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $admin                = Role::firstOrCreate(['name' => 'ADMIN',         'guard_name' => 'web']);
        $laboratory           = Role::firstOrCreate(['name' => 'LABORATORISTA', 'guard_name' => 'web']);
        $teacher              = Role::firstOrCreate(['name' => 'DOCENTE',       'guard_name' => 'web']);
        $student              = Role::firstOrCreate(['name' => 'ESTUDIANTE',    'guard_name' => 'web']);
        $coordinator          = Role::firstOrCreate(['name' => 'COORDINADOR',   'guard_name' => 'web']);


        $permissions = [
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel solicitud reservas',
            'ver panel horarios',
            'ver panel laboratorios',
            'ver panel inventario',
            'ver panel roles',
            'ver panel usuarios',

            // — Ver cualquier —
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'ver cualquier solicitud reserva',
            'ver cualquier horario',
            'ver cualquier laboratorio',
            'ver cualquier inventario',
            'ver cualquier rol',
            'ver cualquier usuario',

            // — Actualizar —
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'actualizar solicitud reserva',
            'actualizar horario',
            'actualizar laboratorio',
            'actualizar inventario',
            'actualizar rol',
            'actualizar usuario',

            // — Crear —
            'crear solicitud prestamo',
            'crear reservar espacio',
            'crear solicitud reserva',
            'crear horario',
            'crear laboratorio',
            'crear inventario',
            'crear rol',
            'crear usuario',

            // — Eliminar —
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
            'eliminar solicitud reserva',
            'eliminar horario',
            'eliminar laboratorio',
            'eliminar inventario',
            'eliminar rol',
            'eliminar usuario',

        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name'       => $perm,
                'guard_name' => 'web',
            ]);
        }


        $admin->syncPermissions(Permission::all());

        $laboratory->syncPermissions([
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel solicitud reservas',
            'ver panel horarios',
            'ver panel laboratorios',
            'ver panel inventario',
            'ver panel roles',
            'ver panel usuarios',

            // — Ver cualquier —
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'ver cualquier solicitud reserva',
            'ver cualquier horario',
            'ver cualquier laboratorio',
            'ver cualquier inventario',
            'ver cualquier rol',
            'ver cualquier usuario',

            // — Actualizar —
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'actualizar solicitud reserva',
            'actualizar horario',
            'actualizar laboratorio',
            'actualizar inventario',
            'actualizar rol',
            'actualizar usuario',

            // — Crear —
            'crear solicitud prestamo',
            'crear reservar espacio',
            'crear solicitud reserva',
            'crear horario',
            'crear laboratorio',
            'crear inventario',
            'crear rol',
            'crear usuario',

            // — Eliminar —
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
            'eliminar solicitud reserva',
            'eliminar laboratorio',
            'eliminar inventario',
            'eliminar rol',
            'eliminar usuario',
        ]);
        $teacher->syncPermissions([
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel horarios',
            // — Ver cualquier —
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',

            // — Actualizar —
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',

            // — Crear —
            'crear solicitud prestamo',
            'crear reservar espacio',

            // — Eliminar —
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
        ]);

        $student->syncPermissions([
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel horarios',
            // — Ver cualquier —
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',

            // — Actualizar —
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',

            // — Crear —
            'crear solicitud prestamo',
            'crear reservar espacio',

            // — Eliminar —
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
        ]);

        $coordinator->syncPermissions([


            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel solicitud reservas',
            'ver panel horarios',

            // — Ver cualquier —
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'ver cualquier solicitud reserva',
            'ver cualquier horario',

            // — Actualizar —
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'actualizar solicitud reserva',
            'actualizar horario',
            // — Crear —
            'crear solicitud prestamo',
            'crear reservar espacio',
            'crear solicitud reserva',
            'crear horario',

            // — Eliminar —
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
            'eliminar solicitud reserva',
            'eliminar horario',
        ]);
    }
}
