<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function testHomeDisplaysMarketingPage(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Modern Estate Agency Software')
            ->assertSee('Get Started Free');

    }
}
