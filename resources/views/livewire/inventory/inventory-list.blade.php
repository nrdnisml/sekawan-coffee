<div class="relative">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">
            Inventory & Stock
        </h1>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 flex gap-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search products..." icon="magnifying-glass" class="max-w-md" />
    </div>

    <!-- Table Section -->
    <div class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6">
        <flux:table variant="striped">
            <flux:table.columns>
                <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Name</flux:table.column>
                <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Current Stock</flux:table.column>
                <flux:table.column class="font-semibold text-zinc-700 dark:text-zinc-300">Status</flux:table.column>
                <flux:table.column align="end" class="font-semibold text-zinc-700 dark:text-zinc-300">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell class="py-4 font-medium text-zinc-900 dark:text-white">
                            {{ $product->name }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300 font-medium">
                            {{ $product->stock }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="{{ $product->is_active ? 'green' : 'zinc' }}" size="sm">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button size="sm" variant="subtle" wire:click="openHistoryModal({{ $product->id }})">
                                    History
                                </flux:button>
                                <flux:button size="sm" variant="filled" color="accent" wire:click="openAdjustmentModal({{ $product->id }})">
                                    Adjust
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            No products found.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <!-- Modals -->
    <flux:modal name="stock-adjustment-modal" class="min-w-[30rem]">
        @if ($adjustingProductId)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Adjust Stock</flux:heading>
                    <flux:subheading>Manage manual stock movements.</flux:subheading>
                </div>
                <livewire:inventory.stock-adjustment-form :product-id="$adjustingProductId" :key="'adj-' . $adjustingProductId" />
            </div>
        @endif
    </flux:modal>

    <flux:modal name="stock-movement-history-modal" class="min-w-[45rem]">
        @if ($selectedProductId)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Stock History</flux:heading>
                    <flux:subheading>Audit trail for this product.</flux:subheading>
                </div>
                <livewire:inventory.stock-movement-history :product-id="$selectedProductId" :key="'hist-' . $selectedProductId" />
            </div>
        @endif
    </flux:modal>
</div>
