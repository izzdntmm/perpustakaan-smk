@php
    $inAdmin = request()->is('admin*');
    $retryAfter = isset($exception) ? ($exception->getHeaders()['Retry-After'] ?? null) : null;
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#2d6ba7">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-dvh bg-base-200 text-base-content antialiased">

<main class="flex min-h-dvh flex-col items-center justify-center p-4">

    <a href="{{ url('/') }}" class="mb-6 flex items-center gap-3">
        @svg('heroicon-o-book-open', 'size-8 shrink-0 text-primary')
        <span class="leading-tight">
            <span class="block font-heading text-base font-bold text-neutral">Perpustakaan</span>
            <span class="block text-xs text-base-content/70">SMK Roudlotul Mubtadiin</span>
        </span>
    </a>

    <div class="w-full max-w-lg rounded-3xl border border-base-300 bg-base-100 p-8 text-center sm:p-12">
        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-primary/10 text-primary">
            @svg('heroicon-o-'.$icon, 'size-8')
        </span>

        <p class="mt-6 font-heading text-6xl font-bold text-primary/25">{{ $code }}</p>
        <h1 class="mt-2 text-2xl font-semibold text-neutral sm:text-3xl">{{ $title }}</h1>
        <p class="mt-3 text-base-content/70">{{ $message }}</p>

        @if ($retryAfter)
            <p class="mt-2 text-sm text-base-content/70">Silakan coba lagi dalam {{ $retryAfter }} detik.</p>
        @endif

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ $inAdmin ? url('/admin/dashboard') : url('/') }}" class="btn btn-primary">
                @svg('heroicon-o-home', 'size-5')
                {{ $inAdmin ? 'Ke panel admin' : 'Kembali ke beranda' }}
            </a>

            @if ($code === 404 && ! $inAdmin)
                <a href="{{ url('/katalog') }}" class="btn">
                    @svg('heroicon-o-magnifying-glass', 'size-5')
                    Cari di katalog
                </a>
            @endif

            @if ($code >= 500 || $code === 429)
                <button type="button" class="btn" onclick="location.reload()">
                    @svg('heroicon-o-arrow-path', 'size-5')
                    Coba lagi
                </button>
            @endif
        </div>
    </div>
</main>

</body>
</html>