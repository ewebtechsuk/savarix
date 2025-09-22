<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function testHomeDisplaysMarketingPage(): void
    {
        $response = $this->get('/');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Modern Estate Agency Software');
        $this->assertSee($response, 'Get Started Free');
    }
}
