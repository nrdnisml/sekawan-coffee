<form wire:submit="save" class="space-y-6">
    <flux:select wire:model="type" label="Adjustment Type">
        <flux:select.option value="in">Stock IN (Increase)</flux:select.option>
        <flux:select.option value="out">Stock OUT (Decrease)</flux:select.option>
        <flux:select.option value="adjustment">Direct Adjustment (Set absolute)</flux:select.option>
    </flux:select>

    <flux:input wire:model="quantity" type="number" label="Quantity / Value" placeholder="Enter amount..." />

    <flux:textarea wire:model="note" label="Notes" placeholder="Reason for adjustment..." />

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" x-on:click="$flux.modal('stock-adjustment-modal').close()">Cancel</flux:button>
        <flux:button type="submit" variant="filled" color="accent">Save Changes</flux:button>
    </div>
</form>
