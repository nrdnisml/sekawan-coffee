<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\User;
use Flux\Concerns\InteractsWithComponents;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ExpenseList extends Component
{
    use InteractsWithComponents;
    use WithPagination;

    #[Url(history: true)]
    public string $sortField = 'expense_date';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    #[Url(history: true)]
    public array $filters = [
        'search' => '',
        'start_date' => '',
        'end_date' => '',
        'user_id' => '',
    ];

    public bool $showFilters = false;

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function updating(string $property): void
    {
        if (str_starts_with($property, 'filters')) {
            $this->resetPage();
        }
    }

    public function clearAllFilters(): void
    {
        $this->reset('filters');
        $this->resetPage();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function openAddModal(): void
    {
        $this->js('$flux.modal("expense-form-modal").show()');
    }

    #[On('expense-saved')]
    public function handleExpenseSaved(string $message): void
    {
        $this->js('$flux.modal("expense-form-modal").close()');

        $this->toast(
            heading: 'Berhasil',
            text: $message,
            variant: 'success',
        );
    }

    public function render()
    {
        $expenses = Expense::query()
            ->with('user')
            ->when($this->filters['search'], function ($query) {
                $search = '%'.$this->filters['search'].'%';

                $query->where('description', 'like', $search);
            })
            ->when($this->filters['start_date'], fn ($query) => $query->where('expense_date', '>=', $this->filters['start_date'].' 00:00:00'))
            ->when($this->filters['end_date'], fn ($query) => $query->where('expense_date', '<=', $this->filters['end_date'].' 23:59:59'))
            ->when($this->filters['user_id'], fn ($query) => $query->where('user_id', $this->filters['user_id']))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.expenses.expense-list', [
            'expenses' => $expenses,
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }
}
