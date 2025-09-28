<?php

namespace Tests;

use App\Models\User;

class DashboardTest extends TestCase
{
    public function testLoginPageLoads(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Log in');
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function testAuthenticatedUserCanSeeDashboard(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user, 'web');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee("You're logged in!");
    }
}
