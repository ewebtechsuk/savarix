<?php
/**
 * Configure Composer's GitHub token from environment or .env file.
 */

$token = getenv('GITHUB_TOKEN');

if (!$token) {
    $envPath = __DIR__.'/../.env';
    if (!is_file($envPath)) {
        $envExample = __DIR__.'/../.env.example';
        if (is_file($envExample)) {
            $envPath = $envExample;
        } else {
            $envPath = null;
        }
    }

    if ($envPath && is_readable($envPath)) {
        $contents = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($contents as $line) {
            if (strpos(trim($line), 'GITHUB_TOKEN=') === 0) {
                $value = trim(substr($line, strlen('GITHUB_TOKEN=')));
                if ($value !== '') {
                    $token = trim($value, "\"' ");
                }
                break;
            }
        }
    }
}

if ($token) {
    $auth = json_encode(['github-oauth' => ['github.com' => $token]], JSON_UNESCAPED_SLASHES);
    if ($auth === false) {
        fwrite(STDERR, "[composer-token] Failed to encode COMPOSER_AUTH payload.\n");
        return;
    }

    putenv('COMPOSER_AUTH='.$auth);
    $_SERVER['COMPOSER_AUTH'] = $auth;
    $_ENV['COMPOSER_AUTH'] = $auth;

    fwrite(STDOUT, "[composer-token] Configured GitHub token from environment.\n");
} else {
    fwrite(STDERR, "[composer-token] Warning: GITHUB_TOKEN is not set. Composer may fail when downloading from GitHub.\n");
}
