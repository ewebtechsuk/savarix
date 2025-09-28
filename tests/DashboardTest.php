<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DashboardTest extends TestCase
{
    use DatabaseMigrations;

    public function testLoginPageLoads()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Log in');
    }

    public function testDashboardRequiresAuthentication()
    {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function testAuthenticatedUserCanSeeDashboard()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');
    }
}
