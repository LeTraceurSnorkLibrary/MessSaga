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
     * @var ImportModeDTO
     */
    protected ImportModeDTO $importMode;

    /**
     * @param ImportModeDTO $importMode
     *
     * @return $this
     */
    public function setImportMode(ImportModeDTO $importMode): static
    {
        $this->importMode = $importMode;

        return $this;
    }

    /**
     * @param MessengerAccount $account
     * @param array            $conversationData
     *
     * @return Conversation|null
     */
    public function resolveConversation(
        MessengerAccount $account,
        array            $conversationData
    ): ?Conversation {
        $importMode = $this->importMode ?? null;
        if (!isset($importMode)) {
            Log::error('SelectImportStrategy used without setting import mode');

            return null;
        }

        $targetConversationId = $importMode->getTargetConversationId();
        if ($targetConversationId === null) {
            Log::warning('SelectImportStrategy not properly configured');

            return null;
        }

        $userId       = $importMode->getUserId();
        $conversation = Conversation::where('id', $targetConversationId)
            ->whereHas('messengerAccount', fn ($q) => $q->where('user_id', $userId))
            ->first();

        if (!$conversation) {
            Log::warning('Selected conversation not found or not owned', [
                'target_id' => $targetConversationId,
                'user_id'   => $userId,
            ]);

            return null;
        }

        return $conversation;
    }
}
