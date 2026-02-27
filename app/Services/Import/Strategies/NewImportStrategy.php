<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeDTO;

class NewImportStrategy implements ImportStrategyInterface
{
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData,
        int              $userId,
        ImportModeDTO    $mode
    ): ?Conversation {
        // Принудительно создаём новую переписку
        return Conversation::create([
            'messenger_account_id' => $account->id,
            'external_id'          => $conversationData['external_id'] ?? null,
            'title'                => $conversationData['title'] ?? 'Unknown chat',
            'participants'         => $conversationData['participants'] ?? [],
        ]);
    }

    public function getName(): string
    {
        return 'new';
    }
}
