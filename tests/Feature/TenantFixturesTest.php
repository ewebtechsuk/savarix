<?php

namespace Tests\Feature;

use Database\Seeders\TenantFixtures;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenantFixturesTest extends TestCase
{
    /** @test */
    public function it_seeds_the_aktonz_hostinger_domain(): void
    {
        TenantFixtures::seed();

        $this->assertTrue(
            Domain::query()
                ->where('domain', 'aktonz.darkorange-chinchilla-918430.hostingersite.com')
                ->exists(),
            'Aktonz Hostinger domain was not seeded.'
        );
    }
}
