<?php

function set_default_env(string $key, string $value): void
{
    if (getenv($key) !== false || isset($_ENV[$key]) || isset($_SERVER[$key])) {
        return;
    }

    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__).'/public';

$runtimePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'laravel';
$cachePath = $runtimePath.DIRECTORY_SEPARATOR.'bootstrap-cache';

foreach ([$runtimePath, $cachePath] as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

set_default_env('APP_SERVICES_CACHE', $cachePath.DIRECTORY_SEPARATOR.'services.php');
set_default_env('APP_PACKAGES_CACHE', $cachePath.DIRECTORY_SEPARATOR.'packages.php');
set_default_env('APP_CONFIG_CACHE', $cachePath.DIRECTORY_SEPARATOR.'config.php');
set_default_env('APP_ROUTES_CACHE', $cachePath.DIRECTORY_SEPARATOR.'routes.php');
set_default_env('APP_EVENTS_CACHE', $cachePath.DIRECTORY_SEPARATOR.'events.php');

require dirname(__DIR__).'/public/index.php';
