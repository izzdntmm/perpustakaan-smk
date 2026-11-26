<x-layouts.admin title="Ubah Buku">
    <a href="{{ route('admin.books.index') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-base-content/70 hover:text-primary">
        @svg('heroicon-o-arrow-left', 'size-4')
        Kembali ke daftar buku
    </a>

    @include('admin.books._form', [
        'action' => route('admin.books.update', $book),
    ])
</x-layouts.admin>