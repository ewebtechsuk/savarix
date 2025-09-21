<?php

if (!function_exists('each')) {
    function each(array &$array)
    {
        $key = key($array);

        if ($key === null) {
            return false;
        }

        $value = current($array);
        next($array);

        return [
            1 => $value,
            'value' => $value,
            0 => $key,
            'key' => $key,
        ];
    }
}

if (!class_exists('PHP_Timer')) {
    class PHP_Timer
    {
        /**
         * @var array<string,int>
         */
        private static $times = [
            'hour' => 3600000,
            'minute' => 60000,
            'second' => 1000,
        ];

        /**
         * @var float[]
         */
        private static $startTimes = [];

        /**
         * @var float
         */
        public static $requestTime;

        public static function start(): void
        {
            self::$startTimes[] = microtime(true);
        }

        public static function stop(): float
        {
            if (empty(self::$startTimes)) {
                return 0.0;
            }

            return microtime(true) - array_pop(self::$startTimes);
        }

        public static function secondsToTimeString($time): string
        {
            $milliseconds = (int) round((float) $time * 1000);

            foreach (self::$times as $unit => $value) {
                if ($milliseconds >= $value) {
                    $duration = floor($milliseconds / $value * 100.0) / 100.0;

                    return $duration . ' ' . ($duration == 1.0 ? $unit : $unit . 's');
                }
            }

            return $milliseconds . ' ms';
        }

        public static function timeSinceStartOfRequest(): string
        {
            return self::secondsToTimeString(microtime(true) - self::$requestTime);
        }

        public static function resourceUsage(): string
        {
            return sprintf(
                'Time: %s, Memory: %4.2fMB',
                self::timeSinceStartOfRequest(),
                memory_get_peak_usage(true) / 1048576
            );
        }
    }

    if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
        PHP_Timer::$requestTime = (float) $_SERVER['REQUEST_TIME_FLOAT'];
    } elseif (isset($_SERVER['REQUEST_TIME'])) {
        PHP_Timer::$requestTime = (float) $_SERVER['REQUEST_TIME'];
    } else {
        PHP_Timer::$requestTime = microtime(true);
    }
}

