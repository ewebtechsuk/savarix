<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DashboardTest extends TestCase
{
    public function testLoginPageLoads(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('Log in', false);
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function testAuthenticatedUserCanSeeDashboard(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
    }
}
