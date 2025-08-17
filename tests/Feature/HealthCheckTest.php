<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_returns_successful_response(): void
    {
        $response = $this->get('/api/v1/health');

        $response->assertStatus(200);
        
        $response->assertJsonStructure([
            'status',
            'app',
            'timestamp'
        ]);

        $response->assertJson([
            'status' => 'ok'
        ]);

        // Verify timestamp is a valid ISO 8601 string
        $data = $response->json();
        $this->assertNotNull($data['timestamp']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?([+-]\d{2}:\d{2}|Z)$/', $data['timestamp']);
        
        // Verify app name is set
        $this->assertNotNull($data['app']);
    }
}