<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DashboardTest extends TestCase
{
    public function testLoginPageLoads(): void
    {
        $response = $this->get('/login');
        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Login');
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $response = $this->get('/dashboard');
        $this->assertRedirect($response, '/login');
    }

    public function testAuthenticatedUserCanSeeDashboard(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $this->assertStatus($response, 200);
        $this->assertSee($response, 'Dashboard');
    }
}
