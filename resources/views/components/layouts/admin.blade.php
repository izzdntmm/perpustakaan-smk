@props(['title' => 'Dashboard'])

@php
    // Menu menyala otomatis begitu route-nya dibuat (Part 12 sampai 17).
    $menu = [
        ['Dashboard', 'squares-2x2', 'admin.dashboard', 'admin.dashboard'],
        ['Data Buku', 'book-open', 'admin.books.index', 'admin.books.*'],
        ['Peminjaman', 'clipboard-document-list', 'admin.loans.index', 'admin.loans.*'],
        ['Pengembalian', 'arrow-uturn-left', 'admin.returns.index', 'admin.returns.*'],
        ['Laporan', 'document-chart-bar', 'admin.reports.index', 'admin.reports.*'],
    ];

    $userName = auth()->user()?->name ?? 'Pustakawan';
@endphp

<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => $title.' - Admin'])
</head>
<body class="bg-base-100 text-base-content antialiased">

<div class="drawer lg:drawer-open">
    <input id="admin-drawer" type="checkbox" class="drawer-toggle" />

    {{-- Konten --}}
    <div class="drawer-content flex min-h-screen flex-col">

        <header class="sticky top-0 z-20 flex items-center justify-between gap-4 border-b border-base-300 bg-base-100/90 px-4 py-4 backdrop-blur sm:px-8">
            <div class="flex items-center gap-3">
                <label for="admin-drawer" class="btn btn-square btn-ghost btn-sm lg:hidden" aria-label="Buka menu">
                    @svg('heroicon-o-bars-3', 'size-6')
                </label>
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-base-content/60">Administrator</p>
                    <h1 class="text-xl font-bold text-neutral sm:text-2xl">{{ $title }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="grid size-9 place-items-center rounded-full bg-primary/10 font-bold text-primary">
                    {{ mb_strtoupper(mb_substr($userName, 0, 1)) }}
                </div>
                <div class="hidden leading-tight sm:block">
                    <p class="text-sm font-semibold text-neutral">{{ $userName }}</p>
                    <p class="text-xs text-base-content/60">Administrator</p>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-8">
            <x-alert />
            {{ $slot }}
        </main>
    </div>

    {{-- Sidebar --}}
    <div class="drawer-side z-40">
        <label for="admin-drawer" aria-label="Tutup menu" class="drawer-overlay"></label>

        <aside class="flex min-h-full w-72 flex-col border-r border-base-300 bg-base-100 p-5">
            <div class="mb-8 flex items-center gap-3 px-2">
                @svg('heroicon-o-book-open', 'size-8 shrink-0 text-primary')
                <div class="leading-tight">
                    <div class="font-heading text-lg font-bold text-neutral">Perpustakaan</div>
                    <div class="text-sm text-base-content/70">Admin Panel</div>
                </div>
            </div>

            <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-base-content/50">Menu Utama</p>

            <nav class="space-y-1">
                @foreach ($menu as [$label, $icon, $routeName, $pattern])
                    @php
                        $ready = Route::has($routeName);
                        $active = $ready && request()->routeIs($pattern);
                    @endphp
                    <a href="{{ $ready ? route($routeName) : '#' }}"
                       @class([
                           'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition',
                           'bg-primary/10 font-semibold text-primary' => $active,
                           'font-medium text-base-content/80 hover:bg-base-200' => ! $active && $ready,
                           'pointer-events-none font-medium text-base-content/30' => ! $ready,
                       ])>
                        @svg('heroicon-o-'.$icon, 'size-5')
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto space-y-1 border-t border-base-300 pt-4">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content/80 hover:bg-base-200">
                    @svg('heroicon-o-arrow-top-right-on-square', 'size-5')
                    Lihat website
                </a>

                @if (Route::has('admin.logout'))
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content/80 hover:bg-base-200">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'size-5')
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