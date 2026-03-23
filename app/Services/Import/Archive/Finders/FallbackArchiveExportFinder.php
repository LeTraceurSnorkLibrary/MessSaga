<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Finders;

use App\Services\Import\Archive\Contracts\ArchiveExportFinderInterface;

/**
 * Fallback finder для неизвестного типа мессенджера.
 * Пробует Telegram-правила, затем WhatsApp-правила.
 */
class FallbackArchiveExportFinder implements ArchiveExportFinderInterface
{
    public function __construct(
        private readonly TelegramArchiveExportFinder $telegramFinder,
        private readonly WhatsAppArchiveExportFinder $whatsAppFinder,
    ) {
    }

    public function findExportFile(string $absoluteExtractedRoot): ?string
    {
        return $this->telegramFinder->findExportFile($absoluteExtractedRoot)
            ?? $this->whatsAppFinder->findExportFile($absoluteExtractedRoot);
    }
}
