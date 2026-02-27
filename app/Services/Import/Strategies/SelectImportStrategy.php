<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeDTO;
use Illuminate\Support\Facades\Log;

class SelectImportStrategy implements ImportStrategyInterface
{
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData,
        int              $userId,
        ImportModeDTO    $mode
    ): ?Conversation {
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

    public function getName(): string
    {
        return 'select';
    }
}
