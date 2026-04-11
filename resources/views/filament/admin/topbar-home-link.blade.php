<?php

use Filament\Support\Icons\Heroicon;

?>
<div class="fi-topbar-home-link flex shrink-0 items-center pe-1">
    <x-filament::icon-button
        color="gray"
        :icon="Heroicon::OutlinedHome"
        icon-size="lg"
        tag="a"
        :href="url('/')"
        label="На главную сайта"
    />
</div>
