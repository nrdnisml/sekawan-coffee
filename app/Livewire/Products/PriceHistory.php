<?php

namespace App\Livewire\Products;

use App\Models\ProductPriceHistory;
use Livewire\Component;

class PriceHistory extends Component
{
    public $productId;

    public function mount($productId)
    {
        $this->productId = $productId;
    }

    public function render()
    {
        return view('livewire.products.price-history', [
            'history' => ProductPriceHistory::where('product_id', $this->productId)
                ->with('user')
                ->latest('changed_at')
                ->get()
        ]);
    }
}
