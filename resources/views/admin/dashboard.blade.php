<x-layouts.admin title="Dashboard">

    <section>
        <p class="text-xs font-medium uppercase tracking-[0.18em] text-base-content/60">Selamat datang</p>
        <h2 class="mt-2 text-3xl font-semibold text-neutral sm:text-4xl">Halo, {{ auth()->user()->name }}</h2>
        <p class="mt-2 text-base-content/70">Berikut ringkasan aktivitas perpustakaan hari ini.</p>
    </section>

    <x-empty-state
        class="mt-8"
        icon="chart-bar"
        title="Statistik segera hadir"
        message="Kartu ringkasan dan daftar pengajuan terbaru dibuat di Part 13." />

</x-layouts.admin>