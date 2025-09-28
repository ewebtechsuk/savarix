<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function testHomeDisplaysMarketingPage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Modern Estate Agency Software');
        $response->assertSee('Get Started Free');
    }
}
