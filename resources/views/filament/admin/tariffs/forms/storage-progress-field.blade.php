<?php
/**
 * @var ViewField         $field
 * @var Closure(): mixed  $getState     Returns current field state value from Filament form data.
 * @var Closure(): string $getStatePath Returns Livewire/Filament dot-path for `wire:model`.
 */

use Filament\Forms\Components\ViewField;

[$value, $statePath] = [
    $getState() ?? 0,
    $getStatePath(),
];
?>
<x-forms.progress-input
    :value="$value"
    label="Доступное место"
    hint="Шаг: 128 Мб (1/8 Гб)"
    min="0"
    max="51200"
    step="128"
    data-progress-bar-measure-type="mbytes"
    suffix=" Мб"
    :state-path="$statePath"
/>

