<?php

namespace {
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

    if (!class_exists(\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class)) {
        $maintenanceFallback = __DIR__.'/../deps/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php';

        if (!class_exists(\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class)
            && file_exists($maintenanceFallback)) {
            require_once $maintenanceFallback;
        }

        if (class_exists(\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class)) {
            class_alias(
                \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
                \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class
            );
        }
    }

    if (!class_exists(\Illuminate\Foundation\Http\Middleware\ValidatePostSize::class)) {
        $validateFallback = __DIR__.'/../deps/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyPostSize.php';

        if (!class_exists(\Illuminate\Foundation\Http\Middleware\VerifyPostSize::class)
            && file_exists($validateFallback)) {
            require_once $validateFallback;
        }

        if (class_exists(\Illuminate\Foundation\Http\Middleware\VerifyPostSize::class)) {
            class_alias(
                \Illuminate\Foundation\Http\Middleware\VerifyPostSize::class,
                \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class
            );
        }
    }
}

namespace Symfony\Component\Debug\Exception {
    if (!class_exists(FatalErrorException::class)) {
        class FatalErrorException extends \ErrorException
        {
            public function __construct(
                $message,
                $code,
                $severity,
                $filename,
                $lineno,
                ?int $traceOffset = null,
                bool $traceArgs = true,
                ?array $trace = null
            ) {
                parent::__construct($message, $code, $severity, $filename, $lineno);

                if (null !== $trace) {
                    if (! $traceArgs) {
                        foreach ($trace as &$frame) {
                            unset($frame['args'], $frame['this'], $frame);
                        }
                    }

                    $this->setTrace($trace);
                } elseif (null !== $traceOffset) {
                    if (\function_exists('xdebug_get_function_stack')) {
                        $trace = xdebug_get_function_stack();
                        if (0 < $traceOffset) {
                            \array_splice($trace, -$traceOffset);
                        }

                        foreach ($trace as &$frame) {
                            if (! isset($frame['type'])) {
                                if (isset($frame['class'])) {
                                    $frame['type'] = '::';
                                }
                            } elseif ('dynamic' === $frame['type']) {
                                $frame['type'] = '->';
                            } elseif ('static' === $frame['type']) {
                                $frame['type'] = '::';
                            }

                            if (! $traceArgs) {
                                unset($frame['params'], $frame['args']);
                            } elseif (isset($frame['params']) && ! isset($frame['args'])) {
                                $frame['args'] = $frame['params'];
                                unset($frame['params']);
                            }
                        }

                        unset($frame);
                        $trace = \array_reverse($trace);
                    } elseif (\function_exists('symfony_debug_backtrace')) {
                        $trace = symfony_debug_backtrace();
                        if (0 < $traceOffset) {
                            \array_splice($trace, 0, $traceOffset);
                        }
                    } else {
                        $trace = [];
                    }

                    $this->setTrace($trace);
                }
            }

            protected function setTrace(array $trace): void
            {
                $traceReflector = new \ReflectionProperty(\Exception::class, 'trace');
                $traceReflector->setAccessible(true);
                $traceReflector->setValue($this, $trace);
            }
        }
    }
}

namespace Illuminate\Foundation\Bootstrap {
    use Exception;
    use ErrorException;
    use Illuminate\Contracts\Foundation\Application;
    use Symfony\Component\Console\Output\ConsoleOutput;
    use Symfony\Component\Debug\Exception\FatalErrorException;
    use Symfony\Component\Debug\Exception\FatalThrowableError;

    if (!class_exists(HandleExceptions::class)) {
        class HandleExceptions
        {
            /**
             * The application instance.
             *
             * @var \Illuminate\Contracts\Foundation\Application
             */
            protected $app;

            /**
             * Bootstrap the given application.
             *
             * @param  \Illuminate\Contracts\Foundation\Application  $app
             * @return void
             */
            public function bootstrap(Application $app)
            {
                $this->app = $app;

                error_reporting(-1);

                set_error_handler([$this, 'handleError']);

                set_exception_handler([$this, 'handleException']);

                register_shutdown_function([$this, 'handleShutdown']);

                if (! $app->environment('testing')) {
                    ini_set('display_errors', 'Off');
                }
            }

            /**
             * Convert a PHP error to an ErrorException.
             *
             * @param  int  $level
             * @param  string  $message
             * @param  string  $file
             * @param  int  $line
             * @param  array  $context
             * @return bool
             *
             * @throws \ErrorException
             */
            public function handleError($level, $message, $file = '', $line = 0, $context = [])
            {
                if ($level === E_DEPRECATED || $level === E_USER_DEPRECATED) {
                    return true;
                }

                if (error_reporting() & $level) {
                    throw new ErrorException($message, 0, $level, $file, $line);
                }

                return true;
            }

            /**
             * Handle an uncaught exception from the application.
             *
             * @param  \Throwable  $e
             * @return void
             */
            public function handleException($e)
            {
                if (! $e instanceof Exception) {
                    $e = new FatalThrowableError($e);
                }

                $this->getExceptionHandler()->report($e);

                if ($this->app->runningInConsole()) {
                    $this->renderForConsole($e);
                } else {
                    $this->renderHttpResponse($e);
                }
            }

            /**
             * Render an exception to the console.
             *
             * @param  \Exception  $e
             * @return void
             */
            protected function renderForConsole(Exception $e)
            {
                $this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
            }

            /**
             * Render an exception as an HTTP response and send it.
             *
             * @param  \Exception  $e
             * @return void
             */
            protected function renderHttpResponse(Exception $e)
            {
                $this->getExceptionHandler()->render($this->app['request'], $e)->send();
            }

            /**
             * Handle the PHP shutdown event.
             *
             * @return void
             */
            public function handleShutdown()
            {
                if (! is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
                    $this->handleException($this->fatalExceptionFromError($error, 0));
                }
            }

            /**
             * Create a new fatal exception instance from an error array.
             *
             * @param  array  $error
             * @param  int|null  $traceOffset
             * @return \Symfony\Component\Debug\Exception\FatalErrorException
             */
            protected function fatalExceptionFromError(array $error, $traceOffset = null)
            {
                return new FatalErrorException(
                    $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
                );
            }

            /**
             * Determine if the error type is fatal.
             *
             * @param  int  $type
             * @return bool
             */
            protected function isFatal($type)
            {
                return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
            }

            /**
             * Get an instance of the exception handler.
             *
             * @return \Illuminate\Contracts\Debug\ExceptionHandler
             */
            protected function getExceptionHandler()
            {
                return $this->app->make('Illuminate\\Contracts\\Debug\\ExceptionHandler');
            }
        }
    }
}

