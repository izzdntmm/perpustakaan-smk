@props(['filters', 'categories', 'total'])

@php
    // Bangun URL dengan filter saat ini, lalu timpa sebagian (null = hapus).
    // Parameter "page" sengaja tidak ikut, jadi pindah filter selalu ke halaman 1.
    $url = fn (array $override = []) => route('catalog', array_filter(
        array_merge($filters, $override),
        fn ($v) => filled($v)
    ));

    $types = [
        [null, 'Semua', 'squares-2x2'],
        ['digital', 'Digital', 'device-tablet'],
        ['physical', 'Fisik', 'book-open'],
    ];

    $activeCategory = $categories->firstWhere('slug', $filters['category']);
    $activeType = \App\Enums\BookType::tryFrom((string) $filters['type']);
    $hasFilter = filled($filters['q']) || $activeType || $activeCategory;
@endphp

<div class="space-y-4">

    {{-- Kotak pencarian --}}
    <form action="{{ route('catalog') }}" method="GET" role="search" class="flex gap-2">
        @if ($filters['type'])
            <input type="hidden" name="type" value="{{ $filters['type'] }}">
        @endif
        @if ($filters['category'])
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
        @endif

        <label class="input h-12 w-full bg-base-100">
            @svg('heroicon-o-magnifying-glass', 'size-5 opacity-60')
            <input type="search"
                   name="q"
                   value="{{ $filters['q'] }}"
                   maxlength="100"
                   placeholder="Cari judul atau penulis"
                   aria-label="Cari judul atau penulis"
                   class="grow">
        </label>

        <button type="submit" class="btn btn-primary h-12" aria-label="Cari">
            @svg('heroicon-o-magnifying-glass', 'size-5')
            <span class="hidden sm:inline">Cari</span>
        </button>
    </form>

    {{-- Filter tipe buku --}}
    <div class="flex flex-wrap items-center gap-x-4 gap-y-3">
        <span class="text-sm font-medium text-base-content/70">Tipe</span>
        <div class="join" role="group" aria-label="Filter tipe buku">
            @foreach ($types as [$value, $label, $icon])
                @php $active = ($filters['type'] ?? null) === $value; @endphp
                <a href="{{ $url(['type' => $value]) }}"
                   @if ($active) aria-current="true" @endif
                   @class([
                       'btn btn-sm join-item gap-1.5',
                       'btn-primary' => $active,
                       'border-base-300 bg-base-100 text-neutral' => ! $active,
                   ])>
                    @svg('heroicon-o-'.$icon, 'size-4')
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Filter kategori (bisa digeser ke samping di HP) --}}
    <div class="-mx-4 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
        <div class="flex w-max gap-2 sm:w-auto sm:flex-wrap" role="group" aria-label="Filter kategori">
            <a href="{{ $url(['category' => null]) }}"
               @if (! $activeCategory) aria-current="true" @endif
               @class([
                   'rounded-full border px-4 py-1.5 text-sm transition',
                   'border-primary bg-primary/10 font-semibold text-primary' => ! $activeCategory,
                   'border-base-300 bg-base-100 text-neutral hover:border-primary/40' => $activeCategory,
               ])>Semua kategori</a>

            @foreach ($categories as $category)
                @php $active = $filters['category'] === $category->slug; @endphp
                <a href="{{ $url(['category' => $category->slug]) }}"
                   @if ($active) aria-current="true" @endif
                   @class([
                       'whitespace-nowrap rounded-full border px-4 py-1.5 text-sm transition',
                       'border-primary bg-primary/10 font-semibold text-primary' => $active,
                       'border-base-300 bg-base-100 text-neutral hover:border-primary/40' => ! $active,
                   ])>{{ $category->name }}</a>
            @endforeach
        </div>
    </div>

    {{-- Ringkasan hasil dan filter aktif --}}
    <div class="flex flex-wrap items-center gap-2 border-t border-base-300 pt-4 text-sm">
        <span class="text-base-content/70">{{ $total }} judul ditemukan</span>

        @if (filled($filters['q']))
            <a href="{{ $url(['q' => null]) }}" class="badge badge-soft badge-primary gap-1.5 py-3"
               aria-label="Hapus kata kunci {{ $filters['q'] }}">
                Kata kunci: {{ $filters['q'] }}
                @svg('heroicon-o-x-mark', 'size-3.5')
            </a>
        @endif

        @if ($activeType)
            <a href="{{ $url(['type' => null]) }}" class="badge badge-soft badge-primary gap-1.5 py-3"
               aria-label="Hapus filter tipe {{ $activeType->label() }}">
                {{ $activeType->label() }}
                @svg('heroicon-o-x-mark', 'size-3.5')
            </a>
        @endif

        @if ($activeCategory)
            <a href="{{ $url(['category' => null]) }}" class="badge badge-soft badge-primary gap-1.5 py-3"
               aria-label="Hapus filter kategori {{ $activeCategory->name }}">
                {{ $activeCategory->name }}
                @svg('heroicon-o-x-mark', 'size-3.5')
            </a>
        @endif

        @if ($hasFilter)
            <a href="{{ route('catalog') }}" class="link link-primary ml-1">Reset filter</a>
        @endif
    </div>
</div>