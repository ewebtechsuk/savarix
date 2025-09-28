<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function testHomeDisplaysMarketingPage(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Modern Estate Agency Software')
            ->assertSee('Get Started Free');
    }
}
