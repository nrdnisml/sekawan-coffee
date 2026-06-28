<form wire:submit="save" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <flux:textarea wire:model="description" label="Deskripsi" placeholder="Contoh: susu, gelas kertas, pembayaran listrik" rows="3" />
        </div>

        <flux:input type="number" step="0.01" min="0.01" wire:model="amount" label="Jumlah" icon="banknotes" />

        <flux:input type="date" wire:model="expense_date" label="Tanggal Pengeluaran" />
    </div>

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">Batal</flux:button>
        </flux:modal.close>

        <flux:button type="submit" variant="filled" color="accent">
            Tambah Pengeluaran
        </flux:button>
    </div>
</form>
