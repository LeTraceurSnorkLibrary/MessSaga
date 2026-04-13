<?php

use Filament\Support\Icons\Heroicon;

?>
<div class="fi-topbar-user-dashboard-link flex shrink-0 items-center pe-1">
    <x-filament::icon-button
        color="gray"
        :icon="Heroicon::OutlinedSquares2x2"
        icon-size="lg"
        tag="a"
        :href="route('dashboard')"
        label="Личный кабинет (дашборд)"
    />
</div>
