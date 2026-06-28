<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_expense_and_writes_an_audit_log(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $expense = app(ExpenseService::class)->createExpense($admin->id, [
            'description' => 'Milk restock',
            'amount' => 125000,
            'expense_date' => '2026-06-15 10:30:00',
        ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'user_id' => $admin->id,
            'description' => 'Milk restock',
            'amount' => '125000.00',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'create',
            'entity' => 'expenses',
            'entity_id' => $expense->id,
            'description' => 'Mencatat pengeluaran: Milk restock',
        ]);
    }

    public function test_it_filters_expenses_by_date_range_and_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        app(ExpenseService::class)->createExpense($admin->id, [
            'description' => 'Visible expense',
            'amount' => 90000,
            'expense_date' => '2026-06-10 09:00:00',
        ]);

        app(ExpenseService::class)->createExpense($admin->id, [
            'description' => 'Too early',
            'amount' => 50000,
            'expense_date' => '2026-06-01 09:00:00',
        ]);

        app(ExpenseService::class)->createExpense($cashier->id, [
            'description' => 'Wrong user',
            'amount' => 60000,
            'expense_date' => '2026-06-12 09:00:00',
        ]);

        $expenses = app(ExpenseService::class)->getExpenses([
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-15',
            'user_id' => $admin->id,
        ]);

        $this->assertCount(1, $expenses);
        $this->assertSame('Visible expense', $expenses->first()->description);
    }
}
