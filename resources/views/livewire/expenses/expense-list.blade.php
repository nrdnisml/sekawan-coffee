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

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">
            Manajemen Pengeluaran
        </h1>

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="funnel" wire:click="toggleFilters">
                {{ $showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}
            </flux:button>

            <flux:button variant="filled" color="accent" icon="plus" wire:click="openAddModal">
                Tambah Pengeluaran
            </flux:button>
        </div>
    </div>

    @if ($showFilters)
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 p-5 mb-6 bg-zinc-50 border border-zinc-200 rounded-xl dark:bg-zinc-900 dark:border-zinc-800 items-center">
            <flux:input wire:model.live.debounce.300ms="filters.search" placeholder="Cari deskripsi..." icon="magnifying-glass" />

            <flux:input type="date" wire:model.live="filters.start_date" label="Tanggal mulai" />

            <flux:input type="date" wire:model.live="filters.end_date" label="Tanggal akhir" />

            <flux:select wire:model.live="filters.user_id" placeholder="Semua Pengguna">
                <flux:select.option value="">Semua Pengguna</flux:select.option>
                @foreach ($users as $user)
                    <flux:select.option :value="$user->id">{{ $user->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div>
                @if (filled($filters['search']) || filled($filters['start_date']) || filled($filters['end_date']) || filled($filters['user_id']))
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
                <flux:table.column sortable :direction="$sortField === 'description' ? $sortDirection : null" wire:click="sortBy('description')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Deskripsi
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'user_id' ? $sortDirection : null" wire:click="sortBy('user_id')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Dicatat Oleh
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'amount' ? $sortDirection : null" wire:click="sortBy('amount')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Jumlah
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'expense_date' ? $sortDirection : null" wire:click="sortBy('expense_date')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Tanggal Pengeluaran
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($expenses as $expense)
                    <flux:table.row :key="$expense->id">
                        <flux:table.cell class="py-4">
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $expense->description }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-600 dark:text-zinc-300">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $expense->user?->name }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $expense->user?->username }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300 font-medium">
                            Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-500 dark:text-zinc-400">
                            {{ $expense->expense_date->format('d M Y H:i') }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center">
                                <flux:icon.magnifying-glass class="size-8 mb-2 text-zinc-300" />
                                <p class="text-sm font-medium">Tidak ada data pengeluaran yang cocok dengan filter Anda.</p>
                                <flux:button variant="subtle" size="sm" class="mt-2" wire:click="clearAllFilters">
                                    Bersihkan semua filter
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>

    <flux:modal name="expense-form-modal" class="min-w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tambah Pengeluaran</flux:heading>
                <flux:subheading>Catat pengeluaran operasional baru.</flux:subheading>
            </div>

            <livewire:expenses.expense-form :key="'expense-form-new'" />
        </div>
    </flux:modal>
</div>
