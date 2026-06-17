<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public function __construct(protected AuditService $auditService)
    {
    }

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

        $this->auditService->log($userId, 'create', 'expenses', $expense->id, "Recorded expense: {$expense->description}");

        return $expense;
    }

    /**
     * Get expenses with optional filtering.
     */
    public function getExpenses(array $filters = [])
    {
        $query = Expense::query();

        if (isset($filters['start_date'])) {
            $query->where('expense_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('expense_date', '<=', $filters['end_date']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest('expense_date')->get();
    }
}
