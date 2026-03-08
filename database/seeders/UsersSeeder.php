<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'SUPER-ADMIN',
            'ADMIN',
            'DOCENTE',
            'LABORATORISTA',
            'ESTUDIANTE',
            'COORDINADOR',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $superAdminEmail = (string) env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $superAdminPassword = env('SUPER_ADMIN_PASSWORD');

        $attributes = [
            'name' => 'Jhonse',
            'last_name' => 'Tajumbina',
            'phone' => '123456791',
            'address' => 'Calle Falsa 125',
            'status' => 'active',
            'document_number' => '123456791',
        ];

        $superAdmin = User::query()->where('email', $superAdminEmail)->first();

        if ($superAdminPassword) {
            $attributes['password'] = Hash::make($superAdminPassword);
        } elseif (! $superAdmin) {
            $generatedPassword = Str::password(16);
            $attributes['password'] = Hash::make($generatedPassword);
            $this->command?->warn("SUPER_ADMIN_PASSWORD no está definido. Contraseña temporal para {$superAdminEmail}: {$generatedPassword}");
        }

        $superAdmin = User::updateOrCreate(['email' => $superAdminEmail], $attributes);

        // Solo este correo debe tener SUPER-ADMIN.
        User::query()
            ->where('email', '!=', $superAdminEmail)
            ->whereHas('roles', fn ($query) => $query->where('name', 'SUPER-ADMIN'))
            ->get()
            ->each(fn (User $user) => $user->removeRole('SUPER-ADMIN'));

        // Conserva ADMIN para compatibilidad con policies que aún validan ese rol.
        $superAdmin->syncRoles(['SUPER-ADMIN', 'ADMIN']);

        $this->command?->info('Super admin synced successfully.');
    }
}
