<?php

namespace App\Livewire\Inventory;

use App\Services\StockService;
use Exception;
use Livewire\Component;

class StockAdjustmentForm extends Component
{
    public $productId;

    public $type = 'in';

    public $quantity = 1;

    public $note = '';

    protected $rules = [
        'type' => 'required|in:in,out,adjustment',
        'quantity' => 'required|integer|min:1',
        'note' => 'nullable|string|max:255',
    ];

    public function save(StockService $stockService)
    {
        $this->validate();

        try {
            if ($this->type === 'in') {
                $stockService->increaseStock($this->productId, $this->quantity, 'manual', null, $this->note);
            } elseif ($this->type === 'out') {
                $stockService->decreaseStock($this->productId, $this->quantity, 'manual', null, $this->note);
            } elseif ($this->type === 'adjustment') {
                $stockService->adjustStock($this->productId, $this->quantity, $this->note);
            }

            $this->dispatch('stock-updated');
            // Assuming Flux has a global toast or similar established in the project
            // For now, the parent handles modal closure.
        } catch (Exception $e) {
            $field = $e->getMessage() === 'Produk yang dipilih sudah tidak tersedia.'
                ? 'productId'
                : 'quantity';

            $this->addError($field, $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.inventory.stock-adjustment-form');
    }
}
