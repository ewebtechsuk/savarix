<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function testHomeDisplaysMarketingPage(): void
    {
        $response = $this->get('/');

        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Ressapp | Property Management Automation');
        $this->assertSee($response, 'marketing-app');

    }
}
