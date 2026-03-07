<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'SUPER-ADMIN', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
        $laboratory = Role::firstOrCreate(['name' => 'LABORATORISTA', 'guard_name' => 'web']);
        $teacher = Role::firstOrCreate(['name' => 'DOCENTE', 'guard_name' => 'web']);
        $student = Role::firstOrCreate(['name' => 'ESTUDIANTE', 'guard_name' => 'web']);
        $coordinator = Role::firstOrCreate(['name' => 'COORDINADOR', 'guard_name' => 'web']);

        $policyPermissions = [
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'delete_any_user',
            'force_delete_user', 'force_delete_any_user', 'restore_user', 'restore_any_user', 'replicate_user', 'reorder_user',

            'view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role', 'delete_any_role',
            'force_delete_role', 'force_delete_any_role', 'restore_role', 'restore_any_role', 'replicate_role', 'reorder_role',

            'view_any_product', 'view_product', 'create_product', 'update_product', 'delete_product', 'delete_any_product',
            'force_delete_product', 'force_delete_any_product', 'restore_product', 'restore_any_product', 'replicate_product', 'reorder_product',

            'view_any_laboratory', 'view_laboratory', 'create_laboratory', 'update_laboratory', 'delete_laboratory', 'delete_any_laboratory',
            'force_delete_laboratory', 'force_delete_any_laboratory', 'restore_laboratory', 'restore_any_laboratory', 'replicate_laboratory', 'reorder_laboratory',

            'view_any_schedule', 'view_schedule', 'create_schedule', 'update_schedule', 'delete_schedule', 'delete_any_schedule',
            'force_delete_schedule', 'force_delete_any_schedule', 'restore_schedule', 'restore_any_schedule', 'replicate_schedule', 'reorder_schedule',

            'view_any_loan::management', 'view_loan::management', 'create_loan::management', 'update_loan::management',
            'delete_loan::management', 'delete_any_loan::management', 'force_delete_loan::management', 'force_delete_any_loan::management',
            'restore_loan::management', 'restore_any_loan::management', 'replicate_loan::management', 'reorder_loan::management',

            'view_any_reservation::request', 'view_reservation::request', 'create_reservation::request', 'update_reservation::request',
            'delete_reservation::request', 'delete_any_reservation::request', 'force_delete_reservation::request', 'force_delete_any_reservation::request',
            'restore_reservation::request', 'restore_any_reservation::request', 'replicate_reservation::request', 'reorder_reservation::request',

            'view_any_available::product', 'view_available::product', 'create_available::product', 'update_available::product',
            'delete_available::product', 'delete_any_available::product', 'force_delete_available::product', 'force_delete_any_available::product',
            'restore_available::product', 'restore_any_available::product', 'replicate_available::product', 'reorder_available::product',

            'view_any_academic::program', 'view_academic::program', 'create_academic::program', 'update_academic::program',
            'delete_academic::program', 'delete_any_academic::program', 'force_delete_academic::program', 'force_delete_any_academic::program',
            'restore_academic::program', 'restore_any_academic::program', 'replicate_academic::program', 'reorder_academic::program',
        ];

        $legacyPermissions = [
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel solicitud reservas',
            'ver panel horarios',
            'ver panel laboratorios',
            'ver panel inventario',
            'ver panel roles',
            'ver panel usuarios',
            'ver panel administrar prestamos',

            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'ver cualquier solicitud reserva',
            'ver cualquier horario',
            'ver cualquier laboratorio',
            'ver cualquier inventario',
            'ver cualquier rol',
            'ver cualquier usuario',

            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'actualizar resevar espacios',
            'actualizar solicitud reserva',
            'actualizar horario',
            'actualizar laboratorio',
            'actualizar inventario',
            'actualizar rol',
            'actualizar usuario',
            'actualizar administrar prestamo',

            'crear solicitud prestamo',
            'crear reservar espacio',
            'crear solicitud reserva',
            'crear horario',
            'crear laboratorio',
            'crear inventario',
            'crear rol',
            'crear usuario',
            'crear administrar prestamo',

            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
            'eliminar solicitud reserva',
            'eliminar horario',
            'eliminar laboratorio',
            'eliminar inventario',
            'eliminar rol',
            'eliminar usuario',
            'eliminar administrar prestamo',
        ];

        $allPermissions = collect(array_merge($policyPermissions, $legacyPermissions))
            ->filter()
            ->unique()
            ->values();

        foreach ($allPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin->syncPermissions($allPermissions->all());
        $admin->syncPermissions($allPermissions->all());

        $teacherAndStudentPermissions = [
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel horarios',
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'crear solicitud prestamo',
            'crear reservar espacio',
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
        ];

        $teacher->syncPermissions($teacherAndStudentPermissions);
        $student->syncPermissions($teacherAndStudentPermissions);

        $laboratory->syncPermissions([
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel solicitud reservas',
            'ver panel horarios',
            'ver panel laboratorios',
            'ver panel inventario',
            'ver panel roles',
            'ver panel usuarios',
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'ver cualquier solicitud reserva',
            'ver cualquier horario',
            'ver cualquier laboratorio',
            'ver cualquier inventario',
            'ver cualquier rol',
            'ver cualquier usuario',
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'actualizar solicitud reserva',
            'actualizar horario',
            'actualizar laboratorio',
            'actualizar inventario',
            'actualizar rol',
            'actualizar usuario',
            'crear solicitud prestamo',
            'crear reservar espacio',
            'crear solicitud reserva',
            'crear horario',
            'crear laboratorio',
            'crear inventario',
            'crear rol',
            'crear usuario',
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
            'eliminar solicitud reserva',
            'eliminar laboratorio',
            'eliminar inventario',
            'eliminar rol',
            'eliminar usuario',
        ]);

        $coordinator->syncPermissions([
            'ver panel solicitudes prestamos',
            'ver panel reservar espacios',
            'ver panel solicitud reservas',
            'ver panel horarios',
            'ver cualquier solicitud prestamo',
            'ver cualquier reservar espacio',
            'ver cualquier solicitud reserva',
            'ver cualquier horario',
            'actualizar solicitud prestamo',
            'actualizar reservar espacio',
            'actualizar solicitud reserva',
            'actualizar horario',
            'crear solicitud prestamo',
            'crear reservar espacio',
            'crear solicitud reserva',
            'crear horario',
            'eliminar solicitud prestamo',
            'eliminar reservar espacio',
            'eliminar solicitud reserva',
            'eliminar horario',
        ]);
    }
}
