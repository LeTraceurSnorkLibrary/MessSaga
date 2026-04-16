@php
    /** @var \Filament\Forms\Components\ViewField $field */
    $statePath = $getStatePath();
@endphp

<x-forms.progress-input
    :value="$getState() ?? 0"
    label="Доступно файлов"
    min="0"
    max="50000"
    step="1"
    suffix=" шт"
    :state-path="$statePath"
/>

