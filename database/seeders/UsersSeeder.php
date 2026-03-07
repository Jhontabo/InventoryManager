<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
  private const DEFAULT_ADMIN_EMAIL = 'jhonse.tajumbina@gmail.com';

  public function run(): void
  {
    $roles = [
      'ADMIN',
      'DOCENTE',
      'LABORATORISTA',
      'ESTUDIANTE',
      'COORDINADOR'
    ];

    foreach ($roles as $role) {
      Role::firstOrCreate(['name' => $role]);
    }

    $admin = User::firstOrCreate(
      ['email' => self::DEFAULT_ADMIN_EMAIL],
      [
        'name' => 'Admin',
        'last_name' => 'User',
        'password' => Hash::make('password'),
        'phone' => '123456789',
        'address' => 'Calle Falsa 123',
        'status' => 'active',
        'document_number' => '123456789'
      ]
    );

    $admin->assignRole('ADMIN');

    $this->command->info('Admin user created successfully.');
  }
}
