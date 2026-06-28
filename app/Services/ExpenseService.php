<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function __construct(protected AuditService $auditService) {}

    /**
     * Record a new expense.
     */
    public function createExpense(int $userId, array $data): Expense
    {
        $expense = Expense::create([
            'user_id' => $userId,
            'description' => $data['description'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'] ?? now(),
        ]);

        $this->auditService->log($userId, 'create', 'expenses', $expense->id, "Mencatat pengeluaran: {$expense->description}");

        return $expense;
    }

    /**
     * Get expenses with optional filtering.
     */
    public function getExpenses(array $filters = []): Collection
    {
        $query = Expense::query();

        if (filled($filters['start_date'] ?? null)) {
            $query->where('expense_date', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }

        if (filled($filters['end_date'] ?? null)) {
            $query->where('expense_date', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest('expense_date')->get();
    }
}
