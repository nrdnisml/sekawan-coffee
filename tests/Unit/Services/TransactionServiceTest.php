<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use App\Services\TransactionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_transaction_updates_stock_and_writes_an_audit_log(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'House Blend',
            'price' => 15000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [['product_id' => $product->id, 'quantity' => 2]],
            'cash',
            40000
        );

        $this->assertStringStartsWith('TXN-', $transaction->transaction_code);
        $this->assertSame('30000.00', $transaction->total_amount);
        $this->assertSame('40000.00', $transaction->paid_amount);
        $this->assertSame('10000.00', $transaction->change_amount);
        $this->assertSame('completed', $transaction->status);
        $this->assertSame(8, $product->fresh()->stock);

        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => 'House Blend',
            'price' => '15000.00',
            'quantity' => 2,
            'subtotal' => '30000.00',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $cashier->id,
            'action' => 'create',
            'entity' => 'transactions',
            'entity_id' => $transaction->id,
            'description' => 'Membuat transaksi '.$transaction->transaction_code,
        ]);
    }

    public function test_it_rejects_empty_transaction_items(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        try {
            app(TransactionService::class)->createTransaction($cashier->id, [], 'cash', 10000);
            $this->fail('Expected the transaction creation to reject an empty cart.');
        } catch (Exception $exception) {
            $this->assertSame('Item transaksi tidak boleh kosong.', $exception->getMessage());
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('transaction_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_it_rejects_inactive_products(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'Retired Latte',
            'price' => 12000,
            'stock' => 5,
            'is_active' => false,
        ]);

        try {
            app(TransactionService::class)->createTransaction(
                $cashier->id,
                [['product_id' => $product->id, 'quantity' => 1]],
                'cash',
                12000
            );
            $this->fail('Expected the transaction creation to reject inactive products.');
        } catch (Exception $exception) {
            $this->assertSame('Produk Retired Latte sedang tidak aktif.', $exception->getMessage());
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_it_rejects_when_stock_is_insufficient(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'Cold Brew',
            'price' => 18000,
            'stock' => 1,
            'is_active' => true,
        ]);

        try {
            app(TransactionService::class)->createTransaction(
                $cashier->id,
                [['product_id' => $product->id, 'quantity' => 2]],
                'cash',
                50000
            );
            $this->fail('Expected the transaction creation to reject when stock is insufficient.');
        } catch (Exception $exception) {
            $this->assertSame('Stok produk Cold Brew tidak mencukupi.', $exception->getMessage());
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_it_rejects_when_paid_amount_is_less_than_the_total(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'Espresso',
            'price' => 20000,
            'stock' => 8,
            'is_active' => true,
        ]);

        try {
            app(TransactionService::class)->createTransaction(
                $cashier->id,
                [['product_id' => $product->id, 'quantity' => 2]],
                'cash',
                39000
            );
            $this->fail('Expected the transaction creation to reject underpaid sales.');
        } catch (Exception $exception) {
            $this->assertSame('Jumlah bayar lebih kecil dari total belanja.', $exception->getMessage());
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_it_keeps_item_snapshots_intact_after_the_product_changes(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'Signature Latte',
            'price' => 18000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [['product_id' => $product->id, 'quantity' => 1]],
            'cash',
            20000
        );

        $product->update([
            'name' => 'Seasonal Latte',
            'price' => 22000,
        ]);

        $item = $transaction->items()->firstOrFail();

        $this->assertSame('Signature Latte', $item->product_name);
        $this->assertSame('18000.00', $item->price);
        $this->assertSame('18000.00', $item->subtotal);
    }

    public function test_it_cancels_a_transaction_restores_stock_and_writes_an_audit_log(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'Manual Brew',
            'price' => 16000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [['product_id' => $product->id, 'quantity' => 3]],
            'cash',
            50000
        );

        $cancelledTransaction = app(TransactionService::class)->cancelTransaction($transaction->id);

        $this->assertSame('cancelled', $cancelledTransaction->status);
        $this->assertSame(10, $product->fresh()->stock);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $cashier->id,
            'action' => 'cancel',
            'entity' => 'transactions',
            'entity_id' => $transaction->id,
            'description' => 'Membatalkan transaksi '.$transaction->transaction_code,
        ]);
    }

    public function test_it_refunds_a_transaction_restores_stock_and_writes_an_audit_log(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $product = Product::factory()->create([
            'name' => 'Affogato',
            'price' => 22000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [['product_id' => $product->id, 'quantity' => 2]],
            'cash',
            50000
        );

        $refundedTransaction = app(TransactionService::class)->refundTransaction($transaction->id);

        $this->assertSame('refunded', $refundedTransaction->status);
        $this->assertSame(10, $product->fresh()->stock);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $cashier->id,
            'action' => 'refund',
            'entity' => 'transactions',
            'entity_id' => $transaction->id,
            'description' => 'Melakukan refund transaksi '.$transaction->transaction_code,
        ]);
    }

    public function test_it_rolls_back_the_whole_sale_when_stock_updates_fail_mid_transaction(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $firstProduct = Product::factory()->create([
            'name' => 'First Cup',
            'price' => 12000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $secondProduct = Product::factory()->create([
            'name' => 'Second Cup',
            'price' => 18000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $stockService = new class extends StockService
        {
            public int $decreaseCalls = 0;

            public function decreaseStock(int $productId, int $quantity, string $referenceType, ?int $referenceId = null, ?string $note = null): void
            {
                $this->decreaseCalls++;

                if ($this->decreaseCalls === 2) {
                    throw new Exception('Injected stock failure.');
                }

                parent::decreaseStock($productId, $quantity, $referenceType, $referenceId, $note);
            }
        };

        $this->app->instance(StockService::class, $stockService);

        try {
            $this->app->make(TransactionService::class)->createTransaction(
                $cashier->id,
                [
                    ['product_id' => $firstProduct->id, 'quantity' => 1],
                    ['product_id' => $secondProduct->id, 'quantity' => 1],
                ],
                'cash',
                40000
            );
            $this->fail('Expected the transaction creation to roll back when stock updates fail mid-transaction.');
        } catch (Exception $exception) {
            $this->assertSame('Injected stock failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('transaction_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertSame(10, $firstProduct->fresh()->stock);
        $this->assertSame(10, $secondProduct->fresh()->stock);
    }
}
