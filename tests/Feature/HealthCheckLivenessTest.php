<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\HealthCheckController;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(HealthCheckController::class, 'liveness')]
class HealthCheckLivenessTest extends TestCase
{
    public function test_liveness_returns_ok_status(): void
    {
        $response = $this->get(route('health.liveness'));

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}
