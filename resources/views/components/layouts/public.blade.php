@props(['title' => null])

<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="flex min-h-screen flex-col bg-base-100 text-base-content antialiased">

    <x-public.navbar />

    <main class="flex-1">
        <x-container class="pt-4">
            <x-alert />
        </x-container>

        {{ $slot }}
    </main>

    <x-public.footer />

    @stack('scripts')
</body>
</html>