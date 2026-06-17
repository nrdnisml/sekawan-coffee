<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(StockService $stockService): void
    {
        $faker = Faker::create();
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->info('No products found. Please seed products first.');
            return;
        }

        foreach ($products as $product) {
            // Ensure product starts with some stock if it was 0
            if ($product->stock == 0) {
                $stockService->increaseStock(
                    $product->id, 
                    $faker->numberBetween(50, 100), 
                    'manual', 
                    null, 
                    'Initial stock seeding'
                );
            }

            // Create 3-7 random movements per product
            $numMovements = $faker->numberBetween(3, 7);

            for ($i = 0; $i < $numMovements; $i++) {
                $type = $faker->randomElement(['in', 'out', 'adjustment']);
                
                try {
                    if ($type === 'in') {
                        $stockService->increaseStock(
                            $product->id,
                            $faker->numberBetween(5, 20),
                            'manual',
                            null,
                            $faker->randomElement(['Restock', 'Supplier delivery', 'Found in warehouse'])
                        );
                    } elseif ($type === 'out') {
                        // Only decrease if we have enough stock (StockService handles this, but let's be safe)
                        $quantity = $faker->numberBetween(1, 5);
                        if ($product->fresh()->stock >= $quantity) {
                            $stockService->decreaseStock(
                                $product->id,
                                $quantity,
                                'manual',
                                null,
                                $faker->randomElement(['Damaged', 'Expired', 'Staff consumption'])
                            );
                        }
                    } else {
                        // Adjustment
                        $stockService->adjustStock(
                            $product->id,
                            $faker->numberBetween(10, 100),
                            'Stock opname adjustment'
                        );
                    }
                } catch (\Exception $e) {
                    // Skip if service throws exception (e.g. insufficient stock)
                    continue;
                }
            }
        }
    }
}
