<?php

namespace Tests;

class TenantPortalTest extends TestCase
{
    public function test_frontend_landing_page_is_served(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Modern Estate Agency Software');
    }

    public function test_static_pricing_page_is_accessible(): void
    {
        $response = $this->get('/pricing.html');

        $response->assertOk();
        $response->assertSee('Pricing', false);
    }

    public function test_frontend_asset_route_serves_files(): void
    {
        $response = $this->get('/assets/style.css');

        $response->assertOk();
        $response->assertSee('font-family', false);
    }

    public function test_requesting_unknown_static_page_returns_not_found(): void
    {
        $response = $this->get('/unknown-page.html');

        $response->assertNotFound();
    }
}
