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

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">Log Audit</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Riwayat aktivitas hanya-baca untuk kebutuhan peninjauan admin.</p>
        </div>

        <flux:button variant="outline" icon="funnel" wire:click="toggleFilters">
            {{ $showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}
        </flux:button>
    </div>

    @if ($showFilters)
        <div class="grid grid-cols-1 items-center gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-5 mb-6 md:grid-cols-5 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:input wire:model.live.debounce.300ms="filters.actor" placeholder="Filter aktor..." icon="magnifying-glass" />

            <flux:select wire:model.live="filters.action" placeholder="Semua Aksi">
                <flux:select.option value="">Semua Aksi</flux:select.option>
                <flux:select.option value="create">Buat</flux:select.option>
                <flux:select.option value="update">Ubah</flux:select.option>
                <flux:select.option value="delete">Hapus</flux:select.option>
                <flux:select.option value="login">Login</flux:select.option>
                <flux:select.option value="logout">Logout</flux:select.option>
                <flux:select.option value="sync">Sync</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="filters.entity" placeholder="Semua Entitas">
                <flux:select.option value="">Semua Entitas</flux:select.option>
                <flux:select.option value="users">Pengguna</flux:select.option>
                <flux:select.option value="products">Produk</flux:select.option>
                <flux:select.option value="inventory">Inventaris</flux:select.option>
                <flux:select.option value="expenses">Pengeluaran</flux:select.option>
                <flux:select.option value="transactions">Transaksi</flux:select.option>
            </flux:select>

            <flux:input wire:model.live="filters.date" type="date" />

            <div>
                @if (filled($filters['actor']) || filled($filters['action']) || filled($filters['entity']) || filled($filters['date']))
                    <flux:button variant="subtle" color="danger" icon="x-mark" wire:click="clearAllFilters" class="w-full justify-center">
                        Bersihkan Filter
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <flux:table variant="striped">
            <flux:table.columns>
                <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Aktor
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'action' ? $sortDirection : null" wire:click="sortBy('action')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Aksi
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'entity' ? $sortDirection : null" wire:click="sortBy('entity')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Entitas
                </flux:table.column>
                <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Detail
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'created_at' ? $sortDirection : null" wire:click="sortBy('created_at')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Tanggal
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($logs as $log)
                    <flux:table.row :key="$log->id">
                        <flux:table.cell class="py-4">
                            @if ($log->user)
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $log->user->name ?: $log->user->username }}</span>
                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $log->user->username }}</span>
                                </div>
                            @else
                                <span class="font-medium text-zinc-900 dark:text-white">Sistem</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="sky" size="sm" inset="top bottom">{{ ucfirst($log->action) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300">
                            <div class="flex flex-col gap-1">
                                <span class="font-medium">{{ str($log->entity)->replace('_', ' ')->headline() }}</span>
                                <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $log->entity_id ? '#'.$log->entity_id : 'Tanpa ID data' }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-sm text-zinc-600 dark:text-zinc-300">
                            {{ $log->description ?: 'Tidak ada detail tambahan.' }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $log->created_at?->format('d M Y H:i') }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center">
                                <flux:icon.magnifying-glass class="mb-2 size-8 text-zinc-300" />
                                <p class="text-sm font-medium">Tidak ada log audit yang cocok dengan filter Anda.</p>
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
        {{ $logs->links() }}
    </div>
</div>
