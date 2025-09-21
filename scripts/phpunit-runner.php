#!/usr/bin/env php
<?php
require __DIR__ . '/../framework/PHPUnit/Framework/AssertionFailedError.php';
require __DIR__ . '/../framework/PHPUnit/Framework/TestCase.php';
require __DIR__ . '/../framework/PHPUnit/TextUI/TestRunner.php';
require __DIR__ . '/../bootstrap/autoload.php';

use PHPUnit\TextUI\TestRunner;

$runner = new TestRunner();
$argv = $_SERVER['argv'] ?? [];
array_shift($argv); // remove script name

$defaultTestsPath = is_dir(__DIR__ . '/../tests') ? __DIR__ . '/../tests' : __DIR__ . '/../../tests';
$testsPath = $defaultTestsPath;
$filter = null;

while ($argv !== []) {
    $argument = array_shift($argv);

    if ($argument === '--filter') {
        $filter = array_shift($argv) ?? '';
        continue;
    }

    if (str_starts_with($argument, '--filter=')) {
        $filter = substr($argument, strlen('--filter='));
        continue;
    }

    if ($argument === '-h' || $argument === '--help') {
        echo "Usage: phpunit [--filter pattern] [tests-path]\n";
        exit(0);
    }

    if (str_starts_with($argument, '-')) {
        fwrite(STDERR, "Unsupported option: {$argument}\n");
        exit(1);
    }

    if ($testsPath !== $defaultTestsPath) {
        fwrite(STDERR, "Multiple test paths provided.\n");
        exit(1);
    }

    $testsPath = $argument;
}

if (! is_dir($testsPath)) {
    $resolved = realpath($testsPath);
    if ($resolved === false || ! is_dir($resolved)) {
        fwrite(STDERR, "Test directory not found: {$testsPath}\n");
        exit(1);
    }
    $testsPath = $resolved;
}

exit($runner->run($testsPath, $filter));
