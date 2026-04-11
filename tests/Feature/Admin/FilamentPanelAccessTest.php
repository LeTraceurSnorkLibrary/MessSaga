<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_manage_panel(): void
    {
        $response = $this->get('/manage');

        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_non_admin_user_cannot_access_manage_panel(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER->value,
        ]);

        $this->actingAs($user)->get('/manage')->assertForbidden();
    }

    public function test_manager_cannot_access_manage_panel(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::MANAGER->value,
        ]);

        $this->actingAs($user)->get('/manage')->assertForbidden();
    }

    public function test_admin_can_access_manage_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN->value,
        ]);

        $this->actingAs($admin)->get('/manage')->assertOk();
    }

    public function test_admin_can_open_users_resource(): void
    {
        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN->value,
        ]);

        $this->actingAs($admin)->get('/manage/users')->assertOk();
    }
}
