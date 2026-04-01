<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;

/**
 * Заглушка под будущую поддержку RAR-архивов.
 */
class RarImportArchiveExtractor implements ImportArchiveExtractorInterface
{
    /**
     * @inheritdoc
     */
    public function supports(string $storagePath): bool
    {
        return str_ends_with(strtolower($storagePath), '.rar');
    }

    /**
     * @inheritdoc
     */
    public function extract(string $storagePath, string $messengerType): ArchiveExtractionResult
    {
        throw new ArchiveExtractionFailedException('RAR archives are not supported yet.');
    }
}
