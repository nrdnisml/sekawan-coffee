<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected AuditService $auditService,
        protected StockService $stockService
    ) {
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product
    {
        $product = Product::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'stock' => $data['stock'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->auditService->log(Auth::id(), 'create', 'products', $product->id, "Created product: {$product->name}");

        return $product;
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(int $id, array $data): Product
    {
        return DB::transaction(function () use ($id, $data) {
            $product = Product::findOrFail($id);
            $oldPrice = $product->price;

            // Handle image deletion if a new one is provided or if it's being removed
            if (isset($data['image_url']) && $product->getRawOriginal('image_url') && $data['image_url'] !== $product->getRawOriginal('image_url')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->getRawOriginal('image_url'));
            }

            $product->update($data);

            if (isset($data['price']) && (float)$data['price'] !== (float)$oldPrice) {
                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $oldPrice,
                    'new_price' => $data['price'],
                    'changed_by' => Auth::id(),
                ]);
                $this->auditService->log(Auth::id(), 'price_update', 'products', $product->id, "Price updated from {$oldPrice} to {$data['price']}");
            }

            $this->auditService->log(Auth::id(), 'update', 'products', $product->id, "Updated product: {$product->name}");

            return $product;
        });
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);

        if (TransactionItem::where('product_id', $id)->exists()) {
            throw new Exception("Cannot delete product used in transactions.");
        }

        $productName = $product->name;
        $imageUrl = $product->getRawOriginal('image_url');
        $product->delete();

        if ($imageUrl) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($imageUrl);
        }

        $this->auditService->log(Auth::id(), 'delete', 'products', $id, "Deleted product: {$productName}");
    }

    /**
     * Update product stock.
     */
    public function updateStock(int $productId, int $quantity): void
    {
        $this->stockService->adjustStock($productId, $quantity, "Manual stock update via ProductService");
    }
}
