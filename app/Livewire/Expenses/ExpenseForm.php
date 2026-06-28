<?php

namespace App\Livewire\Expenses;

use App\Services\ExpenseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExpenseForm extends Component
{
    public string $description = '';

    public string $amount = '';

    public string $expense_date = '';

    public function mount(): void
    {
        $this->expense_date = now()->toDateString();
    }

    public function save(ExpenseService $expenseService): void
    {
        $data = $this->validate($this->rules());

        $expenseService->createExpense((int) Auth::id(), $data);

        $this->reset('description', 'amount');
        $this->expense_date = now()->toDateString();

        $this->dispatch('expense-saved', message: 'Pengeluaran berhasil ditambahkan.');
    }

    protected function rules(): array
    {
        return [
            'description' => ['required', 'string', 'min:3', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ];
    }

    public function render()
    {
        return view('livewire.expenses.expense-form');
    }
}
