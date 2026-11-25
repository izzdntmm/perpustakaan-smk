@props(['title' => 'Dasbor'])

@php
    // Setiap menu hanya aktif bila route-nya sudah dibuat (Part 12 sampai 17).
    $menu = [
        ['Dasbor', 'squares-2x2', 'admin.dashboard', 'admin.dashboard'],
        ['Katalog Buku', 'book-open', 'admin.books.index', 'admin.books.*'],
        ['Sirkulasi', 'arrow-path', 'admin.loans.index', 'admin.loans.*'],
        ['Laporan', 'document-chart-bar', 'admin.reports.index', 'admin.reports.*'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => $title.' - Admin'])
</head>
<body class="bg-base-200 text-base-content">

<div class="drawer lg:drawer-open">
    <input id="admin-drawer" type="checkbox" class="drawer-toggle" />

    {{-- Konten --}}
    <div class="drawer-content flex min-h-screen flex-col">

        {{-- Bar atas khusus HP --}}
        <div class="navbar sticky top-0 z-20 bg-base-100 shadow-sm lg:hidden">
            <div class="flex-none">
                <label for="admin-drawer" class="btn btn-square btn-ghost" aria-label="Buka menu">
                    @svg('heroicon-o-bars-3', 'size-6')
                </label>
            </div>
            <div class="flex-1 px-2 font-semibold">{{ $title }}</div>
        </div>

        <main class="flex-1 p-4 sm:p-6">
            <h1 class="mb-6 hidden text-2xl font-bold lg:block">{{ $title }}</h1>

            <x-alert />
            {{ $slot }}
        </main>
    </div>

    {{-- Sidebar --}}
    <div class="drawer-side z-40">
        <label for="admin-drawer" aria-label="Tutup menu" class="drawer-overlay"></label>

        <aside class="flex min-h-full w-64 flex-col bg-neutral p-4 text-neutral-content">
            <div class="mb-6 flex items-center gap-2 px-2">
                @svg('heroicon-o-building-library', 'size-7 shrink-0')
                <div class="leading-tight">
                    <div class="font-bold">Panel Pustakawan</div>
                    <div class="text-xs opacity-70">SMK Roudlotul Mubtadiin</div>
                </div>
            </div>

            <ul class="menu w-full gap-1 p-0">
                @foreach ($menu as [$label, $icon, $routeName, $pattern])
                    @php $ready = Route::has($routeName); @endphp
                    <li @class(['opacity-40 pointer-events-none' => ! $ready])>
                        <a href="{{ $ready ? route($routeName) : '#' }}"
                           @class(['menu-active' => $ready && request()->routeIs($pattern)])>
                            @svg('heroicon-o-'.$icon, 'size-5')
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-auto space-y-1 border-t border-neutral-content/20 pt-4">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                   class="btn btn-ghost btn-sm w-full justify-start gap-2">
                    @svg('heroicon-o-arrow-top-right-on-square', 'size-4')
                    Lihat Situs
                </a>

                <div class="flex items-center gap-2 px-2 py-2 text-sm">
                    @svg('heroicon-o-user-circle', 'size-5 shrink-0')
                    <span class="truncate">{{ auth()->user()?->name ?? 'Pustakawan' }}</span>
                </div>

                @if (Route::has('admin.logout'))
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm w-full justify-start gap-2">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'size-4')
                            Keluar
                        </button>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</div>

@stack('scripts')
</body>
</html>