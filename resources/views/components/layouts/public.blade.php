@props(['title' => null])

<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="flex min-h-screen flex-col bg-base-100 text-base-content antialiased">

    <a href="#konten"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-content">
        Lewati ke konten
    </a>

    <x-public.navbar />

    <main id="konten" tabindex="-1" class="flex-1 outline-none">
        <x-container class="pt-4">
            <x-alert />
        </x-container>

        {{ $slot }}
    </main>

    <x-public.footer />

    @stack('scripts')
</body>
</html>