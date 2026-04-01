<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\Message;
use Illuminate\Database\QueryException;

class MessageInsertService
{
    public function __construct(
        private readonly MessagePreparationService $messagePreparationService
    ) {
    }

    /**
     * @param class-string<Message> $messageModelClass
     * @param array<string, mixed>  $row
     *
     * @throws QueryException
     */
    public function createMessageSafely(string $messageModelClass, array $row): ?Message
    {
        try {
            /** @var Message $message */
            $message = $messageModelClass::create($row);

            return $message;
        } catch (QueryException $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            /** @var Message|null $existing */
            $existing = $messageModelClass::query()
                ->where('conversation_id', $row['conversation_id'] ?? null)
                ->where(function ($q) use ($row): void {
                    $externalId = $this->messagePreparationService->normalizeExternalId($row['external_id'] ?? null);
                    if ($externalId !== null) {
                        $q->where('external_id', $externalId);
                    } else {
                        $q->where('dedup_hash', $row['dedup_hash'] ?? null);
                    }
                })
                ->first();

            if ($existing === null) {
                throw $e;
            }

            return null;
        }
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'duplicate')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'unique violation');
    }
}
