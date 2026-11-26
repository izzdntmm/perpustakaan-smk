@props(['loans', 'allowReturn' => false])

<div class="overflow-x-auto p-2 sm:p-3">
    <table class="table">
        <thead>
            <tr class="text-xs uppercase tracking-wider text-base-content/60">
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Tanggal</th>
                <th>Jatuh tempo</th>
                <th>Status</th>
                <th class="text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loans as $loan)
                <tr>
                    <td>
                        <p class="font-semibold text-neutral">{{ $loan->borrower_name }}</p>
                        <p class="text-xs text-base-content/60">{{ $loan->borrower_class }} &middot; {{ $loan->code() }}</p>
                    </td>

                    <td>
                        <p class="line-clamp-2 max-w-56 text-neutral">{{ $loan->book->title }}</p>
                        @if ($loan->book->shelf_location)
                            <p class="text-xs text-base-content/60">Rak {{ $loan->book->shelf_location }}</p>
                        @endif
                    </td>

                    <td class="whitespace-nowrap text-sm">
                        {{ $loan->loan_date->translatedFormat('d M Y') }}
                        <p class="text-xs text-base-content/60">{{ $loan->source->label() }}</p>
                    </td>

                    <td class="whitespace-nowrap text-sm">
                        {{ $loan->due_date?->translatedFormat('d M Y') ?? '-' }}
                    </td>

                    <td>
                        <x-badge.loan-status :loan="$loan" />
                        @if ($loan->returned_at)
                            <p class="mt-1 text-xs text-base-content/60">{{ $loan->returned_at->translatedFormat('d M Y, H:i') }}</p>
                        @endif
                    </td>

                    <td>
                        <div class="flex justify-end gap-1">
                            @if ($loan->status === \App\Enums\LoanStatus::Pending)
                                <button type="button" class="btn btn-primary btn-sm"
                                        data-confirm
                                        data-url="{{ route('admin.loans.confirm', $loan) }}"
                                        data-title="Konfirmasi pengambilan buku?"
                                        data-message="{{ $loan->borrower_name }} ({{ $loan->borrower_class }}) mengambil “{{ $loan->book->title }}”. Stok rak berkurang 1 dan batas pengembalian dihitung {{ $loan->duration_days }} hari dari hari ini."
                                        data-label="Ya, konfirmasi">
                                    @svg('heroicon-o-check', 'size-4')
                                    Konfirmasi
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm text-error"
                                        data-confirm data-tone="error"
                                        data-url="{{ route('admin.loans.cancel', $loan) }}"
                                        data-title="Batalkan pengajuan?"
                                        data-message="Pengajuan {{ $loan->code() }} atas nama {{ $loan->borrower_name }} untuk “{{ $loan->book->title }}” akan dibatalkan."
                                        data-label="Ya, batalkan">
                                    Batalkan
                                </button>
                            @elseif ($allowReturn && $loan->status === \App\Enums\LoanStatus::Borrowed)
                                <button type="button" class="btn btn-primary btn-sm"
                                        data-confirm
                                        data-url="{{ route('admin.returns.update', $loan) }}"
                                        data-title="Tandai buku dikembalikan?"
                                        data-message="“{{ $loan->book->title }}” dari {{ $loan->borrower_name }} ({{ $loan->borrower_class }}) dikembalikan. Stok rak bertambah 1."
                                        data-label="Ya, sudah kembali">
                                    @svg('heroicon-o-arrow-uturn-left', 'size-4')
                                    Tandai kembali
                                </button>
                            @else
                                <span class="px-2 text-base-content/30">-</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>