<?php

namespace Offline\Support;

class Table
{
    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    public static function render(array $headers, array $rows): void
    {
        $widths = [];
        foreach ($headers as $index => $header) {
            $widths[$index] = mb_strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $index => $cell) {
                $widths[$index] = max($widths[$index] ?? 0, mb_strlen($cell));
            }
        }

        $border = self::border($widths);
        echo $border, PHP_EOL;
        echo self::formatRow($headers, $widths), PHP_EOL;
        echo $border, PHP_EOL;
        foreach ($rows as $row) {
            echo self::formatRow($row, $widths), PHP_EOL;
        }
        echo $border, PHP_EOL;
    }

    /**
     * @param array<int, int> $widths
     */
    private static function border(array $widths): string
    {
        $segments = ['+'];
        foreach ($widths as $width) {
            $segments[] = str_repeat('-', $width + 2);
            $segments[] = '+';
        }

        return implode('', $segments);
    }

    /**
     * @param array<int, string> $values
     * @param array<int, int> $widths
     */
    private static function formatRow(array $values, array $widths): string
    {
        $cells = ['|'];
        foreach ($widths as $index => $width) {
            $value = $values[$index] ?? '';
            $cells[] = ' ' . str_pad($value, $width) . ' |';
        }

        return implode('', $cells);
    }
}
