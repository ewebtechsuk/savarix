<?php
$artisan = __DIR__ . '/../artisan';
if (!is_file($artisan)) {
    fwrite(STDERR, "[offline] Artisan entrypoint not found; skipping optimize.\n");
    return 0;
}

$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisan) . ' optimize';
exec($command, $output, $status);

if ($status !== 0) {
    fwrite(STDERR, "[offline] php artisan optimize skipped (command unavailable).\n");
}

return 0;
