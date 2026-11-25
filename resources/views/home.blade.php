@php
    $meta = [
        'pelajaran-umum' => ['Pelajaran Umum', 'Materi pembelajaran dasar dan pengetahuan umum.', 'book-open', 'text-primary'],
        'produktif-smk' => ['Produktif SMK', 'Referensi keahlian dan materi vokasi.', 'academic-cap', 'text-secondary'],
        'fiksi' => ['Fiksi & Cerita', 'Novel, cerita inspiratif, dan bacaan santai.', 'heart', 'text-accent'],
        'referensi' => ['Referensi', 'Kamus, ensiklopedia, dan sumber rujukan.', 'bookmark', 'text-violet-600'],
    ];

    $steps = [
        ['01', 'Temukan buku', 'Cari buku melalui katalog dan cek ketersediaannya.'],
        ['02', 'Isi formulir', 'Masukkan nama dan kelas atau jabatan secara singkat.'],
        ['03', 'Ambil di pustakawan', 'Datang ke perpustakaan untuk konfirmasi dan mengambil buku.'],
    ];

    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

<x-layouts.public title="Beranda">

    {{-- ===== HERO ===== --}}
    <section>
        <x-container class="grid items-center gap-12 py-12 lg:grid-cols-2 lg:py-20">
            <div>
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                    @svg('heroicon-o-book-open', 'size-5')
                    Ruang Literasi Sekolah
                </p>

                <h1 class="mt-5 text-5xl font-medium leading-[1.05] text-neutral sm:text-6xl">
                    Temukan ilmu.<br>Jelajahi dunia.
                </h1>

                <p class="mt-6 max-w-lg text-lg leading-relaxed text-base-content/80">
                    Perpustakaan SMK Roudlotul Mubtadiin. Temukan buku pelajaran, referensi kejuruan, dan bacaan favoritmu dalam satu tempat.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('catalog') }}" class="btn btn-primary btn-lg">Jelajahi Katalog</a>
                    <a href="#cara" class="btn btn-lg border-base-300 bg-base-100 text-neutral">Cara Meminjam</a>
                </div>

                <dl class="mt-10 flex gap-8 sm:gap-10">
                    <div>
                        <dd class="font-heading text-2xl font-bold text-primary">{{ $fmt($stats['books']) }}</dd>
                        <dt class="text-sm text-base-content/70">Koleksi buku</dt>
                    </div>
                    <div>
                        <dd class="font-heading text-2xl font-bold text-primary">{{ $fmt($stats['digital']) }}</dd>
                        <dt class="text-sm text-base-content/70">Buku digital</dt>
                    </div>
                    <div>
                        <dd class="font-heading text-2xl font-bold text-primary">{{ $fmt($stats['categories']) }}</dd>
                        <dt class="text-sm text-base-content/70">Kategori utama</dt>
                    </div>
                </dl>
            </div>

            {{-- Ilustrasi --}}
            <div class="relative mx-auto aspect-square w-full max-w-md" aria-hidden="true">
                <div class="absolute inset-[6%] rounded-full border border-primary/15"></div>
                <div class="absolute inset-[16%] rounded-full border border-primary/15"></div>

                <div class="absolute left-[24%] top-[12%] flex aspect-[3/4] w-[52%] rotate-[6deg] flex-col justify-between rounded-lg border border-base-300 bg-base-100 p-4 shadow-2xl shadow-neutral/10 sm:p-5">
                    <span class="text-[9px] font-semibold uppercase tracking-[0.2em] text-base-content/40 sm:text-[10px]">Knowledge</span>
                    <span class="font-heading text-xl font-extrabold leading-tight text-base-content/35 sm:text-3xl">THE<br>WORLD OF<br>IDEAS</span>
                    <span class="text-[8px] uppercase tracking-[0.2em] text-base-content/40 sm:text-[9px]">Read &middot; Learn &middot; Grow</span>
                </div>

                <div class="absolute right-0 top-[14%] rounded-xl border border-base-300 bg-base-100 px-4 py-3 shadow-lg">
                    <p class="text-sm font-semibold text-neutral">Literasi tanpa batas</p>
                    <p class="text-xs text-base-content/60">Mulai dari satu halaman</p>
                </div>

                <div class="absolute bottom-[16%] left-0 flex items-center gap-3 rounded-xl border border-base-300 bg-base-100 px-4 py-3 shadow-lg">
                    @svg('heroicon-o-academic-cap', 'size-6 text-primary')
                    <div>
                        <p class="text-sm font-semibold text-neutral">Belajar lebih mudah</p>
                        <p class="text-xs text-base-content/60">Untuk semua siswa</p>
                    </div>
                </div>
            </div>
        </x-container>
    </section>

    {{-- ===== KATEGORI ===== --}}
    <section class="py-12 lg:py-16">
        <x-container>
            <x-section-heading
                eyebrow="Pilih koleksi"
                title="Jelajahi perpustakaan"
                subtitle="Temukan koleksi sesuai kebutuhan belajarmu."
                :url="route('catalog')" />

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $c)
                    @php [$title, $desc, $icon, $color] = $meta[$c->slug] ?? [$c->name, '', 'book-open', 'text-primary']; @endphp
                    <a href="{{ route('catalog', ['category' => $c->slug]) }}"
                       class="group flex flex-col rounded-box border border-base-300 bg-base-100 p-6 transition hover:border-primary/40 hover:shadow-md">
                        @svg('heroicon-o-'.$icon, 'size-8 '.$color)
                        <h3 class="mt-6 text-xl font-semibold text-neutral">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-base-content/70">{{ $desc }}</p>
                        <div class="mt-5 flex items-center justify-between text-sm text-base-content/80">
                            <span>{{ $c->books_count }} koleksi</span>
                            @svg('heroicon-o-arrow-up-right', 'size-4 text-primary transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5')
                        </div>
                    </a>
                @endforeach
            </div>
        </x-container>
    </section>

    {{-- ===== BUKU REKOMENDASI ===== --}}
    @if ($featured->isNotEmpty())
        <section class="pb-12 lg:pb-16">
            <x-container>
                <x-section-heading
                    eyebrow="Pilihan pustakawan"
                    title="Buku rekomendasi"
                    subtitle="Beberapa bacaan yang bisa kamu mulai hari ini."
                    :url="route('catalog')"
                    urlLabel="Lihat katalog" />

                <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
                    @foreach ($featured as $book)
                        <x-book.card :book="$book" />
                    @endforeach
                </div>
            </x-container>
        </section>
    @endif

    {{-- ===== E-LIBRARY ===== --}}
    <section class="bg-base-200 py-12 lg:py-20">
        <x-container class="grid items-center gap-12 lg:grid-cols-2">

            <div class="relative mx-auto w-full max-w-sm pb-6" aria-hidden="true">
                <div class="-rotate-6 rounded-2xl border border-base-300 bg-base-100 p-8 shadow-xl">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-base-content/50">Chapter 01</p>
                    <p class="mt-6 font-heading text-3xl font-medium leading-tight text-neutral">Knowledge<br>is a journey.</p>
                    <div class="h-24"></div>
                </div>
                <div class="absolute bottom-0 right-0 flex items-center gap-3 rounded-xl border border-base-300 bg-base-100 px-4 py-3 shadow-lg">
                    @svg('heroicon-o-device-tablet', 'size-6 text-primary')
                    <div>
                        <p class="text-sm font-semibold text-neutral">Digital Reader</p>
                        <p class="text-xs text-base-content/60">Baca langsung di browser</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">E-Library</p>
                <h2 class="mt-2 text-3xl font-semibold text-neutral sm:text-5xl">Baca buku digital<br>di mana saja.</h2>
                <p class="mt-4 max-w-md text-base-content/80">
                    Tak perlu membawa buku tebal. Buka koleksi digital dan baca langsung melalui browser di HP maupun komputer.
                </p>

                <ul class="mt-6 space-y-3">
                    @foreach (['Akses praktis dari perangkatmu', 'Tampilan nyaman untuk membaca', 'Navigasi halaman mudah'] as $point)
                        <li class="flex items-center gap-3 text-neutral">
                            @svg('heroicon-o-check-circle', 'size-6 shrink-0 text-accent')
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('catalog', ['type' => 'digital']) }}" class="btn btn-primary btn-lg mt-8">Buka Buku Digital</a>
            </div>
        </x-container>
    </section>

    {{-- ===== CARA MEMINJAM ===== --}}
    <section id="cara" class="scroll-mt-24 py-12 lg:py-20">
        <x-container>
            <x-section-heading
                eyebrow="Mudah & praktis"
                title="Bagaimana cara meminjam?"
                subtitle="Hanya beberapa langkah untuk meminjam koleksi buku fisik." />

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ($steps as [$no, $title, $desc])
                    <div class="rounded-box border border-base-300 bg-base-100 p-6">
                        <span class="font-heading text-4xl font-bold text-primary/25">{{ $no }}</span>
                        <h3 class="mt-4 text-xl font-semibold text-neutral">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-base-content/70">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex flex-col items-start justify-between gap-6 rounded-3xl bg-neutral p-8 text-neutral-content sm:flex-row sm:items-center sm:p-10">
                <div>
                    <h2 class="text-2xl font-semibold text-neutral-content sm:text-3xl">Siap menemukan bacaan berikutnya?</h2>
                    <p class="mt-2 opacity-80">Mulai jelajahi koleksi perpustakaan sekarang.</p>
                </div>
                <a href="{{ route('catalog') }}" class="btn btn-lg shrink-0 border-0 bg-base-100 text-neutral hover:bg-base-200">Cari buku</a>
            </div>
        </x-container>
    </section>

    {{-- ===== TENTANG ===== --}}
    <section id="tentang" class="scroll-mt-24 border-t border-base-300 py-12 lg:py-20">
        <x-container>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Tentang perpustakaan</p>
            <h2 class="mt-2 max-w-2xl text-3xl font-semibold text-neutral sm:text-5xl">Tempat tumbuhnya rasa ingin tahu.</h2>
            <p class="mt-5 max-w-3xl text-lg leading-relaxed text-base-content/80">
                Perpustakaan SMK Roudlotul Mubtadiin hadir untuk mendukung kegiatan belajar, memperluas wawasan, dan menumbuhkan budaya literasi di lingkungan sekolah melalui koleksi buku fisik dan digital.
            </p>
        </x-container>
    </section>

</x-layouts.public>