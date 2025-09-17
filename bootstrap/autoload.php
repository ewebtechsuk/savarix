<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

$vendorDirectory = __DIR__.'/../vendor';
$vendorAutoload = $vendorDirectory.'/autoload.php';
$cachedVendorDirectory = __DIR__.'/../deps/vendor';

if (!function_exists('laravelCopyDirectory')) {
    function laravelCopyDirectory($source, $destination)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination.DIRECTORY_SEPARATOR.$iterator->getSubPathName();

            if ($item->isDir()) {
                if (!is_dir($targetPath) && !@mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
                    throw new RuntimeException('Unable to create directory: '.$targetPath);
                }
            } else {
                if (!@copy($item->getPathname(), $targetPath)) {
                    throw new RuntimeException('Unable to copy file: '.$targetPath);
                }
            }
        }
    }
}

if (!file_exists($vendorAutoload) && is_dir($cachedVendorDirectory)) {
    if (is_link($vendorDirectory)) {
        @unlink($vendorDirectory);
    }

    if (!is_dir($vendorDirectory) && !@mkdir($vendorDirectory, 0775, true) && !is_dir($vendorDirectory)) {
        throw new RuntimeException('Unable to create vendor directory.');
    }

    laravelCopyDirectory($cachedVendorDirectory, $vendorDirectory);
}

if (file_exists($vendorAutoload)) {
    require $vendorAutoload;
} else {
    fwrite(STDERR, "Composer dependencies are missing. Run 'composer install' or ensure deps/vendor is available.\n");
    exit(1);
}

/*
|--------------------------------------------------------------------------
| Include The Compiled Class File
|--------------------------------------------------------------------------
|
| To dramatically increase your application's performance, you may use a
| compiled class file which contains all of the classes commonly used
| by a request. The Artisan "optimize" is used to create this file.
|
*/

$compiledPath = __DIR__.'/cache/compiled.php';

if (file_exists($compiledPath)) {
    require $compiledPath;
}
