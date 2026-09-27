<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Sample Laptop',
            'description' => 'High performance laptop with 16GB RAM and 512GB SSD',
            'price' => 999.99,
            'stock' => 25,
        ]);

        Product::create([
            'name' => 'Wireless Headphones',
            'description' => 'Noise-canceling over-ear bluetooth headphones',
            'price' => 149.50,
            'stock' => 50,
        ]);

        Product::create([
            'name' => 'Ergonomic Chair',
            'description' => 'Adjustable mesh office chair with lumbar support',
            'price' => 229.00,
            'stock' => 12,
        ]);
    }
}
