<?php

declare(strict_types=1);

namespace App\Utils;

use App\Services\Validators\PhoneValidator;
use App\Utils\Formatters\PhoneFormatter;

final readonly class PhoneUtil
{
    /**
     * @param PhoneValidator $validator
     * @param PhoneFormatter $formatter
     */
    public function __construct(
        private PhoneValidator $validator,
        private PhoneFormatter $formatter
    ) {
    }

    /**
     * @return PhoneValidator
     */
    public function validator(): PhoneValidator
    {
        return $this->validator;
    }

    /**
     * @return PhoneFormatter
     */
    public function formatter(): PhoneFormatter
    {
        return $this->formatter;
    }
}
