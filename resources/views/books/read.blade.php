<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => 'Baca: '.$book->title])
    <style>
        @media print { body { display: none !important; } }
    </style>
</head>
<body class="h-dvh select-none overflow-hidden bg-base-200 text-base-content antialiased">

<div id="pdf-reader" data-url="{{ $pdfUrl }}" class="flex h-dvh flex-col">

    {{-- Toolbar --}}
    <header class="border-b border-base-300 bg-base-100">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-3 py-2 sm:px-5">

            <a href="{{ route('books.show', $book) }}" class="btn btn-ghost btn-sm gap-1">
                @svg('heroicon-o-arrow-left', 'size-4')
                Kembali
            </a>

            <p class="min-w-0 flex-1 truncate font-semibold text-neutral">{{ $book->title }}</p>

            <div class="flex w-full items-center justify-between gap-2 sm:w-auto sm:justify-end">

                {{-- Navigasi halaman --}}
                <div class="join">
                    <button id="pdf-prev" type="button" class="btn btn-sm join-item" aria-label="Halaman sebelumnya" disabled>
                        @svg('heroicon-o-chevron-left', 'size-4')
                    </button>

                    <form id="pdf-jump" class="join-item flex items-center gap-1.5 border-y border-base-300 bg-base-100 px-2 text-sm">
                        <input id="pdf-page-input" type="number" min="1" value="1" inputmode="numeric"
                               aria-label="Lompat ke halaman"
                               class="w-12 bg-transparent text-center font-medium outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                        <span class="whitespace-nowrap text-base-content/60">/ <span id="pdf-total">-</span></span>
                    </form>

                    <button id="pdf-next" type="button" class="btn btn-sm join-item" aria-label="Halaman berikutnya" disabled>
                        @svg('heroicon-o-chevron-right', 'size-4')
                    </button>
                </div>

                {{-- Zoom --}}
                <div class="join">
                    <button id="pdf-zoom-out" type="button" class="btn btn-sm join-item" aria-label="Perkecil">
                        @svg('heroicon-o-minus', 'size-4')
                    </button>
                    <button id="pdf-zoom-reset" type="button" class="btn btn-sm join-item min-w-16 tabular-nums"
                            aria-label="Sesuaikan lebar layar">100%</button>
                    <button id="pdf-zoom-in" type="button" class="btn btn-sm join-item" aria-label="Perbesar">
                        @svg('heroicon-o-plus', 'size-4')
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Area baca --}}
    <main id="pdf-stage" class="relative flex flex-1 overflow-auto p-3 sm:p-6" tabindex="0">
        <div id="pdf-loading" class="absolute inset-0 grid place-items-center">
            <div class="text-center">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <p class="mt-3 text-sm text-base-content/60">Memuat buku...</p>
            </div>
        </div>

        <div id="pdf-error" hidden class="m-auto max-w-sm text-center">
            @svg('heroicon-o-exclamation-triangle', 'mx-auto size-12 text-error')
            <h2 class="mt-3 text-lg font-semibold text-neutral">Buku tidak dapat dimuat</h2>
            <p class="mt-1 text-sm text-base-content/70">
                Koneksi terputus atau tautan baca sudah kedaluwarsa. Muat ulang halaman untuk mencoba lagi.
            </p>
            <button type="button" onclick="location.reload()" class="btn btn-primary btn-sm mt-4">Muat ulang</button>
        </div>

        <canvas id="pdf-canvas" hidden class="m-auto bg-white shadow-lg"></canvas>
    </main>
</div>

</body>
</html>