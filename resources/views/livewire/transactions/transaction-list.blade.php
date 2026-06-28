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
                Riwayat Transaksi
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Tinjau penjualan yang selesai, periksa snapshot item, dan konfirmasi aksi batal atau pengembalian dana.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="funnel" wire:click="toggleFilters">
                {{ $showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}
            </flux:button>

            <flux:button variant="filled" color="accent" icon="shopping-cart" wire:click="openPos">
                Buka POS
            </flux:button>
        </div>
    </div>

    @if ($showFilters)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-5 mb-6 bg-zinc-50 border border-zinc-200 rounded-xl dark:bg-zinc-900 dark:border-zinc-800 items-center">
            <flux:input wire:model.live.debounce.300ms="filters.search" placeholder="Cari kode transaksi atau kasir..." icon="magnifying-glass" />

            <flux:select wire:model.live="filters.status" placeholder="Semua Status">
                <flux:select.option value="">Semua Status</flux:select.option>
                <flux:select.option value="completed">Selesai</flux:select.option>
                <flux:select.option value="cancelled">Dibatalkan</flux:select.option>
                <flux:select.option value="refunded">Pengembalian Dana</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filters.payment_method" placeholder="Semua Metode">
                <flux:select.option value="">Semua Metode</flux:select.option>
                <flux:select.option value="cash">Cash</flux:select.option>
                <flux:select.option value="qris">QRIS</flux:select.option>
                <flux:select.option value="transfer">Transfer</flux:select.option>
            </flux:select>

            <div>
                @if (filled($filters['search']) || filled($filters['status']) || filled($filters['payment_method']))
                    <flux:button variant="subtle" color="danger" icon="x-mark" wire:click="clearAllFilters" class="w-full justify-center">
                        Bersihkan Filter
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6">
        <flux:table variant="striped">
            <flux:table.columns>
                <flux:table.column sortable :direction="$sortField === 'transaction_code' ? $sortDirection : null" wire:click="sortBy('transaction_code')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Transaksi
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'transaction_date' ? $sortDirection : null" wire:click="sortBy('transaction_date')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Tanggal
                </flux:table.column>
                <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Kasir
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'total_amount' ? $sortDirection : null" wire:click="sortBy('total_amount')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Total
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'payment_method' ? $sortDirection : null" wire:click="sortBy('payment_method')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Pembayaran
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'status' ? $sortDirection : null" wire:click="sortBy('status')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Status
                </flux:table.column>
                <flux:table.column align="end" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Aksi
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($transactions as $transaction)
                    <flux:table.row :key="$transaction->id">
                        <flux:table.cell class="py-4">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $transaction->transaction_code }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $transaction->items_count }} baris item
                                </span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-500 dark:text-zinc-400">
                            {{ $transaction->transaction_date->format('d M Y H:i') }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-600 dark:text-zinc-300">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $transaction->user?->name }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $transaction->user?->username }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300 font-medium">
                            Rp {{ number_format((float) $transaction->total_amount, 0, ',', '.') }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="{{ $transaction->payment_method === 'cash' ? 'green' : ($transaction->payment_method === 'qris' ? 'blue' : 'zinc') }}" size="sm">
                                {{ strtoupper($transaction->payment_method) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="{{ $transaction->status === 'completed' ? 'green' : ($transaction->status === 'cancelled' ? 'zinc' : 'amber') }}" size="sm">
                                {{ ucfirst($transaction->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-right">
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <flux:button size="sm" variant="subtle" wire:click="showDetails({{ $transaction->id }})">
                                        Detail
                                </flux:button>

                                @if ($transaction->status === 'completed')
                                    <flux:button size="sm" variant="subtle" color="danger" wire:click="confirmStatusChange('cancel', {{ $transaction->id }})">
                                        Batal
                                    </flux:button>
                                    <flux:button size="sm" variant="filled" color="accent" wire:click="confirmStatusChange('refund', {{ $transaction->id }})">
                                        Pengembalian Dana
                                    </flux:button>
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center">
                                <flux:icon.magnifying-glass class="size-8 mb-2 text-zinc-300" />
                                <p class="text-sm font-medium">Tidak ada transaksi yang cocok dengan filter Anda.</p>
                                <flux:button variant="subtle" size="sm" class="mt-2" wire:click="openPos">
                                    Buka POS
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>

    <flux:modal name="transaction-detail-modal" class="min-w-[48rem]">
        @if ($selectedTransaction)
            <div class="space-y-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <flux:heading size="lg">Detail Transaksi</flux:heading>
                        <flux:subheading>
                            {{ $selectedTransaction->transaction_code }} · {{ $selectedTransaction->transaction_date->format('d M Y H:i') }}
                        </flux:subheading>
                    </div>

                    <flux:badge color="{{ $selectedTransaction->status === 'completed' ? 'green' : ($selectedTransaction->status === 'cancelled' ? 'zinc' : 'amber') }}" size="sm">
                        {{ ucfirst($selectedTransaction->status) }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Kasir</p>
                        <p class="mt-2 font-medium text-zinc-900 dark:text-white">{{ $selectedTransaction->user?->name }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $selectedTransaction->user?->username }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Total Belanja</p>
                        <p class="mt-2 font-medium text-zinc-900 dark:text-white">Rp {{ number_format((float) $selectedTransaction->total_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Jumlah Bayar</p>
                        <p class="mt-2 font-medium text-zinc-900 dark:text-white">Rp {{ number_format((float) $selectedTransaction->paid_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Kembalian</p>
                        <p class="mt-2 font-medium text-zinc-900 dark:text-white">Rp {{ number_format((float) $selectedTransaction->change_amount, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6">
                    <flux:table variant="striped">
                        <flux:table.columns>
                            <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Produk</flux:table.column>
                            <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Snapshot Harga</flux:table.column>
                            <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Jumlah</flux:table.column>
                            <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Subtotal</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($selectedTransaction->items as $item)
                                <flux:table.row :key="$item->id">
                                    <flux:table.cell class="py-4 font-medium text-zinc-900 dark:text-white">
                                        {{ $item->product_name }}
                                    </flux:table.cell>
                                    <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300">
                                        Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                                    </flux:table.cell>
                                    <flux:table.cell class="py-4 text-zinc-500 dark:text-zinc-400">
                                        {{ $item->quantity }}
                                    </flux:table.cell>
                                    <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300 font-medium">
                                        Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
        @endif
    </flux:modal>

    <flux:modal name="transaction-action-modal" class="min-w-[28rem]">
        @if ($transactionToProcess)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $pendingAction === 'cancel' ? 'Batalkan Transaksi' : 'Pengembalian Dana Transaksi' }}
                    </flux:heading>
                    <flux:subheading>
                        <p>
                            Apakah Anda yakin ingin {{ $pendingAction === 'cancel' ? 'membatalkan' : 'melakukan pengembalian dana untuk' }} <strong>{{ $transactionToProcess->transaction_code }}</strong>?
                            Stok akan dipulihkan otomatis setelah konfirmasi.
                        </p>
                    </flux:subheading>
                </div>

                @error('transactionAction')
                    <p class="text-sm text-red-500">{{ $message }}</p>
                @enderror

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Pertahankan Transaksi</flux:button>
                    </flux:modal.close>
                    <flux:button color="danger" wire:click="performStatusChange">
                        {{ $pendingAction === 'cancel' ? 'Konfirmasi Batal' : 'Konfirmasi Pengembalian Dana' }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
