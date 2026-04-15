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

    public function test_basic_user_cannot_access_manage_panel(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER->value,
        ]);

        $this->actingAs($user)->get('/manage')->assertForbidden();
    }

    public function test_manager_can_access_manage_panel(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::MANAGER->value,
        ]);

        $this->actingAs($user)->get('/manage')->assertOk();
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

    public function test_inertia_login_with_manage_intended_for_non_admin_forces_full_page_manage_navigation(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER->value,
        ]);

        $response = $this
            ->withHeader('X-Inertia', 'true')
            ->withSession(['url.intended' => url('/manage')])
            ->post('/login', [
                'email'    => $user->email,
                'password' => 'password',
            ]);

        $response
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/manage'));
    }
}
