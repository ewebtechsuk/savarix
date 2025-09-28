<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Dotenv\Dotenv;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected $signature = 'test {--filter= : Run only tests matching the given filter}';

    protected $description = 'Run the application test suite.';

    public function handle(): int
    {
        $binary = base_path('vendor/bin/phpunit');

        if (! file_exists($binary)) {
            $this->error('phpunit executable not found. Run "composer install" first.');

            return self::FAILURE;
        }

        $command = [PHP_BINARY, $binary];

        if ($filter = $this->option('filter')) {
            $command[] = '--filter';
            $command[] = $filter;
        }

        $environment = $this->testingEnvironment();

        $process = new Process($command, base_path(), $environment, null, null);

        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? self::FAILURE;
}

    /**
     * Build the environment variables used when executing the test suite.
     */
    private function testingEnvironment(): array
    {
        $environment = array_merge($_ENV, [
            'APP_ENV' => 'testing',
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
        ]);

        $testingEnv = base_path('.env.testing');

        if (is_file($testingEnv)) {
            $dotenv = Dotenv::createArrayBacked(base_path(), '.env.testing');
            $environment = array_merge($environment, $dotenv->load());
        }

        return $environment;
    }
}
