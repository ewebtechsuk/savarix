<?php

namespace PHPUnit\TextUI;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TestRunner
{
    public function run(string $testsPath, ?string $filter = null): int
    {
        $results = [];

        foreach ($this->testFiles($testsPath) as $file) {
            require_once $file;
        }

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, TestCase::class) && !$this->isAbstract($class)) {
                $instance = new $class();
                $results = array_merge($results, $instance->run($filter));
            }
        }

        $failures = array_filter($results, fn ($result) => $result['status'] !== 'passed');

        foreach ($results as $result) {
            echo sprintf("%s ... %s
", $result['test'], strtoupper($result['status']));
            if ($result['message']) {
                echo '  ' . $result['message'] . "
";
            }
        }

        echo sprintf("
Tests: %d, Failures: %d
", count($results), count($failures));

        return $failures ? 1 : 0;
    }

    private function isAbstract(string $class): bool
    {
        $reflection = new ReflectionClass($class);
        return $reflection->isAbstract();
    }

    /**
     * @return iterable<string>
     */
    private function testFiles(string $testsPath): iterable
    {
        $directory = new \RecursiveDirectoryIterator($testsPath);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }
}
