<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\HealthCheckController;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(HealthCheckController::class, 'readiness')]
#[CoversMethod(HealthCheckController::class, 'checkDiskWritable')]
class HealthCheckReadinessTest extends TestCase
{
    public function test_readiness_returns_ok_when_media_and_import_disks_are_writable(): void
    {
        config()->set('filesystems.media_disk', 'local');
        config()->set('filesystems.imports_tmp_disk', 'local');

        $response = $this->get(route('health.readiness'));

        $response->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.media_disk.ok', true)
            ->assertJsonPath('checks.imports_tmp_disk.ok', true);
    }

    public function test_readiness_returns_503_when_disk_configuration_is_invalid(): void
    {
        config()->set('filesystems.media_disk', 'non_existing_disk');
        config()->set('filesystems.imports_tmp_disk', 'local');

        $response = $this->get(route('health.readiness'));

        $response->assertStatus(503)
            ->assertJsonPath('status', 'fail')
            ->assertJsonPath('checks.media_disk.ok', false);
    }
}
