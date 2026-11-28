@php
    $tabs = [
        'active' => 'Belum dikembalikan',
        'overdue' => 'Terlambat',
        'history' => 'Riwayat pengembalian',
    ];

    $tabUrl = fn (string $key) => route('admin.returns.index', array_filter([
        'tab' => $key === 'active' ? null : $key,
        'q' => $q,
    ]));

    // Tab Terlambat: dikelompokkan per kelas agar mudah ditindaklanjuti
    $groups = $tab === 'overdue'
        ? $loans->getCollection()->groupBy(fn ($loan) => mb_strtoupper($loan->borrower_class))
        : null;
@endphp

<x-layouts.admin title="Pengembalian">

    <form method="GET" action="{{ route('admin.returns.index') }}" role="search" class="flex flex-wrap items-center gap-2">
        @if ($tab !== 'active')
            <input type="hidden" name="tab" value="{{ $tab }}">
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
        @foreach ($tabs as $key => $label)
            @php $count = $counts[$key]; @endphp
            <a role="tab" href="{{ $tabUrl($key) }}"
               @class(['tab shrink-0 gap-2 whitespace-nowrap', 'tab-active font-semibold' => $tab === $key])>
                {{ $label }}
                <span @class([
                    'badge badge-sm',
                    'badge-error' => $key === 'overdue' && $count > 0,
                    'badge-ghost' => ! ($key === 'overdue' && $count > 0),
                ])>{{ $count }}</span>
            </a>
        @endforeach
    </div>

    @if ($tab === 'overdue')

        @if ($loans->isEmpty())
            <section class="mt-4 rounded-box border border-base-300 bg-base-100">
                <x-empty-state icon="check-circle" title="Tidak ada keterlambatan"
                               :message="filled($q)
                                   ? 'Tidak ada peminjaman terlambat yang cocok dengan pencarian ini.'
                                   : 'Semua buku yang dipinjam masih dalam batas waktu pengembalian.'"
                               class="border-0" />
            </section>
        @else
            <p class="mt-4 flex items-start gap-2 text-sm text-base-content/70">
                @svg('heroicon-o-information-circle', 'mt-0.5 size-5 shrink-0 text-error')
                <span>
                    {{ $loans->total() }} peminjaman terlambat, dikelompokkan per kelas agar mudah ditindaklanjuti.
                    Yang paling lama terlambat berada di atas dalam tiap kelas.
                </span>
            </p>

            <div class="mt-4 space-y-4">
                @foreach ($groups as $items)
                    <section class="rounded-box border border-error/30 bg-base-100">
                        <header class="flex items-center justify-between gap-3 border-b border-base-300 px-5 py-4">
                            <h3 class="flex items-center gap-2 text-lg font-semibold text-neutral">
                                @svg('heroicon-o-user-group', 'size-5 text-error')
                                {{ $items->first()->borrower_class }}
                            </h3>
                            <span class="badge badge-error badge-soft">{{ $items->count() }} buku</span>
                        </header>

                        @include('admin.loans._table', ['loans' => $items, 'allowReturn' => true])
                    </section>
                @endforeach
            </div>
        @endif

    @else

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

    @endif

    <div class="mt-6">{{ $loans->links() }}</div>

    <x-admin.confirm-modal />

</x-layouts.admin>