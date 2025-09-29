<?php

namespace Tests;

use App\Models\User;

class DashboardTest extends TestCase
{
    public function testLoginPageLoads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200)
            ->assertSee('Login');

    }

    public function testDashboardRequiresAuthentication(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

    }

    public function testAuthenticatedUserCanSeeDashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test-user@example.com',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200)
            ->assertSee('Dashboard');

    }
}
