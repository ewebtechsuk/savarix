<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerAdminAuthenticationTest extends TestCase
{
    protected function adminPath(): string
    {
        return trim(env('SAVARIX_ADMIN_PATH', 'savarix-admin'), '/');
    }

    public function test_owner_login_page_loads(): void
    {
        $loginPath = '/' . $this->adminPath() . '/login';

        $response = $this->get($loginPath);

        $response->assertOk();
        $response->assertSee('Owner Admin');
        $response->assertSee('Sign in to Savarix');
    }

    public function test_owner_can_log_in_and_redirects_to_dashboard(): void
    {
        $loginPath = '/' . $this->adminPath() . '/login';
        $dashboardPath = '/' . $this->adminPath() . '/dashboard';

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('SavarixPass123!'),
            'role' => 'owner',
        ]);

        $response = $this->post($loginPath, [
            'email' => $owner->email,
            'password' => 'SavarixPass123!',
        ]);

        $response->assertRedirect($dashboardPath);
        $this->assertAuthenticatedAs($owner, 'web');
        $this->assertStringNotContainsString('/login', $response->headers->get('Location'));
    }

    public function test_owner_login_sets_session_cookie(): void
    {
        $loginPath = '/' . $this->adminPath() . '/login';
        $dashboardPath = '/' . $this->adminPath() . '/dashboard';

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('SavarixPass123!'),
            'role' => 'owner',
        ]);

        $response = $this->post($loginPath, [
            'email' => $owner->email,
            'password' => 'SavarixPass123!',
        ]);

        $response->assertRedirect($dashboardPath);
        $response->assertCookie(config('session.cookie', 'savarix_session'));
    }

    public function test_authenticated_owner_is_redirected_from_login_form(): void
    {
        $loginPath = '/' . $this->adminPath() . '/login';

        $owner = User::factory()->create([
            'role' => 'owner',
        ]);

        $response = $this->actingAs($owner)->get($loginPath);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_dashboard_redirects_to_admin_login_when_guest(): void
    {
        $dashboardPath = '/' . $this->adminPath() . '/dashboard';

        $response = $this->get($dashboardPath);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_impersonation_redirects_to_tenant_dashboard(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@savarix.com',
            'password' => Hash::make('SavarixPass123!'),
            'role' => 'owner',
        ]);

        $agency = Agency::create([
            'name' => 'Aktonz Estate Agents',
            'slug' => 'aktonz',
            'email' => 'info@aktonz.com',
            'domain' => 'aktonz.savarix.com',
        ]);

        $agencyAdmin = User::factory()->create([
            'email' => 'info@aktonz.com',
            'password' => Hash::make('AktonzTempPass123!'),
            'role' => 'agency_admin',
            'agency_id' => $agency->id,
        ]);

        $response = $this->actingAs($owner)
            ->post(route('admin.agencies.impersonate', $agency));

        $response->assertRedirect('https://aktonz.savarix.com/dashboard');
        $this->assertAuthenticatedAs($agencyAdmin, 'web');
    }
}
