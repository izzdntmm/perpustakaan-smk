@php
    $type = request('type');
    $onCatalog = request()->routeIs('catalog');

    $items = [
        ['Beranda', route('home'), request()->routeIs('home')],
        ['Katalog Buku', route('catalog'), $onCatalog && ! $type],
        ['Buku Digital', route('catalog', ['type' => 'digital']), $onCatalog && $type === 'digital'],
        ['Buku Fisik', route('catalog', ['type' => 'physical']), $onCatalog && $type === 'physical'],
        ['Tentang', route('home').'#tentang', false],
    ];

    $adminUrl = Route::has('admin.login') ? route('admin.login') : '#';
@endphp

<header class="sticky top-0 z-30 border-b border-base-300 bg-base-100/90 backdrop-blur">
    <x-container class="flex h-16 items-center justify-between gap-4 lg:h-20">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            @svg('heroicon-o-book-open', 'size-8 shrink-0 text-primary')
            <span class="leading-tight">
                <span class="block font-heading text-base font-bold text-neutral">Perpustakaan</span>
                <span class="block text-xs text-base-content/70">SMK Roudlotul Mubtadiin</span>
            </span>
        </a>

        {{-- Menu desktop --}}
        <nav class="hidden items-center gap-8 lg:flex" aria-label="Menu utama">
            @foreach ($items as [$label, $url, $active])
                <a href="{{ $url }}"
                   @class([
                       'text-sm font-medium transition hover:text-primary',
                       'text-primary' => $active,
                       'text-neutral' => ! $active,
                   ])>{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ $adminUrl }}"
               class="hidden items-center gap-2 text-sm font-medium text-base-content/80 transition hover:text-primary sm:inline-flex">
                @svg('heroicon-o-shield-check', 'size-5')
                Admin
            </a>

            {{-- Menu HP --}}
            <div class="dropdown dropdown-end lg:hidden">
                <div tabindex="0" role="button" class="btn btn-square btn-ghost" aria-label="Buka menu">
                    @svg('heroicon-o-bars-3', 'size-6')
                </div>
                <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-60 rounded-box border border-base-300 bg-base-100 p-2 shadow-lg">
                    @foreach ($items as [$label, $url, $active])
                        <li>
                            <a href="{{ $url }}" @class(['font-semibold text-primary' => $active])>{{ $label }}</a>
                        </li>
                    @endforeach
                    <li class="mt-1 border-t border-base-300 pt-1">
                        <a href="{{ $adminUrl }}">
                            @svg('heroicon-o-shield-check', 'size-5')
                            Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </x-container>
</header>