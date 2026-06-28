<?php

namespace App\Livewire\Audit;

use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class AuditList extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $sortField = 'created_at';

    #[Url(history: true)]
    public string $sortDirection = 'desc';

    #[Url(history: true)]
    public array $filters = [
        'actor' => '',
        'action' => '',
        'entity' => '',
        'date' => '',
    ];

    public bool $showFilters = false;

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['created_at', 'action', 'entity'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = $field === 'created_at' ? 'desc' : 'asc';
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

    public function render()
    {
        $sortField = in_array($this->sortField, ['created_at', 'action', 'entity'], true)
            ? $this->sortField
            : 'created_at';

        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $logs = ActivityLog::query()
            ->with('user:id,name,username,email')
            ->when($this->filters['actor'], function ($query) {
                $search = '%'.$this->filters['actor'].'%';

                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', $search)
                        ->orWhere('username', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($this->filters['action'], fn ($query) => $query->where('action', $this->filters['action']))
            ->when($this->filters['entity'], fn ($query) => $query->where('entity', $this->filters['entity']))
            ->when($this->filters['date'], fn ($query) => $query->whereDate('created_at', $this->filters['date']))
            ->orderBy($sortField, $sortDirection)
            ->paginate(10);

        return view('livewire.audit.audit-list', [
            'logs' => $logs,
        ]);
    }
}
