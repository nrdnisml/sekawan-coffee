<?php

namespace Tests\Feature;

use App\Livewire\Inventory\InventoryList;
use App\Livewire\Inventory\StockAdjustmentForm;
use App\Livewire\Inventory\StockMovementHistory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
            ->assertSee('Inventaris & Stok', false);
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

    public function test_history_action_sets_selected_product_for_modal(): void
    {
        $product = Product::factory()->create();

        Livewire::test(InventoryList::class)
            ->call('openHistoryModal', $product->id)
            ->assertSet('selectedProductId', $product->id);
    }

    public function test_adjust_action_sets_product_for_adjustment_modal_and_resets_after_update(): void
    {
        $product = Product::factory()->create();

        Livewire::test(InventoryList::class)
            ->call('openAdjustmentModal', $product->id)
            ->assertSet('adjustingProductId', $product->id)
            ->call('onStockUpdated')
            ->assertSet('adjustingProductId', null);
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
            'note' => 'Restock from supplier',
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
            'note' => 'Expired',
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
            'note' => 'Stock opname',
        ]);
    }

    public function test_adjustment_flow_reports_missing_product_on_stale_inventory_record(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $component = Livewire::test(StockAdjustmentForm::class, ['productId' => $product->id]);

        $product->delete();

        $component
            ->set('type', 'adjustment')
            ->set('quantity', 8)
            ->set('note', 'Stock opname after stale modal')
            ->call('save')
            ->assertHasErrors(['productId']);

        $this->assertDatabaseMissing('stock_movements', [
            'type' => 'adjustment',
            'quantity' => 8,
            'note' => 'Stock opname after stale modal',
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
            'note' => 'Test In',
        ]);

        Livewire::test(StockMovementHistory::class, ['productId' => $product->id])
            ->assertSee('Test In')
            ->assertSee('+5');
    }

    public function test_stock_movement_history_shows_readable_manual_and_transaction_sources(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'adjustment',
            'quantity' => 8,
            'reference_type' => 'manual',
            'note' => 'Cycle count',
        ]);

        $transaction = Transaction::create([
            'transaction_code' => 'TXN-INV-001',
            'user_id' => $this->user->id,
            'total_amount' => $product->price,
            'paid_amount' => $product->price,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
            'note' => 'Sold via POS',
        ]);

        Livewire::test(StockMovementHistory::class, ['productId' => $product->id])
            ->assertSee('Penyesuaian manual')
            ->assertSee('Transaksi TXN-INV-001')
            ->assertSee('Cycle count')
            ->assertSee('Sold via POS');
    }

    public function test_transaction_creation_reduces_stock_and_logs_inventory_history(): void
    {
        $product = Product::factory()->create([
            'name' => 'House Blend',
            'price' => 12,
            'stock' => 10,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $this->user->id,
            [['product_id' => $product->id, 'quantity' => 3]],
            'cash',
            40
        );

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
        ]);
    }

    public function test_transaction_cancellation_restores_stock_and_logs_inventory_history(): void
    {
        $product = Product::factory()->create([
            'name' => 'House Blend',
            'price' => 12,
            'stock' => 10,
        ]);

        $transactionService = app(TransactionService::class);
        $transaction = $transactionService->createTransaction(
            $this->user->id,
            [['product_id' => $product->id, 'quantity' => 3]],
            'cash',
            40
        );

        $transactionService->cancelTransaction($transaction->id);

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 3,
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
            'note' => 'Stok dipulihkan dari transaksi batal: '.$transaction->transaction_code,
        ]);
    }

    public function test_transaction_refund_restores_stock_and_logs_inventory_history(): void
    {
        $product = Product::factory()->create([
            'name' => 'House Blend',
            'price' => 12,
            'stock' => 10,
        ]);

        $transactionService = app(TransactionService::class);
        $transaction = $transactionService->createTransaction(
            $this->user->id,
            [['product_id' => $product->id, 'quantity' => 2]],
            'cash',
            30
        );

        $transactionService->refundTransaction($transaction->id);

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 2,
            'reference_type' => 'transaction',
            'reference_id' => $transaction->id,
            'note' => 'Stok dipulihkan dari transaksi refund: '.$transaction->transaction_code,
        ]);
    }
}
