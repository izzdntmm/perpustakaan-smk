@php
    $tabs = [
        'pending' => 'Menunggu pengambilan',
        'borrowed' => 'Sedang dipinjam',
        'overdue' => 'Terlambat',
        'all' => 'Semua transaksi',
    ];

    $tabUrl = fn (string $key) => route('admin.loans.index', array_filter(['status' => $key, 'q' => $q]));

    $emptyMessages = [
        'pending' => 'Tidak ada pengajuan yang menunggu diambil.',
        'borrowed' => 'Tidak ada buku yang sedang dipinjam.',
        'overdue' => 'Tidak ada peminjaman terlambat. Semua buku kembali tepat waktu.',
        'all' => 'Belum ada transaksi peminjaman.',
    ];
@endphp

<x-layouts.admin title="Peminjaman">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.loans.index') }}" role="search" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <label class="input w-full bg-base-100 sm:w-80">
                @svg('heroicon-o-magnifying-glass', 'size-5 opacity-60')
                <input type="search" name="q" value="{{ $q }}" maxlength="100"
                       placeholder="Cari nama, kelas, buku, atau kode" aria-label="Cari transaksi" class="grow">
            </label>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if (filled($q))
                <a href="{{ route('admin.loans.index', ['status' => $status]) }}" class="link link-primary text-sm">Reset</a>
            @endif
        </form>

        <a href="{{ route('admin.loans.create') }}" class="btn btn-primary">
            @svg('heroicon-o-plus', 'size-5')
            Catat Peminjaman Langsung
        </a>
    </div>

    {{-- Tab --}}
    <div role="tablist" class="tabs tabs-border mt-6 overflow-x-auto">
        @foreach ($tabs as $key => $label)
            <a role="tab" href="{{ $tabUrl($key) }}"
               @class(['tab shrink-0 gap-2 whitespace-nowrap', 'tab-active font-semibold' => $status === $key])>
                {{ $label }}
                <span @class([
                    'badge badge-sm',
                    'badge-warning' => $key === 'pending' && $counts['pending'] > 0,
                    'badge-error' => $key === 'overdue' && $counts['overdue'] > 0,
                    'badge-ghost' => ! (($key === 'pending' && $counts['pending'] > 0) || ($key === 'overdue' && $counts['overdue'] > 0)),
                ])>{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <section class="mt-4 rounded-box border border-base-300 bg-base-100">
        @if ($loans->isEmpty())
            <x-empty-state icon="clipboard-document-list" title="Tidak ada data"
                           :message="filled($q) ? 'Tidak ada transaksi yang cocok dengan pencarian ini.' : $emptyMessages[$status]"
                           class="border-0" />
        @else
            @include('admin.loans._table', ['loans' => $loans, 'allowReturn' => false])
        @endif
    </section>

    <div class="mt-6">{{ $loans->links() }}</div>

    <x-admin.confirm-modal />

</x-layouts.admin>