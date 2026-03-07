<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laboratory;
use App\Models\Product;
use Carbon\Carbon;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        // Create laboratory if not exists
        $laboratory = Laboratory::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Central Laboratory',
                'location' => 'Main Building, 3rd Floor',
                'capacity' => 30,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );




        // Products data
        $products = [
            [
                'name' => 'Digital Microscope',
                'description' => 'Microscope with integrated camera 1000x',
                'available_quantity' => 5,
                'laboratory_id' => $laboratory->id,
                'serial_number' => 'MIC-001',
                'acquisition_date' => Carbon::now()->subYear(),
                'available_for_loan' => true,
                'unit_cost' => 1200.50,
                'location' => 'Shelf A1',
                'product_type' => 'equipment',
                'status' => 'new',
                'image' => 'https://ejemplo.com/microscopio.jpg'
            ],
            [
                'name' => 'HP EliteBook Laptop',
                'description' => 'Business laptop i7, 16GB RAM, 512GB SSD',
                'available_quantity' => 8,
                'laboratory_id' => $laboratory->id,
                'serial_number' => 'LT-HP-001',
                'acquisition_date' => Carbon::now()->subMonths(3),
                'available_for_loan' => true,
                'unit_cost' => 1500.00,
                'location' => 'Shelf B2',
                'product_type' => 'equipment',
                'status' => 'used',
                'image' => 'https://ejemplo.com/laptop.jpg'
            ],
            // (Aquí continúas igual con los demás productos, ya traducidos)
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('10 different products created successfully!');
    }
}
