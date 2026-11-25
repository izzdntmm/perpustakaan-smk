@props(['title' => null])

<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="flex min-h-screen flex-col bg-base-200 text-base-content">

    <x-public.navbar />

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6">
        <x-alert />
        {{ $slot }}
    </main>

    <x-public.footer />

    @stack('scripts')
</body>
</html>