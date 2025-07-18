<?php

use App\User;
use Illuminate\Support\Facades\Hash;

class DashboardTest extends TestCase
{
    public function testLoginPageLoads()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function testDashboardRequiresAuthentication()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function testAuthenticatedUserCanSeeDashboard()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ressapp Dashboard');
    }
}
