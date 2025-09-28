<?php

namespace Tests;

use App\Models\User;

class DashboardTest extends TestCase
{
    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Log in');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_dashboard(): void
    {
        $user = User::factory()->make([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee("You're logged in!", false);
    }
}
