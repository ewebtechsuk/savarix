#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\TextUI\Application;

$application = new Application();

exit($application->run($_SERVER['argv'] ?? []));
