<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeEnum;
use Illuminate\Support\Facades\Log;

class SelectImportStrategy extends AbstractImportStrategy implements ImportStrategyInterface
{
    /**
     * @inheritdoc
     */
    public const IMPORT_STRATEGY_NAME = ImportModeEnum::SELECT->value;

    /**
     * @var int ID of target user
     */
    protected int $userId;

    /**
     * @var int ID of target conversation
     */
    protected int $targetConversationId;

    /**
     * @param int $userId
     *
     * @return static
     */
    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param int $targetConversationId
     *
     * @return $this
     */
    public function setTargetConversationId(int $targetConversationId): static
    {
        $this->targetConversationId = $targetConversationId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData
    ): ?Conversation {
        if (!isset($this->targetConversationId)) {
            Log::warning('Select mode requires target conversation ID', [
                'user_id' => $this->userId,
            ]);

            return null;
        }

        // Используем указанную переписку, проверяем принадлежность
        $conversation = Conversation::where('id', $this->targetConversationId)
            ->whereHas('messengerAccount', fn ($q) => $q->where('user_id', $this->userId))
            ->first();

        if (!$conversation) {
            Log::warning('Selected conversation not found or not owned', [
                'target_id' => $this->targetConversationId,
                'user_id'   => $this->userId,
            ]);

            return null;
        }

        return $conversation;
    }
}
