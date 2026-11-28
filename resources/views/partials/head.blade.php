<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#2d6ba7">
<meta name="description" content="{{ $description ?? 'Katalog Perpustakaan SMK Roudlotul Mubtadiin: cari buku, baca e-book, dan ajukan peminjaman buku fisik.' }}">

@if (! empty($noindex))
    <meta name="robots" content="noindex, nofollow">
@endif

<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

<title>{{ $title ? $title.' - ' : '' }}{{ config('app.name') }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('styles')