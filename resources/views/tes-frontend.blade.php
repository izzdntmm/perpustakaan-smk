<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tes Frontend - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 text-base-content">

    <div class="navbar bg-primary text-primary-content shadow-sm">
        <div class="flex-1 px-2">
            <x-heroicon-o-book-open class="size-6 mr-2" />
            <span class="font-semibold">Perpustakaan SMK Roudlotul Mubtadiin</span>
        </div>
    </div>

    <main class="mx-auto max-w-5xl p-4 sm:p-6">
        <h1 class="mb-6 text-2xl font-bold">Tes Frontend</h1>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Kartu Buku</h2>
                    <p>Komponen card dari DaisyUI.</p>
                    <div class="flex gap-2">
                        <span class="badge badge-primary gap-1">
                            <x-heroicon-s-device-tablet class="size-4" /> Digital
                        </span>
                        <span class="badge badge-secondary gap-1">
                            <x-heroicon-s-book-open class="size-4" /> Fisik
                        </span>
                    </div>
                    <div class="card-actions justify-end">
                        <button class="btn btn-primary">
                            <x-heroicon-o-eye class="size-5" /> Baca Sekarang
                        </button>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Input & Tombol</h2>
                    <label class="input w-full">
                        <x-heroicon-o-magnifying-glass class="size-5 opacity-60" />
                        <input type="search" placeholder="Cari judul atau penulis" />
                    </label>
                    <div class="card-actions">
                        <button class="btn btn-outline btn-primary">Pinjam Buku</button>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Status</h2>
                    <div role="alert" class="alert alert-success">
                        <x-heroicon-o-check-circle class="size-5" />
                        <span>Stok tersedia</span>
                    </div>
                    <div role="alert" class="alert alert-error">
                        <x-heroicon-o-exclamation-triangle class="size-5" />
                        <span>Terlambat</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>