<?php

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

return [
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'name' => env('DB_NAME', 'resume_analyzer'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'grok' => [
        'api_key' => env('GROK_API_KEY', ''),
        'api_url' => env('GROK_API_URL', 'https://api.x.ai/v1/chat/completions'),
        'model' => env('GROK_MODEL', 'grok-4.7'),
    ],
    'app' => [
        'url' => env('APP_URL', 'http://localhost/resume-analyzer-ai/public'),
        'upload_max_mb' => (int) env('UPLOAD_MAX_MB', '5'),
    ],
];
