<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use Livewire\Component;

class StockMovementHistory extends Component
{
    public $productId;

    public function render()
    {
        $movements = StockMovement::where('product_id', $this->productId)
            ->latest('id') // Using ID as fallback for identical timestamps
            ->latest('created_at')
            ->get();

        return view('livewire.inventory.stock-movement-history', [
            'movements' => $movements,
        ]);
    }
}
