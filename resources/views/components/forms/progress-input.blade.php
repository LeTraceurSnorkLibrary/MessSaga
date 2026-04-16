@props([
    'statePath',
    'label' => '',
    'hint' => '',
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'suffix' => '',
    'value' => 0,
])

<div
    x-data="{
        value: Number(@js($value ?? 0)),
        min: Number(@js($min)),
        max: Number(@js($max)),
        step: Number(@js($step)),
        normalize() {
            if (Number.isNaN(this.value)) this.value = this.min;
            this.value = Math.max(this.min, Math.min(this.max, this.value));
        },
    }"
    x-init="normalize()"
    class="progress-input"
>
    <div class="progress-input__head">
        <span class="progress-input__label">{{ $label }}</span>
        <span class="progress-input__value">
            <span x-text="value"></span>{{ $suffix }}
        </span>
    </div>
    @if($hint !== '')
        <p class="progress-input__hint">{{ $hint }}</p>
    @endif
    <div class="progress-input__controls">
        <input
            type="range"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            x-model.number="value"
            @change="normalize()"
            wire:model.live="{{ $statePath }}"
            class="progress-input__range"
        />
        <input
            type="number"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            x-model.number="value"
            @change="normalize()"
            wire:model.live="{{ $statePath }}"
            class="progress-input__number"
        />
    </div>
</div>

