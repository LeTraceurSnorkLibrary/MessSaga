<?php
/**
 * @var ViewColumn $column
 * @var User       $record
 * @var ListUsers  $livewire
 */

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Tables\Columns\ViewColumn;

$livewire  = $column->getLivewire();
$field     = $column->getName();
$isEditing = method_exists($livewire, 'isEditingRecord')
    ? $livewire->isEditingRecord($record)
    : false;
$value     = method_exists($livewire, 'getEditingUserFieldValue')
    ? (string)$livewire->getEditingUserFieldValue((int)$record->id, $field, (string)($record->{$field} ?? ''))
    : (string)($record->{$field} ?? '');
$inputType = $field === 'email'
    ? 'email'
    : 'text';
?>
@if (!$isEditing)
    <div class="editable-text-cell">
        <span class="editable-text-cell__value">{{ $value }}</span>
    </div>
@else
    <div class="editable-text-cell editable-text-cell--editing">
        <div class="editable-text-cell__editor">
            <input
                type="{{ $inputType }}"
                wire:model.defer="editingUserData.{{ $field }}"
                class="editable-text-cell__input"
            />
        </div>
    </div>
@endif
