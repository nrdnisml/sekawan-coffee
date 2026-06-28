<?php

namespace Tests\Feature;

use App\Livewire\Products\ProductForm;
use App\Livewire\Products\ProductList;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_products_list(): void
    {
        Product::factory()->count(3)->create();

        $this->get(route('products.index'))
            ->assertStatus(200)
            ->assertSee('Katalog Produk');
    }

    public function test_can_toggle_product_status(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        Livewire::test(ProductList::class)
            ->call('toggleStatus', $product->id)
            ->assertHasNoErrors();

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_can_delete_product_without_history(): void
    {
        $product = Product::factory()->create();

        Livewire::test(ProductList::class)
            ->set('productToDelete', $product->id)
            ->call('deleteProduct')
            ->assertHasNoErrors();

        $this->assertEquals(0, Product::count());
    }

    public function test_cannot_delete_product_with_history(): void
    {
        $product = Product::factory()->create();

        $transaction = Transaction::create([
            'transaction_code' => 'TRX-001',
            'user_id' => $this->user->id,
            'total_amount' => $product->price,
            'paid_amount' => $product->price,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => $product->price,
            'subtotal' => $product->price,
        ]);

        Livewire::test(ProductList::class)
            ->set('productToDelete', $product->id)
            ->call('deleteProduct')
            ->assertDispatched('toast-show');

        $this->assertEquals(1, Product::count());
    }

    public function test_can_create_product_via_form(): void
    {
        Livewire::test(ProductForm::class)
            ->set('name', 'New Coffee')
            ->set('price', 5.50)
            ->set('stock', 12)
            ->set('description', 'Delicious coffee')
            ->call('save')
            ->assertDispatched('product-saved');

        $this->assertDatabaseHas('products', [
            'name' => 'New Coffee',
            'stock' => 12,
        ]);
    }

    public function test_can_filter_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Americano']);
        Product::factory()->create(['name' => 'Latte']);

        Livewire::test(ProductList::class)
            ->set('filters.name', 'Amer')
            ->assertSee('Americano')
            ->assertDontSee('Latte');
    }

    public function test_can_create_product_with_image(): void
    {
        $disk = Storage::fake('public');

        $file = UploadedFile::fake()->createWithContent(
            'coffee.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnSUs8AAAAASUVORK5CYII=')
        );

        Livewire::test(ProductForm::class)
            ->set('name', 'Coffee with Image')
            ->set('price', 6.00)
            ->set('image', $file)
            ->call('save')
            ->assertDispatched('product-saved');

        $product = Product::where('name', 'Coffee with Image')->first();
        $this->assertNotNull($product->getRawOriginal('image_url'));
        $this->assertTrue($disk->exists($product->getRawOriginal('image_url')));
    }

    public function test_product_image_url_can_be_loaded_from_local_storage_through_app_route(): void
    {
        $disk = Storage::fake('public');
        $path = 'products/catalog-thumbnail.png';
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnSUs8AAAAASUVORK5CYII=');

        $disk->put($path, $content);

        $product = Product::factory()->create([
            'image_url' => $path,
        ]);

        $response = $this->get(parse_url($product->image_url, PHP_URL_PATH));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');
        $this->assertSame($content, $response->getContent());
    }

    public function test_can_replace_product_image_on_edit_and_old_file_is_deleted(): void
    {
        $disk = Storage::fake('public');
        $oldPath = 'products/old-image.png';
        $disk->put($oldPath, 'old-image-content');

        $product = Product::factory()->create([
            'name' => 'Editable Coffee',
            'image_url' => $oldPath,
            'stock' => 3,
        ]);

        $newFile = UploadedFile::fake()->createWithContent(
            'new-image.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnSUs8AAAAASUVORK5CYII=')
        );

        Livewire::test(ProductForm::class, ['productId' => $product->id])
            ->set('name', 'Editable Coffee Updated')
            ->set('price', 9.50)
            ->set('stock', 7)
            ->set('description', 'Updated description')
            ->set('image', $newFile)
            ->call('save')
            ->assertDispatched('product-saved');

        $product->refresh();

        $this->assertSame(7, $product->stock);
        $this->assertNotSame($oldPath, $product->getRawOriginal('image_url'));
        $this->assertFalse($disk->exists($oldPath));
        $this->assertTrue($disk->exists($product->getRawOriginal('image_url')));
    }

    public function test_can_sort_products_by_price(): void
    {
        Product::factory()->create(['name' => 'Cheap Coffee', 'price' => 10]);
        Product::factory()->create(['name' => 'Expensive Coffee', 'price' => 50]);

        Livewire::test(ProductList::class)
            ->set('sortField', 'price')
            ->set('sortDirection', 'asc')
            ->assertSeeInOrder(['Cheap Coffee', 'Expensive Coffee'])
            ->set('sortDirection', 'desc')
            ->assertSeeInOrder(['Expensive Coffee', 'Cheap Coffee']);
    }
}
