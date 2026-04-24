<?php
/**
 * @var ViewColumn $column
 * @var User       $record
 * @var ListUsers  $livewire
 */

use App\Enums\UserRoleEnum;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Tables\Columns\ViewColumn;

$livewire   = $column->getLivewire();
$isEditing  = method_exists($livewire, 'isEditingRecord')
    ? $livewire->isEditingRecord($record)
    : false;
$role       = method_exists($livewire, 'getEditingUserFieldValue')
    ? (string)$livewire->getEditingUserFieldValue((int)$record->id, 'role', (string)($record->role ?? ''))
    : (string)($record->role ?? '');
$isUserRole = $role === UserRoleEnum::USER->value;

$roleLabel = match ($role) {
    UserRoleEnum::ADMIN->value   => 'Администратор',
    UserRoleEnum::MANAGER->value => 'Менеджер',
    default                      => 'Пользователь',
};

$roleIcon = match ($role) {
    UserRoleEnum::ADMIN->value   => 'heroicon-o-shield-check',
    UserRoleEnum::MANAGER->value => 'heroicon-o-briefcase',
    default                      => 'heroicon-o-user',
};
?>
@if (!$isEditing)
    @if ($isUserRole)
        <span class="editable-role-cell__empty"></span>
    @else
        <span @class([
            'editable-role-cell__badge',
            'editable-role-cell__badge--admin' => $role === UserRoleEnum::ADMIN->value,
            'editable-role-cell__badge--manager' => $role === UserRoleEnum::MANAGER->value,
        ]) title="{{ $roleLabel }}">
            <x-filament::icon :icon="$roleIcon" class="editable-role-cell__icon"/>
        </span>
    @endif
@else
    <div
        x-data="editableRoleCell({ userId: {{ (int) $record->id }} })"
        x-init="init($refs.trigger, $refs.menu)"
        class="editable-role-cell editable-role-cell--editing"
    >
        <button
            type="button"
            x-ref="trigger"
            @class([
                'editable-role-cell__badge',
                'editable-role-cell__badge--editable',
                'editable-role-cell__badge--admin' => $role === UserRoleEnum::ADMIN->value,
                'editable-role-cell__badge--manager' => $role === UserRoleEnum::MANAGER->value,
                'editable-role-cell__badge--user' => $role === UserRoleEnum::USER->value,
            ])
            title="{{ $roleLabel }}"
        >
            <x-filament::icon :icon="$roleIcon" class="editable-role-cell__icon"/>
        </button>

        <div x-ref="menu" class="editable-role-cell__popover" style="display: none;">
            <button
                type="button"
                class="editable-role-cell__badge editable-role-cell__badge--admin"
                data-role="{{ UserRoleEnum::ADMIN->value }}"
                title="Администратор"
            >
                <x-filament::icon icon="heroicon-o-shield-check" class="editable-role-cell__icon"/>
            </button>
            <button
                type="button"
                class="editable-role-cell__badge editable-role-cell__badge--manager"
                data-role="{{ UserRoleEnum::MANAGER->value }}"
                title="Менеджер"
            >
                <x-filament::icon icon="heroicon-o-briefcase" class="editable-role-cell__icon"/>
            </button>
            <button
                type="button"
                class="editable-role-cell__badge editable-role-cell__badge--user"
                data-role="{{ UserRoleEnum::USER->value }}"
                title="Пользователь"
            >
                <x-filament::icon icon="heroicon-o-user" class="editable-role-cell__icon"/>
            </button>
        </div>
    </div>
@endif
