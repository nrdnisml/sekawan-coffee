<form wire:submit="save" class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row">
        <div class="w-full md:w-1/3">
            <div class="relative group">
                <div class="aspect-square rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-800 flex items-center justify-center overflow-hidden bg-zinc-50 dark:bg-zinc-900">
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="object-cover w-full h-full">
                    @elseif ($existingImage)
                        <img src="{{ $existingImage }}" class="object-cover w-full h-full">
                    @else
                        <flux:icon.photo class="size-10 text-zinc-300" />
                    @endif
                    
                    <label class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-white text-sm font-medium">
                        Change Image
                        <input type="file" wire:model="image" class="hidden" accept="image/*">
                    </label>
                </div>
                <div wire:loading wire:target="image" class="mt-2 text-xs text-zinc-500">Uploading...</div>
                @error('image') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex-1 space-y-6">
            <flux:input wire:model="name" label="Product Name" placeholder="e.g. Arabica Beans" />

            <flux:textarea wire:model="description" label="Description" placeholder="Brief description of the product..." rows="3" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:input type="number" step="0.01" wire:model="price" label="Price" icon="banknotes" />

        <div class="flex items-center pt-8">
            <flux:checkbox wire:model="is_active" label="Product is active" />
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button type="submit" variant="filled" color="accent">
            {{ $productId ? 'Update Product' : 'Create Product' }}
        </flux:button>
    </div>
</form>
