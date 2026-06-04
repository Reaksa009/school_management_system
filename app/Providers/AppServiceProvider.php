<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $viewCompiledPath = env('VIEW_COMPILED_PATH');

        if (! $viewCompiledPath && ! str_starts_with(__DIR__, '/var/task')) {
            return;
        }

        $basePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'laravel';
        $paths = [
            'cache' => $basePath.DIRECTORY_SEPARATOR.'cache',
            'logs' => $basePath.DIRECTORY_SEPARATOR.'logs',
            'sessions' => $basePath.DIRECTORY_SEPARATOR.'sessions',
            'views' => $viewCompiledPath ?: $basePath.DIRECTORY_SEPARATOR.'views',
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        config([
            'cache.stores.file.path' => $paths['cache'],
            'cache.stores.file.lock_path' => $paths['cache'],
            'logging.channels.daily.path' => $paths['logs'].DIRECTORY_SEPARATOR.'laravel.log',
            'logging.channels.emergency.path' => $paths['logs'].DIRECTORY_SEPARATOR.'laravel.log',
            'logging.channels.single.path' => $paths['logs'].DIRECTORY_SEPARATOR.'laravel.log',
            'session.files' => $paths['sessions'],
            'view.compiled' => $paths['views'],
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
