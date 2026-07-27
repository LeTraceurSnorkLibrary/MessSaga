<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Media quota grace period
    |--------------------------------------------------------------------------
    |
    | After a tariff downgrade while the user is still over quota, existing media
    | is kept until this many days pass (see EnforceExpiredMediaQuotaCommand).
    |
    */
    'grace_days' => (int) env('MEDIA_QUOTA_GRACE_DAYS', 7),
];
