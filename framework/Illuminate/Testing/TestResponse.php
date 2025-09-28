<?php

namespace Illuminate\Testing;

use Framework\Http\Response;
use PHPUnit\Framework\AssertionFailedError;

class TestResponse
{
    private Response $baseResponse;

    public function __construct(Response $response)
    {
        $this->baseResponse = $response;
    }

    public function status(): int
    {
        return $this->baseResponse->status();
    }

    public function content(): string
    {
        return $this->baseResponse->body();
    }

    public function header(string $name): ?string
    {
        return $this->baseResponse->header($name);
    }

    public function assertStatus(int $expected): self
    {
        if ($this->status() !== $expected) {
            throw new AssertionFailedError(
                sprintf('Expected response status %d but received %d.', $expected, $this->status())
            );
        }

        return $this;
    }

    public function assertRedirect(string $location): self
    {
        $status = $this->status();
        if ($status < 300 || $status >= 400) {
            throw new AssertionFailedError(
                sprintf('Response status %d is not a redirect status code.', $status)
            );
        }

        $actual = $this->header('location');

        if ($actual !== $location) {
            throw new AssertionFailedError(
                sprintf('Expected redirect to [%s] but redirected to [%s].', $location, (string) $actual)
            );
        }

        return $this;
    }

    public function assertSee(string $value): self
    {
        if (strpos($this->content(), $value) === false) {
            throw new AssertionFailedError(
                sprintf('Failed asserting that the response contains "%s".', $value)
            );
        }

        return $this;
    }
}
