<?php

namespace Tests\Feature;

use App\Livewire\Expenses\ExpenseForm;
use App\Livewire\Expenses\ExpenseList;
use App\Models\Expense;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_away_from_the_expenses_page(): void
    {
        $this->get(route('expenses.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_expenses_list_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('Manajemen Pengeluaran');
    }

    public function test_cashier_is_forbidden_from_the_expenses_page(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->actingAs($cashier)
            ->get(route('expenses.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_an_expense_via_the_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(ExpenseForm::class)
            ->set('description', 'Paper cups')
            ->set('amount', 45000)
            ->set('expense_date', '2026-06-18')
            ->call('save')
            ->assertDispatched('expense-saved');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $admin->id,
            'description' => 'Paper cups',
            'amount' => '45000.00',
        ]);
    }

    public function test_expense_form_validates_required_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(ExpenseForm::class)
            ->set('description', '')
            ->set('amount', '')
            ->set('expense_date', '')
            ->call('save')
            ->assertHasErrors([
                'description' => ['required'],
                'amount' => ['required'],
                'expense_date' => ['required'],
            ]);
    }

    public function test_admin_can_filter_expenses_by_search_date_and_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
        ]);
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'name' => 'Cashier User',
        ]);

        app(ExpenseService::class)->createExpense($admin->id, [
            'description' => 'Visible milk expense',
            'amount' => 100000,
            'expense_date' => '2026-06-20 08:00:00',
        ]);

        app(ExpenseService::class)->createExpense($admin->id, [
            'description' => 'Outside date range',
            'amount' => 80000,
            'expense_date' => '2026-06-01 08:00:00',
        ]);

        app(ExpenseService::class)->createExpense($cashier->id, [
            'description' => 'Other user expense',
            'amount' => 90000,
            'expense_date' => '2026-06-20 08:00:00',
        ]);

        $this->actingAs($admin);

        Livewire::test(ExpenseList::class)
            ->set('filters.search', 'milk')
            ->set('filters.start_date', '2026-06-15')
            ->set('filters.end_date', '2026-06-25')
            ->set('filters.user_id', (string) $admin->id)
            ->assertSee('Visible milk expense')
            ->assertDontSee('Outside date range')
            ->assertDontSee('Other user expense');
    }

    public function test_expense_creation_writes_an_audit_row_after_save(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        Livewire::test(ExpenseForm::class)
            ->set('description', 'Sugar restock')
            ->set('amount', 70000)
            ->set('expense_date', '2026-06-19')
            ->call('save')
            ->assertDispatched('expense-saved');

        $expenseId = Expense::query()->where('description', 'Sugar restock')->value('id');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'create',
            'entity' => 'expenses',
            'entity_id' => $expenseId,
            'description' => 'Mencatat pengeluaran: Sugar restock',
        ]);
    }
}
