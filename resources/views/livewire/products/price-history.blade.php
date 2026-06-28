<div class="p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Riwayat Harga</h3>

    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">Harga Lama</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Harga Baru</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Diubah Oleh</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse ($history as $record)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:pl-6">
                            ${{ number_format($record->old_price, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            ${{ number_format($record->new_price, 2) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $record->user->name ?? 'Sistem' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $record->changed_at->format('M d, Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                            Belum ada perubahan harga yang tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
