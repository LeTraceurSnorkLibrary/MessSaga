<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Admin\Resources\Users\UserResource;
use App\Models\User;
use App\Tariffs\TariffCatalog;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Params of a users list page inside a Filament admin panel.
 */
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public ?int $editingUserId = null;

    /**
     * @var array<string, mixed>
     */
    public array $editingUserData = [
        'name'        => '',
        'email'       => '',
        'tariff_code' => '',
        'role'        => '',
    ];

    /**
     * @param User $record
     *
     * @return bool
     */
    public function isEditingRecord(User $record): bool
    {
        return $this->editingUserId === (int)$record->getKey();
    }

    /**
     * @param User $record
     *
     * @return void
     */
    public function startEditingRecord(User $record): void
    {
        $this->editingUserId   = (int)$record->getKey();
        $this->editingUserData = [
            'name'        => (string)$record->name,
            'email'       => (string)$record->email,
            'tariff_code' => (string)$record->tariff_code,
            'role'        => (string)$record->role,
        ];
    }

    /**
     * @return void
     */
    public function stopEditingRecord(): void
    {
        $this->editingUserId   = null;
        $this->editingUserData = [
            'name'        => '',
            'email'       => '',
            'tariff_code' => '',
            'role'        => '',
        ];
    }

    /**
     * @param int        $userId
     * @param string     $field
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getEditingUserFieldValue(int $userId, string $field, mixed $default = null): mixed
    {
        if ($this->editingUserId !== $userId) {
            return $default;
        }

        if (!array_key_exists($field, $this->editingUserData)) {
            return $default;
        }

        return $this->editingUserData[$field];
    }

    /**
     * @param int    $userId
     * @param string $field
     * @param mixed  $value
     *
     * @return void
     */
    public function setEditingUserFieldValue(int $userId, string $field, mixed $value): void
    {
        if ($this->editingUserId !== $userId) {
            return;
        }

        if (!array_key_exists($field, $this->editingUserData)) {
            return;
        }

        $normalizedValue = (string)$value;

        if ($field === 'role') {
            $allowedRoles = array_column(UserRoleEnum::cases(), 'value');
            if (!in_array($normalizedValue, $allowedRoles, true)) {
                return;
            }
        }

        if ($field === 'tariff_code') {
            $allowedTariffs = array_keys(TariffCatalog::options());
            if (!in_array($normalizedValue, $allowedTariffs, true)) {
                return;
            }
        }

        $this->editingUserData[$field] = $normalizedValue;
    }

    /**
     * @param User $record
     *
     * @throws ValidationException
     * @return void
     */
    public function saveEditingRecord(User $record): void
    {
        if ($this->editingUserId !== (int)$record->getKey()) {
            return;
        }

        $this->validate([
            'editingUserData.name'        => ['required', 'string', 'max:255'],
            'editingUserData.email'       => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($record->getKey()),
            ],
            'editingUserData.tariff_code' => ['required', Rule::in(array_keys(TariffCatalog::options()))],
            'editingUserData.role'        => ['required', Rule::in(array_column(UserRoleEnum::cases(), 'value'))],
        ]);

        $record->name        = $this->editingUserData['name'];
        $record->email       = $this->editingUserData['email'];
        $record->tariff_code = $this->editingUserData['tariff_code'];
        $record->assignRole($this->editingUserData['role']);
        $record->save();

        $this->stopEditingRecord();
    }

    /**
     * @return string
     */
    public function getBreadcrumb(): string
    {
        return 'Список';
    }
}
