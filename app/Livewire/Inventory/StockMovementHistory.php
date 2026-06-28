<?php

namespace App\Livewire\Inventory;

use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Support\Collection;
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

        $transactionCodes = Transaction::query()
            ->whereIn(
                'id',
                $movements
                    ->where('reference_type', 'transaction')
                    ->pluck('reference_id')
                    ->filter()
                    ->unique()
            )
            ->pluck('transaction_code', 'id');

        $movements->transform(function (StockMovement $movement) use ($transactionCodes) {
            $movement->quantity_label = $this->formatQuantity($movement);
            $movement->source_label = $this->formatSource($movement, $transactionCodes);

            return $movement;
        });

        return view('livewire.inventory.stock-movement-history', [
            'movements' => $movements,
        ]);
    }

    protected function formatQuantity(StockMovement $movement): string
    {
        return match ($movement->type) {
            'out' => '-'.abs($movement->quantity),
            'in' => '+'.abs($movement->quantity),
            'adjustment' => (string) abs($movement->quantity),
            default => (string) $movement->quantity,
        };
    }

    protected function formatSource(StockMovement $movement, Collection $transactionCodes): string
    {
        if ($movement->reference_type === 'transaction') {
            $transactionCode = $transactionCodes->get($movement->reference_id);

            return $transactionCode
                ? 'Transaksi '.$transactionCode
                : 'Transaksi #'.$movement->reference_id;
        }

        if ($movement->reference_type === 'manual') {
            return match ($movement->type) {
                'in' => 'Stok masuk manual',
                'out' => 'Stok keluar manual',
                'adjustment' => 'Penyesuaian manual',
                default => 'Pembaruan manual',
            };
        }

        return ucfirst((string) $movement->reference_type);
    }
}
