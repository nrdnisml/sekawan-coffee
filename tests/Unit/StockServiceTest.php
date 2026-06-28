<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);
    }

    public function test_increase_stock_updates_inventory_and_records_movement(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->stockService->increaseStock($product->id, 5, 'manual', null, 'Restock');

        $this->assertSame(15, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'reference_type' => 'manual',
            'note' => 'Restock',
        ]);
    }

    public function test_decrease_stock_updates_inventory_and_records_movement(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->stockService->decreaseStock($product->id, 4, 'manual', null, 'Shrinkage');

        $this->assertSame(6, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 4,
            'reference_type' => 'manual',
            'note' => 'Shrinkage',
        ]);
    }

    public function test_adjust_stock_sets_absolute_stock_value_and_records_movement(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->stockService->adjustStock($product->id, 6, 'Stock opname');

        $this->assertSame(6, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 6,
            'reference_type' => 'manual',
            'note' => 'Stock opname',
        ]);
    }

    public function test_decrease_stock_rejects_insufficient_inventory_without_recording_movement(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'name' => 'Arabica Beans']);

        $this->expectExceptionMessage('Stok produk Arabica Beans tidak mencukupi.');

        try {
            $this->stockService->decreaseStock($product->id, 3, 'manual', null, 'Damaged');
        } finally {
            $this->assertSame(2, $product->fresh()->stock);
            $this->assertDatabaseMissing('stock_movements', [
                'product_id' => $product->id,
                'type' => 'out',
                'quantity' => 3,
                'note' => 'Damaged',
            ]);
        }
    }
}
