<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;

interface ImportStrategyInterface
{
    /**
     * Determine import strategy depending on import mode
     *
     * @param MessengerAccount $account          Аккаунт мессенджера
     * @param array            $conversationData Данные переписки из парсера
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
