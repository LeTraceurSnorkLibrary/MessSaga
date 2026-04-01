<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeDTO;

interface ImportStrategyInterface
{
    /**
     * @return ImportModeDTO|null
     */
    public function getImportMode(): ?ImportModeDTO;

    /**
     * Determine import strategy depending on import mode
     *
     * @param MessengerAccount $account
     * @param array            $conversationData
     *
     * @return Conversation|null
     */
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData
    ): ?Conversation;

    /**
     * @return string
     */
    public function getName(): string;
}
