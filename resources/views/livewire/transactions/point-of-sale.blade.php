<div class="relative">
    <div wire:loading class="fixed top-0 left-0 right-0 h-0.5 z-[999] overflow-hidden pointer-events-none">
        <div class="h-full bg-indigo-600 w-full animate-progress-bar shadow-[0_0_10px_rgba(79,70,229,0.5)]"></div>
    </div>

    <style>
        @keyframes progress-bar {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-progress-bar {
            animation: progress-bar 2s infinite linear;
        }
    </style>

    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">
                Kasir / POS
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Susun pesanan, terima pembayaran, dan pastikan stok serta snapshot transaksi tetap sinkron.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="clock" wire:click="viewHistory">
                Lihat Riwayat
            </flux:button>

            <flux:button type="button" variant="filled" color="accent" icon="plus" wire:click="addItem">
                Tambah Baris Item
            </flux:button>
        </div>
    </div>

    <form wire:submit="checkout" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(22rem,1fr)]">
        <div class="space-y-6">
            <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6">
                <div class="flex flex-col gap-2 mb-6 md:flex-row md:items-start md:justify-between">
                    <div>
                        <flux:heading size="lg">Pesanan Saat Ini</flux:heading>
                        <flux:subheading>Pilih produk aktif dan atur jumlah sebelum menyelesaikan transaksi.</flux:subheading>
                    </div>

                    <flux:badge color="blue" size="sm">{{ count($cart) }} baris</flux:badge>
                </div>

                @error('cart')
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                        {{ $message }}
                    </div>
                @enderror

                <div class="space-y-4">
                    @foreach ($cart as $index => $line)
                        <div wire:key="sale-item-{{ $index }}" class="rounded-2xl border border-zinc-200 bg-zinc-50/80 p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/80">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Baris {{ $index + 1 }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Pilih produk dan tentukan jumlah pembelian.</p>
                                </div>

                                <flux:button type="button" variant="subtle" color="danger" icon="trash" wire:click="removeItem({{ $index }})" :disabled="count($cart) === 1">
                                    Hapus
                                </flux:button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1.8fr)_12rem] lg:items-start">
                            <div>
                                <flux:select wire:model.live="cart.{{ $index }}.product_id" label="Produk">
                                    <flux:select.option value="">Pilih produk</flux:select.option>
                                    @foreach ($products as $availableProduct)
                                        <flux:select.option :value="$availableProduct->id">
                                            {{ $availableProduct->name }} · Rp {{ number_format((float) $availableProduct->price, 0, ',', '.') }} · Stok {{ $availableProduct->stock }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                                @error('cart.' . $index . '.product_id')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <flux:input wire:model.live="cart.{{ $index }}.quantity" type="number" min="1" label="Jumlah" />

                                @error('cart.' . $index . '.quantity')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6 xl:sticky xl:top-6">
                <div class="mb-6">
                        <flux:heading size="lg">Ringkasan Pesanan</flux:heading>
                        <flux:subheading>Pastikan total belanja sudah benar sebelum menyimpan transaksi.</flux:subheading>
                </div>

                @if ($summary['lines'])
                    <div class="space-y-3">
                        @foreach ($summary['lines'] as $line)
                            <div class="flex items-start justify-between gap-4 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900">
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $line['product']->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $line['quantity'] }} × Rp {{ number_format((float) $line['product']->price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <p class="font-medium text-zinc-900 dark:text-white">
                                    Rp {{ number_format((float) $line['subtotal'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 px-4 py-6 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
                        Tambahkan minimal satu produk untuk melihat ringkasan pesanan.
                    </div>
                @endif

                <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-800 space-y-4">
                    <div class="flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400">
                        <span>Total Belanja</span>
                        <span class="text-base font-semibold text-zinc-900 dark:text-white">
                            Rp {{ number_format((float) $summary['total'], 0, ',', '.') }}
                        </span>
                    </div>

                    <flux:select wire:model="paymentMethod" label="Metode Pembayaran">
                        <flux:select.option value="cash">Tunai</flux:select.option>
                        <flux:select.option value="qris">QRIS</flux:select.option>
                        <flux:select.option value="transfer">Transfer Bank</flux:select.option>
                    </flux:select>

                    @if ($paymentMethod === 'qris')
                        <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-4 text-center dark:border-zinc-700 dark:bg-zinc-900">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">QRIS Dummy</p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Hanya untuk tampilan demo, bukan QR pembayaran asli.</p>
                            <div class="mx-auto mt-4 flex h-44 w-44 items-center justify-center rounded-xl border border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="grid grid-cols-6 gap-1 rounded-lg bg-white p-3 dark:bg-zinc-950">
                                    @for ($row = 0; $row < 6; $row++)
                                        @for ($col = 0; $col < 6; $col++)
                                            <div class="h-4 w-4 rounded-[2px] {{ ($row + $col) % 2 === 0 ? 'bg-zinc-900 dark:bg-white' : 'bg-zinc-200 dark:bg-zinc-700' }}"></div>
                                        @endfor
                                    @endfor
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <flux:input wire:model.live="paidAmount" type="number" step="0.01" min="0.01" label="Jumlah Bayar" icon="banknotes" />

                        @error('paidAmount')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900 space-y-2">
                        <div class="flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400">
                            <span>Kembalian</span>
                            <span>Rp {{ number_format((float) $summary['change'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400">
                            <span>Sisa Tagihan</span>
                            <span>Rp {{ number_format((float) $summary['due'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <flux:button type="submit" variant="filled" color="accent" class="w-full justify-center">
                        Selesaikan Transaksi
                    </flux:button>
                </div>
            </div>
        </div>
    </form>
</div>
