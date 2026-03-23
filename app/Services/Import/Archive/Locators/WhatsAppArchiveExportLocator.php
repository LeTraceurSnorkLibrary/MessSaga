<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Locators;

use App\Services\Import\Archive\Contracts\ArchiveExportLocatorInterface;
use App\Services\Import\Archive\DTO\ArchiveExportLocation;
use App\Services\Import\Archive\Finders\WhatsAppArchiveExportFinder;

/**
 * Locator для WhatsApp-архива.
 * Для WhatsApp media root = корень распакованного архива.
 */
class WhatsAppArchiveExportLocator implements ArchiveExportLocatorInterface
{
    public function __construct(
        private readonly WhatsAppArchiveExportFinder $finder,
    ) {
    }

    public function locate(string $absoluteExtractedRoot): ?ArchiveExportLocation
    {
        $relativeExportPath = $this->finder->findExportFile($absoluteExtractedRoot);
        if ($relativeExportPath === null) {
            return null;
        }

        return new ArchiveExportLocation($relativeExportPath, $absoluteExtractedRoot);
    }
}
