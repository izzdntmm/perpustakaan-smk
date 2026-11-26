@php
    $tabUrl = fn (string $key) => route('admin.returns.index', array_filter(['tab' => $key === 'active' ? null : $key, 'q' => $q]));
@endphp

<x-layouts.admin title="Pengembalian">

    <form method="GET" action="{{ route('admin.returns.index') }}" role="search" class="flex flex-wrap items-center gap-2">
        @if ($tab === 'history')
            <input type="hidden" name="tab" value="history">
        @endif
        <label class="input w-full bg-base-100 sm:w-80">
            @svg('heroicon-o-magnifying-glass', 'size-5 opacity-60')
            <input type="search" name="q" value="{{ $q }}" maxlength="100"
                   placeholder="Cari nama, kelas, buku, atau kode" aria-label="Cari transaksi" class="grow">
        </label>
        <button type="submit" class="btn btn-primary">Cari</button>
        @if (filled($q))
            <a href="{{ $tabUrl($tab) }}" class="link link-primary text-sm">Reset</a>
        @endif
    </form>

    {{-- Tab --}}
    <div role="tablist" class="tabs tabs-border mt-6 overflow-x-auto">
        <a role="tab" href="{{ $tabUrl('active') }}"
           @class(['tab shrink-0 gap-2 whitespace-nowrap', 'tab-active font-semibold' => $tab === 'active'])>
            Belum dikembalikan
            <span class="badge badge-sm badge-ghost">{{ $counts['active'] }}</span>
            @if ($counts['overdue'] > 0)
                <span class="badge badge-sm badge-error badge-soft">{{ $counts['overdue'] }} terlambat</span>
            @endif
        </a>
        <a role="tab" href="{{ $tabUrl('history') }}"
           @class(['tab shrink-0 gap-2 whitespace-nowrap', 'tab-active font-semibold' => $tab === 'history'])>
            Riwayat pengembalian
            <span class="badge badge-sm badge-ghost">{{ $counts['history'] }}</span>
        </a>
    </div>

    <section class="mt-4 rounded-box border border-base-300 bg-base-100">
        @if ($loans->isEmpty())
            <x-empty-state icon="arrow-uturn-left" title="Tidak ada data"
                           :message="filled($q)
                               ? 'Tidak ada transaksi yang cocok dengan pencarian ini.'
                               : ($tab === 'history' ? 'Belum ada buku yang dikembalikan.' : 'Semua buku sudah kembali.')"
                           class="border-0" />
        @else
            @include('admin.loans._table', ['loans' => $loans, 'allowReturn' => true])
        @endif
    </section>

    <div class="mt-6">{{ $loans->links() }}</div>

    <x-admin.confirm-modal />

</x-layouts.admin>