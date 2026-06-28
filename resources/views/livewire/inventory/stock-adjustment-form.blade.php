<form wire:submit="save" class="space-y-6">
    <flux:select wire:model="type" label="Jenis Penyesuaian">
        <flux:select.option value="in">Stok Masuk</flux:select.option>
        <flux:select.option value="out">Stok Keluar</flux:select.option>
        <flux:select.option value="adjustment">Setel Stok Langsung</flux:select.option>
    </flux:select>

    <flux:input wire:model="quantity" type="number" label="Jumlah / Nilai" placeholder="Masukkan jumlah..." />

    <flux:textarea wire:model="note" label="Catatan" placeholder="Alasan penyesuaian stok..." />

    <div class="flex gap-2">
        <flux:spacer />
        <flux:button variant="ghost" x-on:click="$flux.modal('stock-adjustment-modal').close()">Batal</flux:button>
        <flux:button type="submit" variant="filled" color="accent">Simpan Perubahan</flux:button>
    </div>
</form>
