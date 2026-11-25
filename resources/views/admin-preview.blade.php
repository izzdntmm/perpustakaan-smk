<x-layouts.admin title="Pratinjau Layout Admin">

    <div class="mb-4 flex flex-wrap gap-2">
        <button class="btn btn-primary btn-sm">
            <x-heroicon-o-plus class="size-4" /> Tambah Buku
        </button>
        <button class="btn btn-outline btn-sm">
            <x-heroicon-o-printer class="size-4" /> Cetak
        </button>
    </div>

    <div class="overflow-x-auto rounded-box bg-base-100 shadow-sm">
        <table class="table">
            <thead>
                <tr>
                    <th>Peminjam</th>
                    <th>Kelas</th>
                    <th>Buku</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loans as $loan)
                    <tr @class(['bg-error/10' => $loan->isOverdue()])>
                        <td class="font-medium">{{ $loan->borrower_name }}</td>
                        <td>{{ $loan->borrower_class }}</td>
                        <td>{{ $loan->book->title }}</td>
                        <td>{{ $loan->due_date?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td><x-badge.loan-status :loan="$loan" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-layouts.admin>