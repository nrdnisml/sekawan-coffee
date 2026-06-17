<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Sekawan',
                'email' => 'admin@sekawan.com',
                'role' => 'admin',
                'password' => bcrypt('password'), // Add a default password
            ]
        );

        // Create Cashier
        User::updateOrCreate(
            ['username' => 'cashier'],
            [
                'name' => 'Cashier Sekawan',
                'email' => 'cashier@sekawan.com',
                'role' => 'cashier',
                'password' => bcrypt('password'), // Add a default password
            ]
        );

        // Create Sample Products
        \App\Models\Product::create([
            'name' => 'Espresso',
            'price' => 15000,
            'description' => 'Single shot espresso',
            'stock' => 100,
            'is_active' => true,
        ]);

        \App\Models\Product::create([
            'name' => 'Caffe Latte',
            'price' => 25000,
            'description' => 'Espresso with steamed milk',
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->call([
            ProductSeeder::class,
            StockMovementSeeder::class,
        ]);
    }
}
