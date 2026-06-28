<?php

namespace Tests\Feature;

use App\Livewire\Transactions\PointOfSale;
use App\Livewire\Transactions\TransactionList;
use App\Models\Product;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_transaction_history_and_pos_pages(): void
    {
        $this->get(route('transactions.index'))
            ->assertRedirect(route('login'));

        $this->get(route('transactions.pos'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_transaction_history_page(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('Riwayat Transaksi');
    }

    public function test_transaction_history_renders_sales_and_reveals_transaction_details(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $firstProduct = Product::factory()->create([
            'name' => 'House Blend',
            'price' => 15000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $secondProduct = Product::factory()->create([
            'name' => 'Iced Latte',
            'price' => 20000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [
                ['product_id' => $firstProduct->id, 'quantity' => 1],
                ['product_id' => $secondProduct->id, 'quantity' => 1],
            ],
            'cash',
            50000
        );

        $this->actingAs($cashier)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee($transaction->transaction_code);

        Livewire::test(TransactionList::class)
            ->call('showDetails', $transaction->id)
            ->assertSee('Detail Transaksi')
            ->assertSee($transaction->transaction_code)
            ->assertSee('House Blend')
            ->assertSee('Iced Latte');
    }

    public function test_transaction_history_requires_confirmation_before_cancelling_a_sale(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Cappuccino',
            'price' => 18000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [['product_id' => $product->id, 'quantity' => 2]],
            'cash',
            40000
        );

        $this->actingAs($cashier);

        Livewire::test(TransactionList::class)
            ->call('confirmStatusChange', 'cancel', $transaction->id)
            ->assertSet('pendingAction', 'cancel')
            ->assertSet('transactionToProcessId', $transaction->id)
            ->assertSee('Batalkan Transaksi')
            ->call('performStatusChange')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'cancelled',
        ]);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_transaction_history_requires_confirmation_before_refunding_a_sale(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Mochaccino',
            'price' => 22000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $transaction = app(TransactionService::class)->createTransaction(
            $cashier->id,
            [['product_id' => $product->id, 'quantity' => 1]],
            'cash',
            25000
        );

        $this->actingAs($cashier);

        Livewire::test(TransactionList::class)
            ->call('confirmStatusChange', 'refund', $transaction->id)
            ->assertSet('pendingAction', 'refund')
            ->assertSet('transactionToProcessId', $transaction->id)
            ->assertSee('Pengembalian Dana Transaksi')
            ->call('performStatusChange')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'refunded',
        ]);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_cashier_can_create_a_sale_from_the_pos_page(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'House Blend',
            'price' => 15000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('transactions.pos'))
            ->assertOk()
            ->assertSee('Kasir / POS');

        Livewire::test(PointOfSale::class)
            ->set('cart.0.product_id', $product->id)
            ->set('cart.0.quantity', 2)
            ->set('paymentMethod', 'cash')
            ->set('paidAmount', '40000')
            ->call('checkout')
            ->assertHasNoErrors()
            ->assertDispatched('sale-created');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $cashier->id,
            'payment_method' => 'cash',
            'total_amount' => '30000.00',
            'paid_amount' => '40000.00',
            'change_amount' => '10000.00',
            'status' => 'completed',
        ]);

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_pos_only_shows_products_with_available_stock(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $availableProduct = Product::factory()->create([
            'name' => 'Available Beans',
            'price' => 15000,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Out of Stock Beans',
            'price' => 17000,
            'stock' => 0,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'name' => 'Inactive Beans',
            'price' => 19000,
            'stock' => 8,
            'is_active' => false,
        ]);

        $this->actingAs($cashier)
            ->get(route('transactions.pos'))
            ->assertOk()
            ->assertSee($availableProduct->name)
            ->assertDontSee('Out of Stock Beans')
            ->assertDontSee('Inactive Beans');
    }

    public function test_pos_validates_quantity_against_available_stock_on_the_field(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Limited Beans',
            'price' => 20000,
            'stock' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($cashier);

        Livewire::test(PointOfSale::class)
            ->set('cart.0.product_id', $product->id)
            ->set('cart.0.quantity', 3)
            ->set('paymentMethod', 'cash')
            ->set('paidAmount', '70000')
            ->call('checkout')
            ->assertHasErrors(['cart.0.quantity']);

        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_pos_shows_dummy_qr_information_for_qris_payment(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($cashier);

        Livewire::test(PointOfSale::class)
            ->set('paymentMethod', 'qris')
            ->assertSee('QRIS Dummy')
            ->assertSee('Hanya untuk tampilan demo');
    }

    public function test_pos_reports_a_paid_amount_rejection_without_creating_a_sale(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Cold Brew',
            'price' => 18000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($cashier);

        Livewire::test(PointOfSale::class)
            ->set('cart.0.product_id', $product->id)
            ->set('cart.0.quantity', 2)
            ->set('paymentMethod', 'cash')
            ->set('paidAmount', '25000')
            ->call('checkout')
            ->assertHasErrors(['paidAmount']);

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_pos_reports_an_inactive_product_rejection_without_creating_a_sale(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $product = Product::factory()->create([
            'name' => 'Archived Beans',
            'price' => 16000,
            'stock' => 10,
            'is_active' => false,
        ]);

        $this->actingAs($cashier);

        Livewire::test(PointOfSale::class)
            ->set('cart.0.product_id', $product->id)
            ->set('cart.0.quantity', 1)
            ->set('paymentMethod', 'cash')
            ->set('paidAmount', '16000')
            ->call('checkout')
            ->assertHasErrors(['cart']);

        $this->assertDatabaseCount('transactions', 0);
    }
}
