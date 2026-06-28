<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

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
        $this->js('$flux.modal("stock-adjustment-modal").show()');
    }

    public function openHistoryModal($productId)
    {
        $this->selectedProductId = $productId;
        $this->js('$flux.modal("stock-movement-history-modal").show()');
    }

    #[On('stock-updated')]
    public function onStockUpdated()
    {
        $this->adjustingProductId = null;
        $this->js('$flux.modal("stock-adjustment-modal").close()');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $products = Product::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->paginate(10);

        return view('livewire.inventory.inventory-list', [
            'products' => $products,
        ]);
    }
}
