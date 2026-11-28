<!DOCTYPE html>
<html lang="id" data-theme="perpus">
<head>
    @include('partials.head', ['title' => 'Login Admin', 'noindex' => true])
</head>
<body class="min-h-dvh bg-base-200 text-base-content antialiased">

<main class="flex min-h-dvh items-center justify-center p-4">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="mb-6 flex items-center justify-center gap-3">
            @svg('heroicon-o-book-open', 'size-9 shrink-0 text-primary')
            <span class="leading-tight">
                <span class="block font-heading text-lg font-bold text-neutral">Perpustakaan</span>
                <span class="block text-sm text-base-content/70">SMK Roudlotul Mubtadiin</span>
            </span>
        </a>

        <div class="rounded-3xl border border-base-300 bg-base-100 p-6 sm:p-10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Area Pustakawan</p>
            <h1 class="mt-2 text-3xl font-semibold text-neutral sm:text-4xl">Selamat datang.</h1>
            <p class="mt-2 text-base-content/70">Masuk untuk mengelola koleksi dan sirkulasi buku.</p>

            @if (session('error'))
                <div role="alert" class="alert alert-error mt-6">
                    @svg('heroicon-o-x-circle', 'size-5 shrink-0')
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="mt-8 space-y-5">
                @csrf

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Email</legend>
                    <label class="input w-full @error('email') input-error @enderror">
                        @svg('heroicon-o-envelope', 'size-5 opacity-60')
                        <input type="email" name="email" value="{{ old('email') }}"
                               required autofocus maxlength="255"
                               autocomplete="username" inputmode="email"
                               placeholder="admin@perpustakaan.sch.id" class="grow">
                    </label>
                    @error('email')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Password</legend>
                    <label class="input w-full @error('password') input-error @enderror">
                        @svg('heroicon-o-lock-closed', 'size-5 opacity-60')
                        <input id="password" type="password" name="password"
                               required maxlength="255" autocomplete="current-password"
                               placeholder="Masukkan password" class="grow">
                        <button type="button" id="toggle-password" class="text-base-content/70 hover:text-primary"
                                aria-label="Tampilkan password">
                            <span data-show>@svg('heroicon-o-eye', 'size-5')</span>
                            <span data-hide hidden>@svg('heroicon-o-eye-slash', 'size-5')</span>
                        </button>
                    </label>
                    @error('password')
                        <p class="label text-error">{{ $message }}</p>
                    @enderror
                </fieldset>

                <button type="submit" class="btn btn-primary btn-lg w-full">
                    Masuk dashboard
                    @svg('heroicon-o-arrow-right', 'size-5')
                </button>
            </form>
        </div>

        <a href="{{ route('home') }}"
           class="mt-6 flex items-center justify-center gap-2 text-sm font-medium text-base-content/70 hover:text-primary">
            @svg('heroicon-o-arrow-left', 'size-4')
            Kembali ke website
        </a>
    </div>
</main>

<script>
    const pw = document.getElementById('password');
    document.getElementById('toggle-password').addEventListener('click', (e) => {
        const show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        e.currentTarget.querySelector('[data-show]').hidden = show;
        e.currentTarget.querySelector('[data-hide]').hidden = !show;
        e.currentTarget.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
</script>

</body>
</html>