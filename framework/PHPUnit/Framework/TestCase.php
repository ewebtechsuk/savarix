<?php

namespace PHPUnit\Framework;

abstract class TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }

    public function run(): array
    {
        $results = [];
        foreach (get_class_methods($this) as $method) {
            if (str_starts_with($method, 'test')) {
                $results[] = $this->runTestMethod($method);
            }
        }
        return $results;
    }

    private function runTestMethod(string $method): array
    {
        $this->setUp();
        try {
            $this->{$method}();
            $status = 'passed';
            $message = '';
        } catch (AssertionFailedError $e) {
            $status = 'failed';
            $message = $e->getMessage();
        } catch (\Throwable $e) {
            $status = 'error';
            $message = $e->getMessage();
        }
        $this->tearDown();

        return [
            'test' => get_class($this) . '::' . $method,
            'status' => $status,
            'message' => $message,
        ];
    }

    protected static function fail(string $message): void
    {
        throw new AssertionFailedError($message);
    }

    public static function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            self::fail($message ?: sprintf('Failed asserting that %s is identical to %s.', self::export($actual), self::export($expected)));
        }
    }

    public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        if (strpos($haystack, $needle) === false) {
            self::fail($message ?: sprintf('Failed asserting that "%s" contains "%s".', $haystack, $needle));
        }
    }

    public static function assertTrue($value, string $message = ''): void
    {
        if ($value !== true) {
            self::fail($message ?: 'Failed asserting that value is true.');
        }
    }

    private static function export($value): string
    {
        if (is_scalar($value) || $value === null) {
            return var_export($value, true);
        }
        if (is_object($value)) {
            return 'object(' . get_class($value) . ')';
        }
        return gettype($value);
    }
}
