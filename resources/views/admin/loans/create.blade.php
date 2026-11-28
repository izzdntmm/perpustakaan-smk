<x-layouts.admin title="Peminjaman Langsung">

    <a href="{{ route('admin.loans.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-base-content/70 hover:text-primary">
        @svg('heroicon-o-arrow-left', 'size-4')
        Kembali ke peminjaman
    </a>

    @if ($books->isEmpty())
        <x-empty-state icon="book-open" title="Tidak ada buku yang bisa dipinjamkan"
                       message="Semua buku fisik sedang habis atau sudah dipesan lewat pengajuan online." />
    @else
        <form method="POST" action="{{ route('admin.loans.store') }}"
              class="max-w-2xl rounded-box border border-base-300 bg-base-100 p-5 sm:p-6">
            @csrf

            <p class="flex items-start gap-3 rounded-lg bg-primary/5 p-3 text-sm text-base-content/80">
                @svg('heroicon-o-information-circle', 'mt-0.5 size-5 shrink-0 text-primary')
                <span>
                    Untuk siswa atau guru yang datang langsung ke meja. Peminjaman langsung berstatus
                    <strong>Dipinjam</strong>: stok rak berkurang 1 dan batas pengembalian dihitung dari hari ini.
                </span>
            </p>

            <div class="mt-4 grid gap-x-4 gap-y-2 sm:grid-cols-2">
                <x-admin.field name="book_id" label="Buku" class="sm:col-span-2">
                    <select name="book_id" required class="select w-full @error('book_id') select-error @enderror">
                        <option value="">Pilih buku</option>
                        @foreach ($books as $b)
                            <option value="{{ $b->id }}" @selected((int) old('book_id') === $b->id)>
                                {{ $b->title }} - {{ $b->author }} ({{ $b->stock_available - $b->pending_count }} tersedia)
                            </option>
                        @endforeach
                    </select>
                </x-admin.field>

                <x-admin.field name="borrower_name" label="Nama peminjam">
                    <input type="text" name="borrower_name" value="{{ old('borrower_name') }}" required
                           minlength="3" maxlength="100" placeholder="Contoh: Ahmad Fauzi"
                           class="input w-full @error('borrower_name') input-error @enderror">
                </x-admin.field>

                <x-admin.field name="borrower_class" label="Kelas / Jabatan">
                    <input type="text" name="borrower_class" value="{{ old('borrower_class') }}" required
                           maxlength="50" placeholder="Contoh: XI TKJ 1 atau Guru"
                           class="input w-full @error('borrower_class') input-error @enderror">
                </x-admin.field>

                <x-admin.field name="duration_days" label="Durasi pinjam">
                    <select name="duration_days" required class="select w-full @error('duration_days') select-error @enderror">
                        @foreach (\App\Models\Loan::DURATIONS as $d)
                            <option value="{{ $d }}" @selected((int) old('duration_days', \App\Models\Loan::DEFAULT_DURATION) === $d)>
                                {{ $d }} hari{{ $d === \App\Models\Loan::DEFAULT_DURATION ? ' (standar)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="btn btn-primary">
                    @svg('heroicon-o-check', 'size-5')
                    Catat peminjaman
                </button>
                <a href="{{ route('admin.loans.index') }}" class="btn">Batal</a>
            </div>
        </form>
    @endif

</x-layouts.admin>