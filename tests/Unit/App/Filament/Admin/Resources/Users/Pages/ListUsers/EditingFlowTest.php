<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\Pages\ListUsers;

use App\Enums\UserRoleEnum;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\Tariff;
use App\Models\User;
use App\Tariffs\FreeTariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ListUsers::class)]
final class EditingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_and_stop_editing_record_manage_component_state(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'tariff_10',
            'label'                 => 'Тариф 10',
            'price'                 => 10.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);

        $user = User::factory()->create([
            'role'        => UserRoleEnum::USER->value,
            'tariff_code' => $tariff->name,
        ]);

        $page = new ListUsers();
        $page->startEditingRecord($user);

        $this->assertTrue($page->isEditingRecord($user));
        $this->assertSame($user->name, $page->editingUserData['name']);
        $this->assertSame($user->email, $page->editingUserData['email']);
        $this->assertSame($tariff->name, $page->editingUserData['tariff_code']);
        $this->assertSame(UserRoleEnum::USER->value, $page->editingUserData['role']);

        $page->stopEditingRecord();

        $this->assertNull($page->editingUserId);
        $this->assertSame('', $page->editingUserData['name']);
        $this->assertSame('', $page->editingUserData['email']);
        $this->assertSame('', $page->editingUserData['tariff_code']);
        $this->assertSame('', $page->editingUserData['role']);
    }

    public function test_set_editing_user_field_value_rejects_invalid_role_and_tariff(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 10.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);

        $user = User::factory()->create([
            'role'        => UserRoleEnum::USER->value,
            'tariff_code' => FreeTariff::TARIFF_NAME,
        ]);

        $page = new ListUsers();
        $page->startEditingRecord($user);

        $page->setEditingUserFieldValue($user->id, 'role', 'invalid-role');
        $this->assertSame(UserRoleEnum::USER->value, $page->editingUserData['role']);

        $page->setEditingUserFieldValue($user->id, 'tariff_code', 'invalid-tariff');
        $this->assertSame(FreeTariff::TARIFF_NAME, $page->editingUserData['tariff_code']);

        $page->setEditingUserFieldValue($user->id, 'role', UserRoleEnum::MANAGER->value);
        $page->setEditingUserFieldValue($user->id, 'tariff_code', $tariff->name);

        $this->assertSame(UserRoleEnum::MANAGER->value, $page->editingUserData['role']);
        $this->assertSame($tariff->name, $page->editingUserData['tariff_code']);
    }

    public function test_save_editing_record_persists_buffered_changes_and_resets_state(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 10.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);

        $user = User::factory()->create([
            'role'        => UserRoleEnum::USER->value,
            'tariff_code' => FreeTariff::TARIFF_NAME,
        ]);

        /** @var ListUsers&Mockery\MockInterface $page */
        $page = Mockery::mock(ListUsers::class)->makePartial();
        $page->shouldReceive('validate')->once()->andReturn([]);

        $page->editingUserId   = $user->id;
        $page->editingUserData = [
            'name'        => 'Updated Name',
            'email'       => 'updated@example.com',
            'tariff_code' => $tariff->name,
            'role'        => UserRoleEnum::ADMIN->value,
        ];

        $page->saveEditingRecord($user);

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('updated@example.com', $user->fresh()->email);
        $this->assertSame($tariff->name, $user->fresh()->tariff_code);
        $this->assertSame(UserRoleEnum::ADMIN->value, $user->fresh()->role);
        $this->assertNull($page->editingUserId);
    }

    public function test_save_editing_record_does_nothing_for_non_editing_user(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 10.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);

        $user = User::factory()->create([
            'role'        => UserRoleEnum::USER->value,
            'tariff_code' => FreeTariff::TARIFF_NAME,
        ]);

        /** @var ListUsers&Mockery\MockInterface $page */
        $page = Mockery::mock(ListUsers::class)->makePartial();
        $page->shouldReceive('validate')->never();

        $page->editingUserId   = $user->id + 1;
        $page->editingUserData = [
            'name'        => 'Ignored Name',
            'email'       => 'ignored@example.com',
            'tariff_code' => $tariff->name,
            'role'        => UserRoleEnum::ADMIN->value,
        ];

        $page->saveEditingRecord($user);

        $this->assertNotSame('Ignored Name', $user->fresh()->name);
        $this->assertNotSame('ignored@example.com', $user->fresh()->email);
        $this->assertSame(FreeTariff::TARIFF_NAME, $user->fresh()->tariff_code);
        $this->assertSame(UserRoleEnum::USER->value, $user->fresh()->role);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
