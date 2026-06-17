<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;

class InventoryList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedProductId = null;
    public $adjustingProductId = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAdjustmentModal($productId)
    {
        $this->adjustingProductId = $productId;
        $this->dispatch('modal-opened', 'stock-adjustment-modal');
    }

    public function openHistoryModal($productId)
    {
        $this->selectedProductId = $productId;
        $this->dispatch('modal-opened', 'stock-movement-history-modal');
    }

    #[On('stock-updated')]
    public function onStockUpdated()
    {
        $this->adjustingProductId = null;
        $this->dispatch('close-modal', 'stock-adjustment-modal');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.inventory.inventory-list', [
            'products' => $products,
        ]);
    }
}
