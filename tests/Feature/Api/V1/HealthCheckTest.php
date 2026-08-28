<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_the_versioned_api_health_endpoint_is_operational(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'message' => 'Wavexa API is operational.',
                'version' => 'v1',
            ]);
    }
}
