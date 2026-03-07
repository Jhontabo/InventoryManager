<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laboratory;
use App\Models\User;

class LaboratoriesSeeder extends Seeder
{
  public function run()
  {
    $laboratoristas = User::role('LABORATORISTA')->get();

    if ($laboratoristas->isEmpty()) {
      $this->command->info('No hay usuarios con el rol "LABORATORISTA".');
      return;
    }



    $labNames = [
      'Quimica',
      'Fisica',
      'Biologia',
      'Fisico-Quimica',
      'Fluidos',
      'Electronica',
      'STEAM',
      'Automatizacion',
      'Taller',
      'Materiales',
      'Operaciones',
      'Geotecnia',
      'Topografia',

    ];


    foreach ($labNames as $name) {
      Laboratory::create([
        'name'     => $name,
        'location' => 'Edificio principal, Piso 2',
        'capacity' => rand(11, 50),
        'user_id'  => $laboratoristas->random()->id,
      ]);
    }

    $this->command->info(count($labNames) . ' laboratorios creados correctamente.');
  }
}
