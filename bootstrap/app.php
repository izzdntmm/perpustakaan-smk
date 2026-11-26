<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu yang membuka halaman admin diarahkan ke login
        $middleware->redirectGuestsTo(fn() => route('admin.login'));

        // Admin yang sudah login tidak perlu melihat halaman login lagi
        $middleware->redirectUsersTo(fn() => route('admin.dashboard'));

        $middleware->alias([
            'no.store' => \App\Http\Middleware\NoStoreResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sesi/token form kedaluwarsa (419): kembalikan ke halaman sebelumnya dengan pesan ramah
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 && !$request->expectsJson()) {
                return redirect()->back()
                    ->with('error', 'Sesi halaman sudah habis. Silakan ulangi.');
            }
        });
                // Berkas melebihi batas server: kembali ke form dengan pesan ramah
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            return redirect()->back()
                ->with('error', 'Ukuran berkas melebihi batas server. Gunakan sampul maksimal 2 MB dan PDF maksimal 30 MB.');
        });
    })->create();
