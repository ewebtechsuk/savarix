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

$projectRoot = realpath(__DIR__.'/..');
$vendorDirectory = $projectRoot.'/vendor';
$vendorAutoload = $vendorDirectory.'/autoload.php';
$cachedVendorDirectory = $projectRoot.'/deps/vendor';
$cachedAutoload = $cachedVendorDirectory.'/autoload.php';
$composerAlreadyLoaded = class_exists(\Composer\Autoload\ClassLoader::class, false);

// Provide compatibility polyfills for legacy vendor dependencies that may rely
// on functions removed from modern PHP runtimes (e.g. each()). This allows the
// cached vendor tree to continue working in environments where Composer cannot
// install fresh dependencies, such as the CI sandbox used for kata exercises.
$polyfills = __DIR__.'/polyfills.php';


if (file_exists($polyfills)) {
    require $polyfills;
}

if (!file_exists($vendorAutoload) && file_exists($cachedAutoload)) {
    $vendorAutoload = $cachedAutoload;
}

if (file_exists($vendorAutoload)) {
    if (! $composerAlreadyLoaded) {
        require_once $vendorAutoload;
    }
} else {
    fwrite(STDERR, "Composer dependencies are missing. Run 'composer install' or ensure deps/vendor is available.\n");
    exit(1);
}

if (class_exists(\Composer\Autoload\ClassLoader::class, false)) {
    foreach (\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $loader) {
        if (! $loader instanceof \Composer\Autoload\ClassLoader) {
            continue;
        }

        $loader->setPsr4('App\\', [$projectRoot.'/app']);
        $loader->setPsr4('Tests\\', [$projectRoot.'/tests']);
        $loader->setPsr4('Database\\Seeders\\', [$projectRoot.'/database/seeders']);
        $loader->addClassMap([
            'Tests\\TestCase' => $projectRoot.'/tests/TestCase.php',
        ]);
    }
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
    require_once $compiledPath;
}
