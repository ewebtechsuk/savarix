#!/usr/bin/env php
<?php

$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!is_file($autoloadPath)) {
    fwrite(
        STDERR,
        "Composer autoload file not found. Please run `composer install` before executing the test suite." . PHP_EOL
    );
    exit(1);
}

require $autoloadPath;

$polyfills = __DIR__ . '/../tests/phpunit_polyfills.php';

if (is_file($polyfills)) {
    require_once $polyfills;
}

if (class_exists(\PHPUnit\TextUI\Application::class)) {
    $application = new PHPUnit\TextUI\Application();

    exit($application->run($_SERVER['argv'] ?? []));
}

if (class_exists(\PHPUnit_TextUI_Command::class)) {
    $command = new \PHPUnit_TextUI_Command();

    return $command->run($_SERVER['argv'] ?? [], true);
}

fwrite(STDERR, "Unable to locate a compatible PHPUnit entry point.\n");

exit(1);
