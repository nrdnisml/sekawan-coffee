<div>
    <flux:table variant="striped">
        <flux:table.columns>
            <flux:table.column>Tanggal</flux:table.column>
            <flux:table.column>Tipe</flux:table.column>
            <flux:table.column>Jumlah</flux:table.column>
            <flux:table.column>Sumber</flux:table.column>
            <flux:table.column>Catatan</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($movements as $movement)
                <flux:table.row :key="$movement->id">
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $movement->created_at->format('d M Y H:i') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @php
                            $color = match($movement->type) {
                                'in' => 'green',
                                'out' => 'red',
                                'adjustment' => 'blue',
                                default => 'zinc',
                            };
                        @endphp
                        <flux:badge color="{{ $color }}" size="sm" class="uppercase">
                            {{ $movement->type }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        {{ $movement->quantity_label }}
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">
                        {{ $movement->source_label }}
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500">
                        {{ $movement->note ?? '-' }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                        Belum ada riwayat stok untuk produk ini.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-6 flex justify-end">
        <flux:button variant="ghost" x-on:click="$flux.modal('stock-movement-history-modal').close()">Tutup</flux:button>
    </div>
</div>
