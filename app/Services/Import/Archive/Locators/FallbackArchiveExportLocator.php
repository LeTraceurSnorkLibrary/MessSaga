<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Locators;

use App\Services\Import\Archive\Contracts\ArchiveExportLocatorInterface;
use App\Services\Import\Archive\DTO\ArchiveExportLocation;

/**
 * Fallback locator для неизвестных мессенджеров.
 */
class FallbackArchiveExportLocator implements ArchiveExportLocatorInterface
{
    public function __construct(
        private readonly TelegramArchiveExportLocator $telegramLocator,
        private readonly WhatsAppArchiveExportLocator $whatsAppLocator,
    ) {
    }

    public function locate(string $absoluteExtractedRoot): ?ArchiveExportLocation
    {
        return $this->telegramLocator->locate($absoluteExtractedRoot)
            ?? $this->whatsAppLocator->locate($absoluteExtractedRoot);
    }
}
