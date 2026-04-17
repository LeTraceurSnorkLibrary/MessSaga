@props([
    'statePath',
    'label' => '',
    'hint' => '',
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'suffix' => '',
    'value' => 0,
    'dataProgressBarMeasureType' => null,
])
<?php
/**
 * @var string|null $dataProgressBarMeasureType
 */

$measureType = is_string($dataProgressBarMeasureType)
    ? $dataProgressBarMeasureType
    : '';
?>
<div
    class="progress-input"
    data-progress-input
    data-progress-input-converter="{{ $measureType }}"
>
    <div class="progress-input__controls">
        <span class="progress-input__label">{{ $label }}</span>
        @if($measureType !== '')
            <p class="progress-input__converted" data-progress-input-converted wire:ignore></p>
        @endif
        <input
            type="range"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $value }}"
            wire:model.live="{{ $statePath }}"
            class="progress-input__range"
            data-progress-input-range
        />
        <input
            type="number"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $value }}"
            wire:model.live="{{ $statePath }}"
            class="progress-input__number"
            data-progress-input-number
        />
        @if($hint !== '')
            <p class="progress-input__hint">{{ $hint }}</p>
        @endif
    </div>
</div>

