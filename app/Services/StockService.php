<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Increase product stock.
     */
    public function increaseStock(int $productId, int $quantity, string $referenceType, ?int $referenceId = null, ?string $note = null): void
    {
        $this->moveStock($productId, $quantity, 'in', $referenceType, $referenceId, $note);
    }

    /**
     * Decrease product stock.
     */
    public function decreaseStock(int $productId, int $quantity, string $referenceType, ?int $referenceId = null, ?string $note = null): void
    {
        $this->moveStock($productId, $quantity, 'out', $referenceType, $referenceId, $note);
    }

    /**
     * Adjust product stock manually.
     */
    public function adjustStock(int $productId, int $quantity, string $note): void
    {
        // For adjustment, quantity can be positive or negative
        $type = 'adjustment';
        $this->moveStock($productId, $quantity, $type, 'manual', null, $note);
    }

    /**
     * Shared logic for stock movements.
     */
    protected function moveStock(int $productId, int $quantity, string $type, string $referenceType, ?int $referenceId, ?string $note): void
    {
        DB::transaction(function () use ($productId, $quantity, $type, $referenceType, $referenceId, $note) {
            $product = Product::query()
                ->lockForUpdate()
                ->find($productId);

            if (! $product) {
                throw new Exception('Produk yang dipilih sudah tidak tersedia.');
            }

            if ($type === 'adjustment') {
                $product->stock = abs($quantity);
                $product->save();
            } elseif ($type === 'out') {
                $absQuantity = abs($quantity);
                if ($product->stock < $absQuantity) {
                    throw new Exception("Stok produk {$product->name} tidak mencukupi.");
                }
                $product->decrement('stock', $absQuantity);
            } else {
                $product->increment('stock', abs($quantity));
            }

            StockMovement::create([
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note,
            ]);
        });
    }
}
