<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Livewire\Inventory\InventoryList;
use App\Livewire\Inventory\StockAdjustmentForm;
use App\Livewire\Inventory\StockMovementHistory;
use App\Services\StockService;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_inventory_list(): void
    {
        Product::factory()->count(3)->create(['stock' => 10]);

        $this->get(route('inventory.index'))
            ->assertStatus(200)
            ->assertSee('Inventory & Stock', false);
    }

    public function test_can_search_inventory(): void
    {
        Product::factory()->create(['name' => 'Kopi Tubruk', 'stock' => 5]);
        Product::factory()->create(['name' => 'Es Teh', 'stock' => 20]);

        Livewire::test(InventoryList::class)
            ->set('search', 'Kopi')
            ->assertSee('Kopi Tubruk')
            ->assertDontSee('Es Teh');
    }

    public function test_can_increase_stock_via_form(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        Livewire::test(StockAdjustmentForm::class, ['productId' => $product->id])
            ->set('type', 'in')
            ->set('quantity', 5)
            ->set('note', 'Restock from supplier')
            ->call('save')
            ->assertDispatched('stock-updated');

        $this->assertEquals(15, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'note' => 'Restock from supplier'
        ]);
    }

    public function test_can_decrease_stock_via_form(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        Livewire::test(StockAdjustmentForm::class, ['productId' => $product->id])
            ->set('type', 'out')
            ->set('quantity', 3)
            ->set('note', 'Expired')
            ->call('save')
            ->assertDispatched('stock-updated');

        $this->assertEquals(7, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
            'note' => 'Expired'
        ]);
    }

    public function test_cannot_decrease_stock_below_zero(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        Livewire::test(StockAdjustmentForm::class, ['productId' => $product->id])
            ->set('type', 'out')
            ->set('quantity', 5)
            ->call('save')
            ->assertHasErrors(['quantity']);

        $this->assertEquals(2, $product->fresh()->stock);
    }

    public function test_can_adjust_stock_via_form(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        Livewire::test(StockAdjustmentForm::class, ['productId' => $product->id])
            ->set('type', 'adjustment')
            ->set('quantity', 50)
            ->set('note', 'Stock opname')
            ->call('save')
            ->assertDispatched('stock-updated');

        $this->assertEquals(50, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 50,
            'note' => 'Stock opname'
        ]);
    }

    public function test_can_view_stock_movement_history(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        
        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 5,
            'reference_type' => 'manual',
            'note' => 'Test In'
        ]);

        Livewire::test(StockMovementHistory::class, ['productId' => $product->id])
            ->assertSee('Test In')
            ->assertSee('+5');
    }
}
