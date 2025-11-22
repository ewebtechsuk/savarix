<?php

namespace Tests\Feature\Admin;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AgencyUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_agency_domain_without_500(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('SavarixPass123!'),
            'role' => 'owner',
        ]);

        $agency = Agency::factory()->create([
            'name' => 'Aktonz',
            'email' => 'info@aktonz.com',
            'domain' => null,
        ]);

        $response = $this->actingAs($owner)
            ->from(route('admin.agencies.show', $agency))
            ->put(route('admin.agencies.update', $agency), [
                'name' => 'Aktonz',
                'email' => 'info@aktonz.com',
                'phone' => '02030389009',
                'domain' => 'aktonz.savarix.com',
            ]);

        $response->assertRedirect(route('admin.agencies.show', $agency));

        $this->assertSame(
            'aktonz.savarix.com',
            $agency->fresh()->domain
        );
    }
}
