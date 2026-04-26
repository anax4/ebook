<?php

namespace App\Core;

function base_path(string $path = ''): string
{
    $basePath = dirname(__DIR__, 2);

    if ($path === '') {
        return $basePath;
    }

    return $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function load_env(string $path): void
{
    static $loaded = [];

    if (isset($loaded[$path]) || !is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);
        $name = trim($parts[0] ?? '');
        $value = trim($parts[1] ?? '');

        if ($name === '') {
            continue;
        }

        $value = trim($value, "\"'");

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }

        if (!array_key_exists($name, $_SERVER)) {
            $_SERVER[$name] = $value;
        }

        putenv(sprintf('%s=%s', $name, $value));
    }

    $loaded[$path] = true;
}

function env(string $key, ?string $default = null): ?string
{
    load_env(base_path('.env'));

    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return $value;
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function back()
{
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}
