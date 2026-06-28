<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use App\Services\TransactionService;
use Flux\Concerns\InteractsWithComponents;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class TransactionList extends Component
{
    use InteractsWithComponents;
    use WithPagination;

    public ?int $selectedTransactionId = null;

    public ?int $transactionToProcessId = null;

    public ?string $pendingAction = null;

    #[Url(history: true)]
    public string $sortField = 'transaction_date';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    #[Url(history: true)]
    public array $filters = [
        'search' => '',
        'status' => '',
        'payment_method' => '',
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

    public function openPos(): void
    {
        $this->redirect(route('transactions.pos'), navigate: true);
    }

    public function showDetails(int $transactionId): void
    {
        $this->selectedTransactionId = $transactionId;
        $this->js('$flux.modal("transaction-detail-modal").show()');
    }

    public function confirmStatusChange(string $action, int $transactionId): void
    {
        if (! in_array($action, ['cancel', 'refund'], true)) {
            return;
        }

        $this->resetErrorBag('transactionAction');
        $this->pendingAction = $action;
        $this->transactionToProcessId = $transactionId;
        $this->js('$flux.modal("transaction-action-modal").show()');
    }

    public function performStatusChange(TransactionService $transactionService): void
    {
        if (! $this->transactionToProcessId || ! in_array($this->pendingAction, ['cancel', 'refund'], true)) {
            return;
        }

        try {
            $transaction = $this->pendingAction === 'cancel'
                ? $transactionService->cancelTransaction($this->transactionToProcessId)
                : $transactionService->refundTransaction($this->transactionToProcessId);
        } catch (\Exception $exception) {
            $this->addError('transactionAction', $exception->getMessage());

            return;
        }

        $actionText = $this->pendingAction === 'cancel' ? 'dibatalkan' : 'di-refund';

        $this->selectedTransactionId = $transaction->id;

        $this->toast(
            heading: 'Transaksi Diperbarui',
            text: $transaction->transaction_code.' berhasil '.$actionText.'.',
            variant: 'success',
        );

        $this->js('$flux.modal("transaction-action-modal").close()');
        $this->reset('transactionToProcessId', 'pendingAction');
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with('user')
            ->withCount('items')
            ->when($this->filters['search'], function ($query) {
                $search = '%'.$this->filters['search'].'%';

                $query->where(function ($transactionQuery) use ($search) {
                    $transactionQuery
                        ->where('transaction_code', 'like', $search)
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', $search)
                                ->orWhere('username', 'like', $search);
                        });
                });
            })
            ->when($this->filters['status'], fn ($query) => $query->where('status', $this->filters['status']))
            ->when($this->filters['payment_method'], fn ($query) => $query->where('payment_method', $this->filters['payment_method']))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.transactions.transaction-list', [
            'transactions' => $transactions,
            'selectedTransaction' => $this->selectedTransactionId
                ? Transaction::query()->with(['items', 'user'])->find($this->selectedTransactionId)
                : null,
            'transactionToProcess' => $this->transactionToProcessId
                ? Transaction::query()->find($this->transactionToProcessId)
                : null,
        ]);
    }
}
