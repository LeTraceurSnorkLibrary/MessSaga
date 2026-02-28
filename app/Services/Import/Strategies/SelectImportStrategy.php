<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeDTO;
use App\Services\Import\DTO\ImportModeEnum;
use Illuminate\Support\Facades\Log;

class SelectImportStrategy extends AbstractImportStrategy implements ImportStrategyInterface
{
    /**
     * @inheritdoc
     */
    public const IMPORT_STRATEGY_NAME = ImportModeEnum::SELECT->value;

    /**
     * @inheritdoc
     */
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData,
        int              $userId,
        ImportModeDTO    $mode
    ): ?Conversation {
        // Проверяем, что в режиме select передан ID переписки
        if ($mode->targetConversationId === null) {
            Log::warning('Select mode requires target conversation ID', [
                'user_id' => $userId,
                'mode'    => $mode->getName(),
            ]);

            return null;
        }

        // Используем указанную переписку, проверяем принадлежность
        $conversation = Conversation::where('id', $mode->targetConversationId)
            ->whereHas('messengerAccount', fn ($q) => $q->where('user_id', $userId))
            ->first();

        if (!$conversation) {
            Log::warning('Selected conversation not found or not owned', [
                'target_id' => $mode->targetConversationId,
                'user_id'   => $userId,
            ]);

            return null;
        }

        return $conversation;
    }
}
