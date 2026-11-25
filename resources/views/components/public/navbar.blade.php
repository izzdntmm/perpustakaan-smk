@php
    $type = request('type');

    $items = [
        ['label' => 'Beranda', 'icon' => 'home', 'url' => route('home'), 'active' => request()->routeIs('home') && ! $type],
        ['label' => 'Koleksi Digital', 'icon' => 'device-tablet', 'url' => route('home', ['type' => 'digital']), 'active' => $type === 'digital'],
        ['label' => 'Koleksi Fisik', 'icon' => 'book-open', 'url' => route('home', ['type' => 'physical']), 'active' => $type === 'physical'],
    ];
@endphp

<header class="sticky top-0 z-30 bg-primary text-primary-content shadow-sm">
    <div class="navbar mx-auto max-w-6xl px-4 sm:px-6">

        <div class="navbar-start gap-1">
            {{-- Menu HP --}}
            <div class="dropdown lg:hidden">
                <div tabindex="0" role="button" class="btn btn-square btn-ghost" aria-label="Buka menu">
                    @svg('heroicon-o-bars-3', 'size-6')
                </div>
                <ul tabindex="0" class="menu dropdown-content z-50 mt-3 w-60 rounded-box bg-base-100 p-2 text-base-content shadow-lg">
                    @foreach ($items as $item)
                        <li>
                            <a href="{{ $item['url'] }}" @class(['menu-active' => $item['active']])>
                                @svg('heroicon-o-'.$item['icon'], 'size-5')
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Nama sekolah --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 px-1">
                @svg('heroicon-o-building-library', 'size-7 shrink-0')
                <span class="leading-tight">
                    <span class="block text-sm font-bold sm:text-base">Perpustakaan</span>
                    <span class="block text-[11px] opacity-80 sm:text-xs">SMK Roudlotul Mubtadiin</span>
                </span>
            </a>
        </div>

        {{-- Menu desktop --}}
        <nav class="navbar-end hidden gap-1 lg:flex" aria-label="Menu utama">
            @foreach ($items as $item)
                <a href="{{ $item['url'] }}"
                   @class([
                       'btn btn-ghost btn-sm gap-2',
                       'bg-primary-content/20' => $item['active'],
                   ])>
                    @svg('heroicon-o-'.$item['icon'], 'size-4')
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>