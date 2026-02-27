<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeDTO;

interface ImportStrategyInterface
{
    /**
     * Determine import strategy depending on import mode
     *
     * @param MessengerAccount $account          Аккаунт мессенджера
     * @param array            $conversationData Данные переписки из парсера
     * @param int              $userId           ID пользователя
     * @param ImportModeDTO    $mode             Параметры режима импорта
     *
     * @return Conversation|null
     */
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData,
        int              $userId,
        ImportModeDTO    $mode
    ): ?Conversation;

    /**
     * Возвращает название режима
     */
    public function getName(): string;
}
