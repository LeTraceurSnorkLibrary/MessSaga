<?php

declare(strict_types=1);

namespace App\Services\Import\DTO;

use Illuminate\Database\Eloquent\Model;

final readonly class MessageCreateResult
{
    /**
     * @param Model $message
     * @param bool  $created
     */
    public function __construct(
        private Model $message,
        private bool  $created
    ) {
    }

    /**
     * @return Model
     */
    public function getMessage(): Model
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isCreated(): bool
    {
        return $this->created;
    }
}

