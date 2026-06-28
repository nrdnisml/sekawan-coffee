<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Services\ProductService;
use Flux\Concerns\InteractsWithComponents;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ProductList extends Component
{
    use InteractsWithComponents, WithPagination;

    public $selectedProductId;

    public $editingProductId = null;

    public $productToDelete = null;

    #[Url(history: true)]
    public $sortField = 'created_at';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    #[Url(history: true)]
    public $filters = [
        'name' => '',
        'description' => '',
        'status' => '',
        'min_price' => '',
        'max_price' => '',
    ];

    public $showFilters = false;

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updating($property)
    {
        if (str_starts_with($property, 'filters')) {
            $this->resetPage();
        }
    }

    public function clearAllFilters()
    {
        $this->reset('filters');
        $this->resetPage();
    }

    public function showHistory($productId)
    {
        $this->selectedProductId = $productId;
        $this->js('$flux.modal("price-history-modal").show()');
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function openAddModal()
    {
        $this->editingProductId = null;
        $this->js('$flux.modal("product-form-modal").show()');
    }

    public function editProduct($productId)
    {
        $this->editingProductId = $productId;
        $this->js('$flux.modal("product-form-modal").show()');
    }

    public function confirmDelete($productId)
    {
        $this->productToDelete = $productId;
        $this->js('$flux.modal("delete-product-modal").show()');
    }

    public function deleteProduct(ProductService $productService)
    {
        if (! $this->productToDelete) {
            return;
        }

        try {
            $product = Product::findOrFail($this->productToDelete);
            $productName = $product->name;
            $productService->deleteProduct($this->productToDelete);

            $this->toast(
                heading: 'Produk Dihapus',
                text: "{$productName} telah dihapus dari katalog.",
                variant: 'success'
            );

            $this->js('$flux.modal("delete-product-modal").close()');
            $this->productToDelete = null;
        } catch (\Exception $e) {
            $this->toast(
                heading: 'Gagal',
                text: 'Produk tidak dapat dihapus karena sudah memiliki riwayat penjualan. Nonaktifkan produk sebagai gantinya.',
                variant: 'danger'
            );
            $this->js('$flux.modal("delete-product-modal").close()');
        }
    }

    public function toggleStatus($productId, ProductService $productService)
    {
        $product = Product::findOrFail($productId);
        $productService->updateProduct($productId, [
            'is_active' => ! $product->is_active,
        ]);

        $this->toast(
            heading: 'Status Diperbarui',
            text: "Status {$product->name} berhasil diubah.",
            variant: 'success'
        );
    }

    #[On('product-saved')]
    public function handleProductSaved($message)
    {
        $this->js('$flux.modal("product-form-modal").close()');

        $this->toast(
            heading: 'Berhasil',
            text: $message,
            variant: 'success'
        );
    }

    public function render()
    {
        $query = Product::query()
            ->withCount('transactionItems')
            ->when($this->filters['name'], fn ($q) => $q->where('name', 'like', '%'.$this->filters['name'].'%'))
            ->when($this->filters['description'], fn ($q) => $q->where('description', 'like', '%'.$this->filters['description'].'%'))
            ->when($this->filters['status'] !== '', fn ($q) => $q->where('is_active', $this->filters['status']))
            ->when($this->filters['min_price'], fn ($q) => $q->where('price', '>=', $this->filters['min_price']))
            ->when($this->filters['max_price'], fn ($q) => $q->where('price', '<=', $this->filters['max_price']))
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.products.product-list', [
            'products' => $query->paginate(10),
        ]);
    }
}
