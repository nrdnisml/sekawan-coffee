<?php

namespace App\Livewire\Transactions;

use App\Models\Product;
use App\Services\TransactionService;
use Flux\Concerns\InteractsWithComponents;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PointOfSale extends Component
{
    use InteractsWithComponents;

    public array $cart = [
        ['product_id' => '', 'quantity' => 1],
    ];

    public string $paymentMethod = 'cash';

    public string $paidAmount = '';

    public function addItem(): void
    {
        $this->cart[] = ['product_id' => '', 'quantity' => 1];
    }

    public function removeItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);

        if ($this->cart === []) {
            $this->cart[] = ['product_id' => '', 'quantity' => 1];
        }
    }

    public function viewHistory(): void
    {
        $this->redirect(route('transactions.index'), navigate: true);
    }

    public function checkout(TransactionService $transactionService): void
    {
        $validated = $this->validate($this->rules());

        if ($this->hasUnavailableStock($validated['cart'])) {
            return;
        }

        try {
            $transaction = $transactionService->createTransaction(
                (int) Auth::id(),
                $this->normalizedCart($validated['cart']),
                $validated['paymentMethod'],
                (float) $validated['paidAmount'],
            );
        } catch (\Exception $exception) {
            $this->mapServiceError($exception);

            return;
        }

        $transactionCode = $transaction->transaction_code;

        $this->resetCheckoutForm();

        $this->toast(
            heading: 'Penjualan Selesai',
            text: $transactionCode.' berhasil dicatat.',
            variant: 'success',
        );

        $this->dispatch('sale-created', transactionId: $transaction->id);
    }

    protected function rules(): array
    {
        return [
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
            'paymentMethod' => ['required', 'in:cash,qris,transfer'],
            'paidAmount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    protected function normalizedCart(array $cart): array
    {
        return collect($cart)
            ->map(fn (array $item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ])
            ->values()
            ->all();
    }

    protected function mapServiceError(\Exception $exception): void
    {
        $message = $exception->getMessage();

        if ($message === 'Jumlah bayar lebih kecil dari total belanja.') {
            $this->addError('paidAmount', $message);

            return;
        }

        $this->addError('cart', $message);
    }

    protected function hasUnavailableStock(array $cart): bool
    {
        $productIds = collect($cart)
            ->pluck('product_id')
            ->filter()
            ->map(fn ($productId) => (int) $productId)
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($cart as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);

            if ($productId === 0) {
                continue;
            }

            $product = $products->get($productId);

            if (! $product) {
                $this->addError('cart.'.$index.'.product_id', 'Produk tidak tersedia.');

                return true;
            }

            $quantity = (int) ($item['quantity'] ?? 0);

            if ($quantity > $product->stock) {
                $this->addError('cart.'.$index.'.quantity', 'Jumlah melebihi stok tersedia ('.$product->stock.').');

                return true;
            }
        }

        return false;
    }

    protected function resetCheckoutForm(): void
    {
        $this->resetErrorBag();
        $this->cart = [
            ['product_id' => '', 'quantity' => 1],
        ];
        $this->paymentMethod = 'cash';
        $this->paidAmount = '';
    }

    protected function activeProducts(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();
    }

    protected function orderSummary(): array
    {
        $productIds = collect($this->cart)
            ->pluck('product_id')
            ->filter()
            ->map(fn ($productId) => (int) $productId)
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $lines = [];
        $total = 0.0;

        foreach ($this->cart as $index => $line) {
            if (blank($line['product_id'])) {
                continue;
            }

            $product = $products->get((int) $line['product_id']);

            if (! $product) {
                continue;
            }

            $quantity = max(1, (int) ($line['quantity'] ?? 1));
            $subtotal = (float) $product->price * $quantity;
            $total += $subtotal;

            $lines[] = [
                'index' => $index,
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        $paidAmount = is_numeric($this->paidAmount) ? (float) $this->paidAmount : 0.0;

        return [
            'lines' => $lines,
            'total' => $total,
            'change' => max(0, $paidAmount - $total),
            'due' => max(0, $total - $paidAmount),
        ];
    }

    public function render()
    {
        return view('livewire.transactions.point-of-sale', [
            'products' => $this->activeProducts(),
            'summary' => $this->orderSummary(),
        ]);
    }
}
