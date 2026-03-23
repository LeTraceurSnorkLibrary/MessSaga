<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

use RuntimeException;

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
    public function extract(string $storagePath, string $messengerType): array
    {
        throw new RuntimeException('RAR archives are not supported yet.');
    }
}
