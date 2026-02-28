<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeEnum;

class AutoImportStrategy extends AbstractImportStrategy implements ImportStrategyInterface
{
    /**
     * @inheritdoc
     */
    public const IMPORT_STRATEGY_NAME = ImportModeEnum::AUTO->value;

    /**
     * @inheritdoc
     */
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData
    ): ?Conversation {
        return Conversation::updateOrCreate(
            [
                'messenger_account_id' => $account->id,
                'external_id'          => $conversationData['external_id'] ?? null,
            ],
            [
                'title'        => $conversationData['title'] ?? 'Unknown chat',
                'participants' => $conversationData['participants'] ?? [],
            ]
        );
    }
}
