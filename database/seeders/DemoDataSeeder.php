<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Loan;
use App\Models\Maintenance;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->first();

        if (! $superAdmin) {
            $this->call(UsersSeeder::class);

            $superAdmin = User::query()->where('email', env('SUPER_ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        }

        Auth::guard('web')->setUser($superAdmin);

        $users = $this->seedUsers();
        $programs = $this->seedAcademicPrograms();
        $laboratories = $this->seedLaboratories($users);
        $products = $this->seedProducts($laboratories, $superAdmin);
        $schedules = $this->seedSchedules($laboratories, $superAdmin);

        $this->seedBookings($schedules['open'], $programs, $laboratories, $users, $superAdmin);
        $this->seedLoans($products, $users);
        $this->seedMaintenances($products, $users, $superAdmin);

        Auth::guard('web')->logout();

        $this->command?->info('Demo data seeded successfully.');
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $password = (string) env('SUPER_ADMIN_PASSWORD', 'Demo12345!');

        $definitions = [
            'admin_ops' => [
                'email' => 'operaciones.demo@example.com',
                'roles' => ['ADMIN'],
                'name' => 'Paula',
                'last_name' => 'Mendoza',
                'document_number' => '1001001001',
                'phone' => '3001001001',
                'address' => 'Bloque administrativo',
            ],
            'coordinator' => [
                'email' => 'coordinacion.demo@example.com',
                'roles' => ['COORDINADOR'],
                'name' => 'Camilo',
                'last_name' => 'Benitez',
                'document_number' => '1001001002',
                'phone' => '3001001002',
                'address' => 'Facultad de Ingenierias',
            ],
            'lab_north' => [
                'email' => 'lab.norte.demo@example.com',
                'roles' => ['LABORATORISTA'],
                'name' => 'Laura',
                'last_name' => 'Rosero',
                'document_number' => '1001001003',
                'phone' => '3001001003',
                'address' => 'Edificio de laboratorios norte',
            ],
            'lab_south' => [
                'email' => 'lab.sur.demo@example.com',
                'roles' => ['LABORATORISTA'],
                'name' => 'Andres',
                'last_name' => 'Paredes',
                'document_number' => '1001001004',
                'phone' => '3001001004',
                'address' => 'Edificio de laboratorios sur',
            ],
            'teacher_env' => [
                'email' => 'docente.ambiental.demo@example.com',
                'roles' => ['DOCENTE'],
                'name' => 'Natalia',
                'last_name' => 'Caicedo',
                'document_number' => '1001001005',
                'phone' => '3001001005',
                'address' => 'Programa ambiental',
            ],
            'teacher_process' => [
                'email' => 'docente.procesos.demo@example.com',
                'roles' => ['DOCENTE'],
                'name' => 'Miguel',
                'last_name' => 'Santacruz',
                'document_number' => '1001001006',
                'phone' => '3001001006',
                'address' => 'Programa de procesos',
            ],
            'student_civil' => [
                'email' => 'estudiante.civil.demo@example.com',
                'roles' => ['ESTUDIANTE'],
                'name' => 'Sofia',
                'last_name' => 'Ortiz',
                'document_number' => '1001001007',
                'phone' => '3001001007',
                'address' => 'Residencias A',
            ],
            'student_mechatronics' => [
                'email' => 'estudiante.meca.demo@example.com',
                'roles' => ['ESTUDIANTE'],
                'name' => 'Diego',
                'last_name' => 'Munoz',
                'document_number' => '1001001008',
                'phone' => '3001001008',
                'address' => 'Residencias B',
            ],
        ];

        $users = [];

        foreach ($definitions as $key => $definition) {
            $user = User::query()->firstOrNew(['email' => $definition['email']]);
            $user->fill([
                'name' => $definition['name'],
                'last_name' => $definition['last_name'],
                'phone' => $definition['phone'],
                'address' => $definition['address'],
                'status' => 'active',
                'document_number' => $definition['document_number'],
                'locale' => 'es',
            ]);

            if (! $user->exists || ! Hash::check($password, (string) $user->password)) {
                $user->password = Hash::make($password);
            }

            $user->save();
            $user->syncRoles($definition['roles']);

            $users[$key] = $user;
        }

        return $users;
    }

    /**
     * @return array<string, AcademicProgram>
     */
    private function seedAcademicPrograms(): array
    {
        $definitions = [
            ['code' => 'IAMB', 'name' => 'Ingenieria Ambiental', 'faculty' => 'Ingenieria'],
            ['code' => 'ICIV', 'name' => 'Ingenieria Civil', 'faculty' => 'Ingenieria'],
            ['code' => 'IPRO', 'name' => 'Ingenieria de Procesos', 'faculty' => 'Ingenieria'],
            ['code' => 'IMEC', 'name' => 'Ingenieria Mecatronica', 'faculty' => 'Ingenieria'],
            ['code' => 'ISIS', 'name' => 'Ingenieria de Sistemas', 'faculty' => 'Ingenieria'],
            ['code' => 'IBIO', 'name' => 'Biotecnologia', 'faculty' => 'Ciencias Aplicadas'],
        ];

        $programs = [];

        foreach ($definitions as $definition) {
            $program = AcademicProgram::query()->updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'faculty' => $definition['faculty'],
                    'is_active' => true,
                ],
            );

            $programs[$definition['code']] = $program;
        }

        return $programs;
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Laboratory>
     */
    private function seedLaboratories(array $users): array
    {
        $definitions = [
            ['name' => 'Quimica', 'location' => 'Bloque A - Piso 2', 'capacity' => 28, 'user' => 'lab_north'],
            ['name' => 'Fisica', 'location' => 'Bloque A - Piso 3', 'capacity' => 32, 'user' => 'lab_north'],
            ['name' => 'Biologia', 'location' => 'Bloque B - Piso 1', 'capacity' => 24, 'user' => 'lab_south'],
            ['name' => 'Electronica', 'location' => 'Bloque C - Piso 2', 'capacity' => 26, 'user' => 'lab_south'],
            ['name' => 'Automatizacion', 'location' => 'Bloque C - Piso 3', 'capacity' => 22, 'user' => 'lab_south'],
            ['name' => 'Materiales', 'location' => 'Bloque D - Piso 1', 'capacity' => 20, 'user' => 'lab_north'],
            ['name' => 'Fluidos', 'location' => 'Bloque D - Piso 2', 'capacity' => 18, 'user' => 'lab_north'],
            ['name' => 'Topografia', 'location' => 'Zona de practicas externas', 'capacity' => 16, 'user' => 'lab_south'],
        ];

        $laboratories = [];

        foreach ($definitions as $definition) {
            $laboratory = Laboratory::query()->updateOrCreate(
                ['name' => $definition['name']],
                [
                    'location' => $definition['location'],
                    'capacity' => $definition['capacity'],
                    'user_id' => $users[$definition['user']]->id,
                ],
            );

            $laboratories[$definition['name']] = $laboratory;
        }

        return $laboratories;
    }

    /**
     * @param  array<string, Laboratory>  $laboratories
     * @return array<string, Product>
     */
    private function seedProducts(array $laboratories, User $superAdmin): array
    {
        $definitions = [
            [
                'serial_number' => 'DEMO-QUIM-001',
                'name' => 'Balanza Analitica Ohaus',
                'laboratory' => 'Quimica',
                'product_type' => 'equipment',
                'status' => 'new',
                'available_for_loan' => true,
                'available_quantity' => 2,
                'location' => 'Estacion Q-01',
                'brand' => 'Ohaus',
                'model' => 'PX224',
                'cost' => 5200,
                'acquired_days_ago' => 240,
            ],
            [
                'serial_number' => 'DEMO-QUIM-002',
                'name' => 'Medidor de pH Hanna',
                'laboratory' => 'Quimica',
                'product_type' => 'equipment',
                'status' => 'used',
                'available_for_loan' => true,
                'available_quantity' => 4,
                'location' => 'Estacion Q-02',
                'brand' => 'Hanna',
                'model' => 'HI5221',
                'cost' => 1800,
                'acquired_days_ago' => 420,
            ],
            [
                'serial_number' => 'DEMO-FIS-001',
                'name' => 'Kit de Movimiento Uniforme',
                'laboratory' => 'Fisica',
                'product_type' => 'equipment',
                'status' => 'used',
                'available_for_loan' => false,
                'available_quantity' => 1,
                'location' => 'Mesa F-01',
                'brand' => 'Phywe',
                'model' => 'Linear Motion',
                'cost' => 2100,
                'acquired_days_ago' => 510,
            ],
            [
                'serial_number' => 'DEMO-FIS-002',
                'name' => 'Osciloscopio Digital Rigol',
                'laboratory' => 'Electronica',
                'product_type' => 'equipment',
                'status' => 'new',
                'available_for_loan' => true,
                'available_quantity' => 3,
                'location' => 'Mesa E-03',
                'brand' => 'Rigol',
                'model' => 'DS1054Z',
                'cost' => 3400,
                'acquired_days_ago' => 180,
            ],
            [
                'serial_number' => 'DEMO-BIO-001',
                'name' => 'Microscopio Binocular Leica',
                'laboratory' => 'Biologia',
                'product_type' => 'equipment',
                'status' => 'maintenance',
                'available_for_loan' => false,
                'available_quantity' => 1,
                'location' => 'Mesa B-02',
                'brand' => 'Leica',
                'model' => 'DM500',
                'cost' => 4600,
                'acquired_days_ago' => 365,
            ],
            [
                'serial_number' => 'DEMO-BIO-002',
                'name' => 'Incubadora de Cultivos',
                'laboratory' => 'Biologia',
                'product_type' => 'equipment',
                'status' => 'used',
                'available_for_loan' => false,
                'available_quantity' => 1,
                'location' => 'Zona B-05',
                'brand' => 'Memmert',
                'model' => 'IN55',
                'cost' => 6200,
                'acquired_days_ago' => 640,
            ],
            [
                'serial_number' => 'DEMO-AUTO-001',
                'name' => 'Modulo PLC Siemens',
                'laboratory' => 'Automatizacion',
                'product_type' => 'equipment',
                'status' => 'new',
                'available_for_loan' => true,
                'available_quantity' => 5,
                'location' => 'Rack A-01',
                'brand' => 'Siemens',
                'model' => 'S7-1200',
                'cost' => 3900,
                'acquired_days_ago' => 90,
            ],
            [
                'serial_number' => 'DEMO-AUTO-002',
                'name' => 'Sensor de Proximidad',
                'laboratory' => 'Automatizacion',
                'product_type' => 'supply',
                'status' => 'new',
                'available_for_loan' => true,
                'available_quantity' => 12,
                'location' => 'Rack A-03',
                'brand' => 'Autonics',
                'model' => 'PR18',
                'cost' => 180,
                'acquired_days_ago' => 45,
            ],
            [
                'serial_number' => 'DEMO-MAT-001',
                'name' => 'Prensa de Compresion',
                'laboratory' => 'Materiales',
                'product_type' => 'equipment',
                'status' => 'used',
                'available_for_loan' => false,
                'available_quantity' => 1,
                'location' => 'Zona M-01',
                'brand' => 'Controls',
                'model' => '50-C56',
                'cost' => 11000,
                'acquired_days_ago' => 780,
            ],
            [
                'serial_number' => 'DEMO-MAT-002',
                'name' => 'Moldes Cilindricos',
                'laboratory' => 'Materiales',
                'product_type' => 'consumable',
                'status' => 'new',
                'available_for_loan' => false,
                'available_quantity' => 20,
                'location' => 'Bodega M-02',
                'brand' => 'TecniTest',
                'model' => 'CIL-150',
                'cost' => 40,
                'acquired_days_ago' => 60,
            ],
            [
                'serial_number' => 'DEMO-FLU-001',
                'name' => 'Banco de Ensayo Hidraulico',
                'laboratory' => 'Fluidos',
                'product_type' => 'equipment',
                'status' => 'damaged',
                'available_for_loan' => false,
                'available_quantity' => 1,
                'location' => 'Zona F-02',
                'brand' => 'Armfield',
                'model' => 'F1-10',
                'cost' => 9800,
                'acquired_days_ago' => 900,
            ],
            [
                'serial_number' => 'DEMO-TOP-001',
                'name' => 'Estacion Total Topcon',
                'laboratory' => 'Topografia',
                'product_type' => 'equipment',
                'status' => 'used',
                'available_for_loan' => true,
                'available_quantity' => 2,
                'location' => 'Locker T-01',
                'brand' => 'Topcon',
                'model' => 'GM-55',
                'cost' => 7200,
                'acquired_days_ago' => 300,
            ],
        ];

        $products = [];

        foreach ($definitions as $definition) {
            $product = Product::query()->updateOrCreate(
                ['serial_number' => $definition['serial_number']],
                [
                    'name' => $definition['name'],
                    'description' => 'Registro demo para exhibir inventario, prestamos y mantenimiento.',
                    'available_quantity' => $definition['available_quantity'],
                    'laboratory_id' => $laboratories[$definition['laboratory']]->id,
                    'unit_cost' => $definition['cost'],
                    'location' => $definition['location'],
                    'acquisition_date' => now()->subDays($definition['acquired_days_ago'])->toDateString(),
                    'use' => 'Practicas academicas y demostraciones controladas.',
                    'applies_to' => ['docencia', 'investigacion'],
                    'authorized_personnel' => ['docentes', 'laboratoristas'],
                    'brand' => $definition['brand'],
                    'model' => $definition['model'],
                    'manufacturer' => $definition['brand'],
                    'calibration_frequency' => 'anual',
                    'observations' => 'Activo demo para panel administrativo.',
                    'product_type' => $definition['product_type'],
                    'status' => $definition['status'],
                    'available_for_loan' => $definition['available_for_loan'],
                    'created_by' => $superAdmin->id,
                    'updated_by' => $superAdmin->id,
                ],
            );

            $products[$definition['serial_number']] = $product;
        }

        return $products;
    }

    /**
     * @param  array<string, Laboratory>  $laboratories
     * @return array{structured: array<string, Schedule>, open: array<string, Schedule>}
     */
    private function seedSchedules(array $laboratories, User $superAdmin): array
    {
        $baseWeek = now()->startOfWeek();

        $structuredDefinitions = [
            ['key' => 'chem_lab', 'laboratory' => 'Quimica', 'title' => 'Demo - Quimica Analitica', 'day' => 1, 'start' => '08:00', 'end' => '10:00', 'weeks' => 12, 'color' => '#e11d48'],
            ['key' => 'physics_lab', 'laboratory' => 'Fisica', 'title' => 'Demo - Fisica Experimental', 'day' => 2, 'start' => '10:00', 'end' => '12:00', 'weeks' => 10, 'color' => '#2563eb'],
            ['key' => 'bio_lab', 'laboratory' => 'Biologia', 'title' => 'Demo - Microbiologia Aplicada', 'day' => 3, 'start' => '07:00', 'end' => '09:00', 'weeks' => 14, 'color' => '#16a34a'],
            ['key' => 'electronics_lab', 'laboratory' => 'Electronica', 'title' => 'Demo - Electronica Analogica', 'day' => 4, 'start' => '13:00', 'end' => '15:00', 'weeks' => 16, 'color' => '#7c3aed'],
            ['key' => 'automation_lab', 'laboratory' => 'Automatizacion', 'title' => 'Demo - PLC y Automatizacion', 'day' => 5, 'start' => '09:00', 'end' => '12:00', 'weeks' => 8, 'color' => '#ea580c'],
            ['key' => 'materials_lab', 'laboratory' => 'Materiales', 'title' => 'Demo - Tecnologia del Concreto', 'day' => 4, 'start' => '07:00', 'end' => '10:00', 'weeks' => 9, 'color' => '#64748b'],
        ];

        $openDefinitions = [
            ['key' => 'open_quimica_1', 'laboratory' => 'Quimica', 'title' => 'Demo abierta Quimica 1', 'offset_days' => 2, 'start' => '14:00', 'end' => '16:00'],
            ['key' => 'open_quimica_2', 'laboratory' => 'Quimica', 'title' => 'Demo abierta Quimica 2', 'offset_days' => 6, 'start' => '09:00', 'end' => '11:00'],
            ['key' => 'open_fisica_1', 'laboratory' => 'Fisica', 'title' => 'Demo abierta Fisica 1', 'offset_days' => 3, 'start' => '08:00', 'end' => '10:00'],
            ['key' => 'open_biologia_1', 'laboratory' => 'Biologia', 'title' => 'Demo abierta Biologia 1', 'offset_days' => 4, 'start' => '10:00', 'end' => '12:00'],
            ['key' => 'open_electronica_1', 'laboratory' => 'Electronica', 'title' => 'Demo abierta Electronica 1', 'offset_days' => 5, 'start' => '15:00', 'end' => '17:00'],
            ['key' => 'open_auto_1', 'laboratory' => 'Automatizacion', 'title' => 'Demo abierta Automatizacion 1', 'offset_days' => 7, 'start' => '08:00', 'end' => '10:00'],
            ['key' => 'open_mat_1', 'laboratory' => 'Materiales', 'title' => 'Demo abierta Materiales 1', 'offset_days' => 8, 'start' => '10:00', 'end' => '12:00'],
            ['key' => 'open_topo_1', 'laboratory' => 'Topografia', 'title' => 'Demo abierta Topografia 1', 'offset_days' => 9, 'start' => '07:00', 'end' => '09:00'],
        ];

        $structured = [];
        $open = [];

        foreach ($structuredDefinitions as $definition) {
            $startAt = $baseWeek->copy()->addDays($definition['day'] - 1)->setTimeFromTimeString($definition['start']);
            $endAt = $baseWeek->copy()->addDays($definition['day'] - 1)->setTimeFromTimeString($definition['end']);

            $structured[$definition['key']] = Schedule::query()->updateOrCreate(
                [
                    'laboratory_id' => $laboratories[$definition['laboratory']]->id,
                    'title' => $definition['title'],
                    'start_at' => $startAt,
                ],
                [
                    'user_id' => $superAdmin->id,
                    'end_at' => $endAt,
                    'description' => 'Horario estructurado de demostracion para el panel.',
                    'color' => $definition['color'],
                    'type' => 'structured',
                    'recurrence_days' => (string) $definition['day'],
                    'recurrence_until' => $startAt->copy()->addWeeks($definition['weeks'])->toDateString(),
                ],
            );
        }

        foreach ($openDefinitions as $definition) {
            $startAt = now()->addDays($definition['offset_days'])->setTimeFromTimeString($definition['start']);
            $endAt = now()->addDays($definition['offset_days'])->setTimeFromTimeString($definition['end']);

            $open[$definition['key']] = Schedule::query()->updateOrCreate(
                [
                    'laboratory_id' => $laboratories[$definition['laboratory']]->id,
                    'title' => $definition['title'],
                    'start_at' => $startAt,
                ],
                [
                    'user_id' => $superAdmin->id,
                    'end_at' => $endAt,
                    'description' => 'Franja abierta para reservas demo.',
                    'color' => '#0f766e',
                    'type' => 'unstructured',
                    'recurrence_days' => null,
                    'recurrence_until' => null,
                ],
            );
        }

        return [
            'structured' => $structured,
            'open' => $open,
        ];
    }

    /**
     * @param  array<string, Schedule>  $openSchedules
     * @param  array<string, AcademicProgram>  $programs
     * @param  array<string, Laboratory>  $laboratories
     * @param  array<string, User>  $users
     */
    private function seedBookings(array $openSchedules, array $programs, array $laboratories, array $users, User $superAdmin): void
    {
        $definitions = [
            [
                'token' => 'DEMO-BOOK-001',
                'schedule' => 'open_quimica_1',
                'user' => 'teacher_env',
                'program' => 'IAMB',
                'status' => Booking::STATUS_APPROVED,
                'project_type' => 'Investigacion',
                'semester' => 6,
                'applicants' => 'Natalia Caicedo, Semillero AquaLab',
                'advisor' => 'Dra. Elena Paz',
                'created_weeks_ago' => 6,
            ],
            [
                'token' => 'DEMO-BOOK-002',
                'schedule' => 'open_quimica_2',
                'user' => 'student_civil',
                'program' => 'ICIV',
                'status' => Booking::STATUS_PENDING,
                'project_type' => 'Clase',
                'semester' => 4,
                'applicants' => 'Sofia Ortiz, Grupo Concretos 401',
                'advisor' => 'Ing. Mauricio Chicaiza',
                'created_weeks_ago' => 2,
            ],
            [
                'token' => 'DEMO-BOOK-003',
                'schedule' => 'open_fisica_1',
                'user' => 'teacher_process',
                'program' => 'IPRO',
                'status' => Booking::STATUS_APPROVED,
                'project_type' => 'Practica',
                'semester' => 5,
                'applicants' => 'Miguel Santacruz, Grupo Procesos V',
                'advisor' => 'Ing. Diana Viveros',
                'created_weeks_ago' => 5,
            ],
            [
                'token' => 'DEMO-BOOK-004',
                'schedule' => 'open_biologia_1',
                'user' => 'student_mechatronics',
                'program' => 'IMEC',
                'status' => Booking::STATUS_REJECTED,
                'project_type' => 'Proyecto',
                'semester' => 7,
                'applicants' => 'Diego Munoz, Equipo BioMeca',
                'advisor' => 'Mg. Lina Chamorro',
                'created_weeks_ago' => 3,
            ],
            [
                'token' => 'DEMO-BOOK-005',
                'schedule' => 'open_electronica_1',
                'user' => 'teacher_process',
                'program' => 'ISIS',
                'status' => Booking::STATUS_PENDING,
                'project_type' => 'Investigacion',
                'semester' => 8,
                'applicants' => 'Semillero IoT Aplicado',
                'advisor' => 'Mg. Carlos Villota',
                'created_weeks_ago' => 1,
            ],
            [
                'token' => 'DEMO-BOOK-006',
                'schedule' => 'open_auto_1',
                'user' => 'coordinator',
                'program' => 'IMEC',
                'status' => Booking::STATUS_APPROVED,
                'project_type' => 'Evento',
                'semester' => 0,
                'applicants' => 'Coordinacion de programa',
                'advisor' => 'Coord. Camilo Benitez',
                'created_weeks_ago' => 4,
            ],
        ];

        foreach ($definitions as $definition) {
            $schedule = $openSchedules[$definition['schedule']];
            $user = $users[$definition['user']];
            $timestamp = now()->subWeeks($definition['created_weeks_ago'])->subDays(1);

            $booking = Booking::query()->updateOrCreate(
                ['research_name' => $definition['token']],
                [
                    'schedule_id' => $schedule->id,
                    'user_id' => $user->id,
                    'laboratory_id' => $schedule->laboratory_id,
                    'project_type' => $definition['project_type'],
                    'academic_program' => $programs[$definition['program']]->name,
                    'semester' => $definition['semester'] ?: null,
                    'applicants' => $definition['applicants'],
                    'advisor' => $definition['advisor'],
                    'products' => ['kits_basicos', 'material_apoyo'],
                    'start_at' => $schedule->start_at,
                    'end_at' => $schedule->end_at,
                    'color' => '#0ea5e9',
                    'status' => $definition['status'],
                    'created_by' => $superAdmin->id,
                    'updated_by' => $superAdmin->id,
                ],
            );

            $booking->forceFill([
                'created_at' => $timestamp,
                'updated_at' => $timestamp->copy()->addHours(3),
            ])->saveQuietly();
        }
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, User>  $users
     */
    private function seedLoans(array $products, array $users): void
    {
        $definitions = [
            [
                'token' => 'DEMO-LOAN-001',
                'product' => 'DEMO-TOP-001',
                'user' => 'student_civil',
                'status' => Loan::STATUS_PENDING,
                'requested_days_ago' => 1,
                'approved_days_ago' => null,
                'estimated_return_days' => 5,
                'actual_return_days_ago' => null,
            ],
            [
                'token' => 'DEMO-LOAN-002',
                'product' => 'DEMO-AUTO-001',
                'user' => 'student_mechatronics',
                'status' => Loan::STATUS_APPROVED,
                'requested_days_ago' => 8,
                'approved_days_ago' => 7,
                'estimated_return_days' => 2,
                'actual_return_days_ago' => null,
            ],
            [
                'token' => 'DEMO-LOAN-003',
                'product' => 'DEMO-QUIM-002',
                'user' => 'teacher_env',
                'status' => Loan::STATUS_RETURNED,
                'requested_days_ago' => 18,
                'approved_days_ago' => 17,
                'estimated_return_days' => -8,
                'actual_return_days_ago' => 10,
            ],
            [
                'token' => 'DEMO-LOAN-004',
                'product' => 'DEMO-FIS-002',
                'user' => 'teacher_process',
                'status' => Loan::STATUS_REJECTED,
                'requested_days_ago' => 5,
                'approved_days_ago' => null,
                'estimated_return_days' => null,
                'actual_return_days_ago' => null,
            ],
            [
                'token' => 'DEMO-LOAN-005',
                'product' => 'DEMO-AUTO-002',
                'user' => 'teacher_process',
                'status' => Loan::STATUS_APPROVED,
                'requested_days_ago' => 3,
                'approved_days_ago' => 2,
                'estimated_return_days' => 12,
                'actual_return_days_ago' => null,
            ],
        ];

        foreach ($definitions as $definition) {
            $requestedAt = now()->subDays($definition['requested_days_ago']);
            $approvedAt = $definition['approved_days_ago'] !== null ? now()->subDays($definition['approved_days_ago']) : null;
            $estimatedReturnAt = $definition['estimated_return_days'] !== null ? now()->addDays($definition['estimated_return_days']) : null;
            $actualReturnAt = $definition['actual_return_days_ago'] !== null ? now()->subDays($definition['actual_return_days_ago']) : null;

            $loan = Loan::query()->updateOrCreate(
                ['observations' => $definition['token']],
                [
                    'product_id' => $products[$definition['product']]->id,
                    'user_id' => $users[$definition['user']]->id,
                    'status' => $definition['status'],
                    'requested_at' => $requestedAt,
                    'approved_at' => $approvedAt,
                    'estimated_return_at' => $estimatedReturnAt,
                    'actual_return_at' => $actualReturnAt,
                ],
            );

            $loan->forceFill([
                'created_at' => $requestedAt,
                'updated_at' => $approvedAt ?? $requestedAt,
            ])->saveQuietly();
        }
    }

    /**
     * @param  array<string, Product>  $products
     * @param  array<string, User>  $users
     */
    private function seedMaintenances(array $products, array $users, User $superAdmin): void
    {
        $definitions = [
            [
                'token' => 'DEMO-MNT-001',
                'product' => 'DEMO-BIO-001',
                'type' => 'corrective',
                'status' => 'in_progress',
                'scheduled_days_ago' => 2,
                'started_days_ago' => 1,
                'completed_days_ago' => null,
                'next_days' => 45,
                'provider' => 'BioService SAS',
                'cost' => 850000,
                'performed_by' => 'lab_south',
            ],
            [
                'token' => 'DEMO-MNT-002',
                'product' => 'DEMO-QUIM-001',
                'type' => 'calibration',
                'status' => 'scheduled',
                'scheduled_days_ago' => -4,
                'started_days_ago' => null,
                'completed_days_ago' => null,
                'next_days' => 180,
                'provider' => 'Metrolab',
                'cost' => 320000,
                'performed_by' => 'lab_north',
            ],
            [
                'token' => 'DEMO-MNT-003',
                'product' => 'DEMO-FIS-002',
                'type' => 'preventive',
                'status' => 'completed',
                'scheduled_days_ago' => 20,
                'started_days_ago' => 19,
                'completed_days_ago' => 18,
                'next_days' => 120,
                'provider' => 'Electro Andina',
                'cost' => 210000,
                'performed_by' => 'lab_south',
            ],
            [
                'token' => 'DEMO-MNT-004',
                'product' => 'DEMO-FLU-001',
                'type' => 'corrective',
                'status' => 'cancelled',
                'scheduled_days_ago' => 12,
                'started_days_ago' => null,
                'completed_days_ago' => null,
                'next_days' => null,
                'provider' => 'Hidrotec',
                'cost' => 0,
                'performed_by' => 'lab_north',
            ],
        ];

        foreach ($definitions as $definition) {
            $scheduledAt = now()->subDays($definition['scheduled_days_ago']);
            $startedAt = $definition['started_days_ago'] !== null ? now()->subDays($definition['started_days_ago']) : null;
            $completedAt = $definition['completed_days_ago'] !== null ? now()->subDays($definition['completed_days_ago']) : null;
            $nextMaintenanceAt = $definition['next_days'] !== null ? now()->addDays($definition['next_days'])->toDateString() : null;

            Maintenance::query()->updateOrCreate(
                ['notes' => $definition['token']],
                [
                    'product_id' => $products[$definition['product']]->id,
                    'maintenance_type' => $definition['type'],
                    'status' => $definition['status'],
                    'scheduled_at' => $scheduledAt,
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                    'next_maintenance_at' => $nextMaintenanceAt,
                    'provider' => $definition['provider'],
                    'cost' => $definition['cost'],
                    'performed_by' => $users[$definition['performed_by']]->id,
                    'created_by' => $superAdmin->id,
                    'updated_by' => $superAdmin->id,
                ],
            );
        }
    }
}
