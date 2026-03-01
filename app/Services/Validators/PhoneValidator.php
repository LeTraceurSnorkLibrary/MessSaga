<?php

declare(strict_types=1);

namespace App\Services\Validators;

use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

/**
 * Валидатор телефонных номеров
 */
class PhoneValidator
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public function isPhoneNumber(string $value): bool
    {
        try {
            $phoneNumber = new PhoneNumber($value, 'INTERNATIONAL');

            return $phoneNumber->isValid();
        } catch (Throwable) {
            return false;
        }
    }
}
