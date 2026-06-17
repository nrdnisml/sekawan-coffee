<div class="relative">
    <!-- Top Loading Bar Indicator -->
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

    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">
            Product Catalog
        </h1>

        <div class="flex items-center gap-3">
            <flux:button variant="outline" icon="funnel" wire:click="toggleFilters">
                {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
            </flux:button>

            <flux:button variant="filled" color="accent" icon="plus" wire:click="openAddModal">
                Add Product
            </flux:button>
        </div>
    </div>

    <!-- Filters Section -->
    @if ($showFilters)
        <div
            class="grid grid-cols-1 md:grid-cols-5 gap-4 p-5 mb-6 bg-zinc-50 border border-zinc-200 rounded-xl dark:bg-zinc-900 dark:border-zinc-800 items-center">
            <flux:input wire:model.live.debounce.300ms="filters.name" placeholder="Filter Name..."
                icon="magnifying-glass" />

            <flux:input wire:model.live.debounce.300ms="filters.description" placeholder="Filter Description..."
                icon="document-text" />

            <div class="flex items-center gap-2">
                <flux:input wire:model.live.debounce.300ms="filters.min_price" placeholder="0" icon="banknotes"
                    class="w-full" />
                <span class="text-zinc-400 text-sm">to</span>
                <flux:input wire:model.live.debounce.300ms="filters.max_price" placeholder="10" icon="banknotes"
                    class="w-full" />
            </div>

            <flux:select wire:model.live="filters.status" placeholder="All Status">
                <flux:select.option value="">All Status</flux:select.option>
                <flux:select.option value="1">Active</flux:select.option>
                <flux:select.option value="0">Inactive</flux:select.option>
            </flux:select>

            <div>
                @if (filled($filters['name']) ||
                        filled($filters['description']) ||
                        filled($filters['min_price']) ||
                        filled($filters['max_price']) ||
                        filled($filters['status']))
                    <flux:button variant="subtle" color="danger" icon="x-mark" wire:click="clearAllFilters"
                        class="w-full justify-center">
                        Clear Filters
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    <!-- Table Section -->
    <div
        class="bg-white border border-zinc-200 rounded-xl shadow-sm overflow-hidden dark:bg-zinc-950 dark:border-zinc-800 p-6">
        <flux:table variant="striped">
            <flux:table.columns>
                <flux:table.column sortable :direction="$sortField === 'name' ? $sortDirection : null"
                    wire:click="sortBy('name')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Name
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'description' ? $sortDirection : null"
                    wire:click="sortBy('description')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Description
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'price' ? $sortDirection : null"
                    wire:click="sortBy('price')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Price
                </flux:table.column>
                <flux:table.column sortable :direction="$sortField === 'is_active' ? $sortDirection : null"
                    wire:click="sortBy('is_active')" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Status
                </flux:table.column>
                <flux:table.column align="end" class="font-semibold text-zinc-700 dark:text-zinc-300">
                    Actions
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($products as $product)
                    <flux:table.row :key="$product->id">
                        <flux:table.cell class="py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="size-10 rounded-lg object-cover bg-zinc-100 dark:bg-zinc-800">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $product->name }}</span>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-500 dark:text-zinc-400">
                            {{ Str::limit($product->description, 50) }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-zinc-700 dark:text-zinc-300 font-medium">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </flux:table.cell>
                        <flux:table.cell class="py-4">
                            <flux:badge color="{{ $product->is_active ? 'green' : 'zinc' }}" size="sm"
                                inset="top bottom" class="cursor-pointer"
                                wire:click="toggleStatus({{ $product->id }})">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button icon="clock" size="sm" variant="subtle" square
                                    wire:click="showHistory({{ $product->id }})" />
                                <flux:button icon="pencil-square" size="sm" variant="subtle" square
                                    wire:click="editProduct({{ $product->id }})" />

                                @if ($product->transaction_items_count > 0)
                                    <flux:button icon="trash" size="sm" variant="subtle" square disabled
                                        tooltip="Cannot delete: Sales history exists"
                                        class="text-zinc-300 dark:text-zinc-600" />
                                @else
                                    <flux:button icon="trash" size="sm" variant="subtle" square
                                        class="hover:text-red-600 dark:hover:text-red-400"
                                        wire:click="confirmDelete({{ $product->id }})" />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center">
                                <flux:icon.magnifying-glass class="size-8 mb-2 text-zinc-300" />
                                <p class="text-sm font-medium">No products found matching your criteria.</p>
                                <flux:button variant="subtle" size="sm" class="mt-2"
                                    wire:click="clearAllFilters">
                                    Clear all filters
                                </flux:button>
                            </div>
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
    <flux:modal name="price-history-modal" class="min-w-[30rem]">
        @if ($selectedProductId)
            <livewire:products.price-history :product-id="$selectedProductId" :key="'history-' . $selectedProductId" />
        @endif
    </flux:modal>

    <flux:modal name="product-form-modal" class="min-w-[30rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingProductId ? 'Edit Product' : 'Add Product' }}</flux:heading>
                <flux:subheading>Fill in the details below.</flux:subheading>
            </div>

            <livewire:products.product-form :product-id="$editingProductId" :key="'product-form-' . ($editingProductId ?? 'new')" />
        </div>
    </flux:modal>

    <flux:modal name="delete-product-modal" class="min-w-[25rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Product?</flux:heading>
                <flux:subheading>
                    <p>Are you sure you want to delete this product? <strong>This action cannot be undone.</strong></p>
                </flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button color="danger" wire:click="deleteProduct">Delete Product</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
