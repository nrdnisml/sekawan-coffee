<form wire:submit="save" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:input wire:model="name" label="Nama Lengkap" placeholder="Contoh: Kasir Pagi" />

        <flux:input wire:model="username" label="Username" placeholder="e.g. kasir-pagi" />

        <flux:input wire:model="email" label="Alamat Email" type="email" placeholder="Opsional" />

        <flux:input wire:model="password" label="Kata Sandi" type="password" placeholder="{{ $userId ? 'Kosongkan jika tidak ingin mengubah kata sandi' : 'Minimal 8 karakter' }}" />

        <flux:select wire:model="role" label="Peran">
            <flux:select.option value="admin">Admin</flux:select.option>
            <flux:select.option value="cashier">Kasir</flux:select.option>
        </flux:select>

        <div class="flex items-center pt-8">
            <flux:checkbox wire:model="is_active" label="Pengguna aktif" />
        </div>
    </div>

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="ghost">Batal</flux:button>
        </flux:modal.close>

        <flux:button type="submit" variant="filled" color="accent">
            {{ $userId ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
        </flux:button>
    </div>
</form>
