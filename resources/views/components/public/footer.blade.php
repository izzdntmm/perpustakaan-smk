<footer class="bg-neutral text-neutral-content">
    <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div class="flex items-center gap-2">
            @svg('heroicon-o-building-library', 'size-5')
            <span>Perpustakaan SMK Roudlotul Mubtadiin</span>
        </div>

        <div class="flex items-center gap-4">
            <span class="opacity-70">&copy; {{ now()->year }}</span>

            @if (Route::has('admin.login'))
                <a href="{{ route('admin.login') }}" class="link link-hover inline-flex items-center gap-1">
                    @svg('heroicon-o-lock-closed', 'size-4')
                    Login Pustakawan
                </a>
            @endif
        </div>
    </div>
</footer>