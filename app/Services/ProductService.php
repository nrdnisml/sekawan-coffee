<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\TransactionItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        protected AuditService $auditService,
        protected StockService $stockService
    ) {}

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

        $this->auditService->log(Auth::id(), 'create', 'products', $product->id, "Menambahkan produk: {$product->name}");

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
                Storage::disk('public')->delete($product->getRawOriginal('image_url'));
            }

            $product->update($data);

            if (isset($data['price']) && (float) $data['price'] !== (float) $oldPrice) {
                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'old_price' => $oldPrice,
                    'new_price' => $data['price'],
                    'changed_by' => Auth::id(),
                ]);
                $this->auditService->log(Auth::id(), 'price_update', 'products', $product->id, "Mengubah harga dari {$oldPrice} menjadi {$data['price']}");
            }

            $this->auditService->log(Auth::id(), 'update', 'products', $product->id, "Memperbarui produk: {$product->name}");

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
            throw new Exception('Produk tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }

        $productName = $product->name;
        $imageUrl = $product->getRawOriginal('image_url');
        $product->delete();

        if ($imageUrl) {
            Storage::disk('public')->delete($imageUrl);
        }

        $this->auditService->log(Auth::id(), 'delete', 'products', $id, "Menghapus produk: {$productName}");
    }

    /**
     * Update product stock.
     */
    public function updateStock(int $productId, int $quantity): void
    {
        $this->stockService->adjustStock($productId, $quantity, 'Pembaruan stok manual via ProductService');
    }
}
