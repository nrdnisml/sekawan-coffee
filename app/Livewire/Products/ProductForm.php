<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Services\ProductService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public $productId;
    public $name;
    public $description;
    public $price;
    public $image;
    public $existingImage;
    public $is_active = true;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'description' => 'nullable|max:1000',
        'price' => 'required|numeric|min:0',
        'image' => 'nullable|image|max:2048', // 2MB Max
        'is_active' => 'boolean',
    ];

    public function mount($productId = null)
    {
        if ($productId) {
            $product = Product::findOrFail($productId);
            $this->productId = $product->id;
            $this->name = $product->name;
            $this->description = $product->description;
            $this->price = $product->price;
            $this->existingImage = $product->image_url;
            $this->is_active = $product->is_active;
        }
    }

    public function save(ProductService $productService)
    {
        $data = $this->validate();

        if ($this->image) {
            $data['image_url'] = $this->image->store('products', 'public');
        }
        
        // Remove image from data if it's not set (to avoid overwriting with null if no new image uploaded)
        unset($data['image']);

        if ($this->productId) {
            $productService->updateProduct($this->productId, $data);
            $message = 'Product updated successfully.';
        } else {
            $productService->createProduct($data);
            $message = 'Product created successfully.';
        }

        $this->dispatch('product-saved', message: $message);
    }

    public function render()
    {
        return view('livewire.products.product-form');
    }
}
