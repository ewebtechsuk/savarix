<?php

namespace Tests\Support;

use Framework\Http\Response;
use Tests\TestCase;

class LegacyTestResponse
{
    public function __construct(
        private readonly Response $response,
        private readonly TestCase $testCase
    ) {
    }

    public function assertStatus(int $expected): self
    {
        $this->testCase::assertSame($expected, $this->response->status());

        return $this;
    }

    public function assertOk(): self
    {
        return $this->assertStatus(200);
    }

    public function assertRedirect(string $location): self
    {
        $this->assertStatus(302);
        $this->testCase::assertSame($location, $this->response->header('location'));

        return $this;
    }

    public function assertSee(string $text): self
    {
        $this->testCase::assertStringContainsString($text, $this->response->body());

        return $this;
    }
}
