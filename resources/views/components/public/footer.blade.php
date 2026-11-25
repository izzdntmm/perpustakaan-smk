<footer class="border-t border-base-300 bg-base-100">
    <x-container class="flex flex-col gap-4 py-8 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            @svg('heroicon-o-book-open', 'size-7 shrink-0 text-primary')
            <span class="leading-tight">
                <span class="block font-heading text-sm font-bold text-neutral">Perpustakaan</span>
                <span class="block text-xs text-base-content/70">SMK Roudlotul Mubtadiin</span>
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-base-content/70">
            <span>&copy; {{ now()->year }} Perpustakaan SMK Roudlotul Mubtadiin</span>
            <a href="{{ Route::has('admin.login') ? route('admin.login') : '#' }}" class="hover:text-primary">Administrator</a>
        </div>
    </x-container>
</footer>