<?php

// This file is plain `require`d (not require_once) from a few different places
// so it always hands back the config array, so the function definitions need
// to be guarded against running twice in the same request.
if (!function_exists('loadEnv')) {
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
            if ($key !== '') {
                $_ENV[$key] = $value;
            }
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? $default;
    }
}

loadEnv(__DIR__ . '/../.env');

return [
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'name' => env('DB_NAME', 'resume_analyzer'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'ai' => [
        'api_key' => env('GROQ_API_KEY', ''),
        'api_url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-120b'),
    ],
    'app' => [
        'url' => env('APP_URL', 'http://localhost/Ai-job-analyzer/public'),
        'upload_max_mb' => (int) env('UPLOAD_MAX_MB', '5'),
    ],
];
