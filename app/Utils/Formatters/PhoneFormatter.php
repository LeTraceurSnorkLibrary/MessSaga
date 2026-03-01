<?php

namespace App\Utils\Formatters;

use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneFormatter
{
    /**
     * International format: +7 123 456-78-90
     *
     * @param string $phone
     * @param string $country
     *
     * @return string
     */
    public static function international(string $phone, string $country = 'RU'): string
    {
        return (new PhoneNumber($phone, $country))->formatInternational();
    }
}
