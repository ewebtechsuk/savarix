#!/usr/bin/env php
<?php
require __DIR__ . '/../framework/PHPUnit/Framework/AssertionFailedError.php';
require __DIR__ . '/../framework/PHPUnit/Framework/TestCase.php';
require __DIR__ . '/../framework/PHPUnit/TextUI/TestRunner.php';
require __DIR__ . '/../bootstrap/autoload.php';

use PHPUnit\TextUI\TestRunner;

$runner = new TestRunner();
$defaultTestsPath = is_dir(__DIR__ . '/../tests') ? __DIR__ . '/../tests' : __DIR__ . '/../../tests';
$testsPath = $argv[1] ?? $defaultTestsPath;
exit($runner->run($testsPath));
