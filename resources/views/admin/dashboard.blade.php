@php
    $loansUrl = Route::has('admin.loans.index') ? route('admin.loans.index') : '#';

    // Ikon dan warna per jenis aktivitas
    $activityStyle = [
        'request' => ['clipboard-document-list', 'bg-primary/10 text-primary'],
        'borrowed' => ['book-open', 'bg-secondary/10 text-secondary'],
        'returned' => ['arrow-uturn-left', 'bg-accent/10 text-accent'],
        'book' => ['pencil-square', 'bg-base-200 text-base-content/70'],
    ];
@endphp

<x-layouts.admin title="Dashboard">

    {{-- Sapaan --}}
    <section>
        <p class="text-xs font-medium uppercase tracking-[0.18em] text-base-content/60">Selamat datang</p>
        <h2 class="mt-2 text-3xl font-semibold text-neutral sm:text-4xl">Halo, {{ auth()->user()->name }}</h2>
        <p class="mt-2 text-base-content/70">Berikut ringkasan aktivitas perpustakaan hari ini.</p>
    </section>

    {{-- Kartu statistik --}}
    <section class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Ringkasan statistik">
        <x-admin.stat-card icon="rectangle-stack" tone="primary" label="Total judul buku" :value="$stats['titles']"
            caption="Koleksi aktif" />

        <x-admin.stat-card icon="archive-box" tone="accent" label="Eksemplar fisik" :value="$stats['copies']"
            caption="Total stok tercatat" />

        <x-admin.stat-card icon="book-open" tone="secondary" label="Sedang dipinjam" :value="$stats['borrowed']"
            caption="Transaksi aktif" />

        <x-admin.stat-card icon="clock" tone="error" :href="route('admin.returns.index', ['tab' => 'overdue'])"
            label="Terlambat" :value="$stats['overdue']" caption="Perlu tindak lanjut" />
    </section>

    <div class="mt-6 grid gap-4 xl:grid-cols-3">

        <div class="space-y-4 xl:col-span-2">

            {{-- Pengajuan terbaru --}}
            <section class="rounded-box border border-base-300 bg-base-100">
                <div class="flex items-start justify-between gap-4 p-5 pb-3 sm:p-6 sm:pb-3">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-xl font-semibold text-neutral">Pengajuan terbaru</h3>
                            @if ($stats['pending'] > 0)
                                <span class="badge badge-soft badge-warning">{{ $stats['pending'] }} menunggu</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-base-content/70">Permintaan peminjaman buku</p>
                    </div>
                    <a href="{{ $loansUrl }}"
                        class="shrink-0 text-sm font-semibold text-neutral hover:text-primary">Lihat semua</a>
                </div>

                @if ($recentLoans->isEmpty())
                    <p class="px-6 pb-6 text-sm text-base-content/60">Belum ada pengajuan peminjaman.</p>
                @else
                    <div class="overflow-x-auto px-3 pb-3 sm:px-4">
                        <table class="table">
                            <thead>
                                <tr class="text-xs uppercase tracking-wider text-base-content/60">
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentLoans as $loan)
                                    <tr>
                                        <td>
                                            <p class="font-semibold text-neutral">{{ $loan->borrower_name }}</p>
                                            <p class="text-xs text-base-content/60">{{ $loan->borrower_class }}</p>
                                        </td>
                                        <td class="max-w-48 truncate">{{ $loan->book->title }}</td>
                                        <td class="whitespace-nowrap">{{ $loan->loan_date->translatedFormat('d M Y') }}</td>
                                        <td><x-badge.loan-status :loan="$loan" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Peminjaman terlambat --}}
            <section id="terlambat" class="scroll-mt-24 rounded-box border border-base-300 bg-base-100">
                <div class="p-5 pb-3 sm:p-6 sm:pb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-xl font-semibold text-neutral">Peminjaman terlambat</h3>
                        @if ($stats['overdue'] > 0)
                            <span class="badge badge-soft badge-error">{{ $stats['overdue'] }} buku</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-base-content/70">Sudah melewati batas pengembalian dan perlu
                        ditindaklanjuti</p>
                </div>

                @if ($overdueLoans->isEmpty())
                    <div class="flex items-center gap-3 px-6 pb-6 text-sm text-base-content/70">
                        @svg('heroicon-o-check-circle', 'size-6 shrink-0 text-accent')
                        Tidak ada peminjaman terlambat. Semua buku kembali tepat waktu.
                    </div>
                @else
                    <div class="overflow-x-auto px-3 pb-3 sm:px-4">
                        <table class="table">
                            <thead>
                                <tr class="text-xs uppercase tracking-wider text-base-content/60">
                                    <th>Peminjam</th>
                                    <th>Buku</th>
                                    <th>Jatuh tempo</th>
                                    <th>Keterlambatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overdueLoans as $loan)
                                    <tr class="bg-error/5">
                                        <td>
                                            <p class="font-semibold text-neutral">{{ $loan->borrower_name }}</p>
                                            <p class="text-xs text-base-content/60">{{ $loan->borrower_class }}</p>
                                        </td>
                                        <td class="max-w-48 truncate">{{ $loan->book->title }}</td>
                                        <td class="whitespace-nowrap">{{ $loan->due_date->translatedFormat('d M Y') }}</td>
                                        <td><x-badge.loan-status :loan="$loan" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($stats['overdue'] > $overdueLoans->count())
                        <p class="px-6 pb-5 text-xs text-base-content/60">
                            Menampilkan {{ $overdueLoans->count() }} dari {{ $stats['overdue'] }} peminjaman terlambat.
                            <a href="{{ route('admin.returns.index', ['tab' => 'overdue']) }}" class="link link-primary">Lihat
                                semua</a>
                        </p>
                    @endif
                @endif
            </section>
        </div>

        {{-- Aktivitas singkat --}}
        <aside class="h-fit rounded-box border border-base-300 bg-base-100 p-5 sm:p-6">
            <h3 class="text-xl font-semibold text-neutral">Aktivitas singkat</h3>
            <p class="mt-1 text-sm text-base-content/70">Ringkasan sirkulasi</p>

            @if ($activities->isEmpty())
                <p class="mt-6 text-sm text-base-content/60">Belum ada aktivitas.</p>
            @else
                <ul class="mt-4 divide-y divide-base-300">
                    @foreach ($activities as $activity)
                        @php [$icon, $style] = $activityStyle[$activity['type']]; @endphp
                        <li class="flex items-start gap-3 py-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-lg {{ $style }}">
                                @svg('heroicon-o-' . $icon, 'size-4')
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-neutral">{{ $activity['title'] }}</p>
                                <p class="truncate text-xs text-base-content/60">
                                    {{ $activity['detail'] }} &middot; {{ $activity['at']->diffForHumans() }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </aside>
    </div>

</x-layouts.admin>