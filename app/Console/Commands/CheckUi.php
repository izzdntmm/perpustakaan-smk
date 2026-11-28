<?php

namespace App\Console\Commands;

use BladeUI\Icons\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class CheckUi extends Command
{
    protected $signature = 'app:check-ui';

    protected $description = 'Periksa aturan UI: tanpa emoji dan semua ikon yang dipakai tersedia';

    public function handle(): int
    {
        $files = collect([resource_path('views'), app_path(), database_path('seeders')])
            ->flatMap(fn (string $dir) => File::allFiles($dir))
            ->filter(fn ($file) => $file->getExtension() === 'php');

        $icons = app(Factory::class);
        $problems = [];

        foreach ($files as $file) {
            $content = File::get($file->getPathname());
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());

            // 1. Emoji
            if (preg_match_all('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}]/u', $content, $emoji)) {
                $problems[] = [$relative, 'Emoji ditemukan: '.implode(' ', array_unique($emoji[0]))];
            }

            // 2. Nama ikon yang ditulis langsung (x-heroicon-o-..., @svg('heroicon-o-...'), icon="...")
            preg_match_all('/heroicon-[os]-[a-z0-9]+(?:-[a-z0-9]+)*/', $content, $named);
            preg_match_all('/\bicon="([a-z0-9]+(?:-[a-z0-9]+)*)"/', $content, $props);

            $names = collect($named[0])
                ->merge(collect($props[1])->map(fn (string $name) => 'heroicon-o-'.$name))
                ->unique();

            foreach ($names as $name) {
                try {
                    $icons->svg($name);
                } catch (Throwable) {
                    $problems[] = [$relative, "Ikon tidak ditemukan: {$name}"];
                }
            }
        }

        if ($problems === []) {
            $this->info('Semua aman: tidak ada emoji dan semua ikon tersedia ('.$files->count().' berkas diperiksa).');

            return self::SUCCESS;
        }

        $this->table(['Berkas', 'Masalah'], $problems);
        $this->error(count($problems).' masalah ditemukan.');

        return self::FAILURE;
    }
}