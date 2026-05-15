<?php
/**
 * @var ViewColumn $column
 * @var User       $record
 * @var ListUsers  $livewire
 */

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Tariffs\TariffCatalog;
use Filament\Tables\Columns\ViewColumn;

$livewire  = $column->getLivewire();
$isEditing = method_exists($livewire, 'isEditingRecord')
    ? $livewire->isEditingRecord($record)
    : false;
$value     = method_exists($livewire, 'getEditingUserFieldValue')
    ? (string)$livewire->getEditingUserFieldValue((int)$record->id, 'tariff_code', (string)($record->tariff_code ?? ''))
    : (string)($record->tariff_code ?? '');
$options   = TariffCatalog::options();
?>
@if (!$isEditing)
    <div class="editable-tariff-cell">
        <span class="editable-tariff-cell__value">{{ $options[$value] ?? $value }}</span>
    </div>
@else
    <div class="editable-tariff-cell editable-tariff-cell--editing">
        <div class="editable-tariff-cell__editor">
            <select
                wire:model.defer="editingUserData.tariff_code"
                class="editable-tariff-cell__select"
            >
                @foreach ($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif
