<?php
/**
 * @var ViewField $field
 */

use Filament\Forms\Components\ViewField;

$statePath = $getStatePath();
?>
<x-forms.progress-input
    :value="$getState() ?? 0"
    label="Доступное место"
    hint="Шаг: 100 МБ"
    min="0"
    max="102400"
    step="100"
    suffix=" МБ"
    :state-path="$statePath"
/>

