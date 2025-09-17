<?php

namespace Tests;

class ExampleTest extends TestCase
{
    public function testHomeRedirectsToDashboard(): void
    {
        $response = $this->get('/');
        $this->assertRedirect($response, '/dashboard');
    }
}
