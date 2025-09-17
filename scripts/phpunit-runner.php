#!/usr/bin/env php
<?php
$possible = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../autoload.php',
];
foreach ($possible as $autoload) {
    if (file_exists($autoload)) {
        require $autoload;
        break;
    }
}

use PHPUnit\TextUI\TestRunner;

$runner = new TestRunner();
$defaultTestsPath = is_dir(__DIR__ . '/../tests') ? __DIR__ . '/../tests' : __DIR__ . '/../../tests';
$testsPath = $argv[1] ?? $defaultTestsPath;
exit($runner->run($testsPath));
