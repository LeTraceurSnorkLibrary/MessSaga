<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;

/**
 * Контракт подготовки импортного архива конкретного формата (zip/rar/...).
 */
interface ImportArchiveExtractorInterface
{
    /**
     * Возвращает true, если текущий Extractor может распаковать переданный файл.
     *
     * @param string $storagePath
     *
     * @return bool
     */
    public function supports(string $storagePath): bool;

    /**
     * Распаковывает архив.
     *
     * @param string $storagePath
     * @param string $messengerType
     *
     * @return ArchiveExtractionResult
     */
    public function extract(string $storagePath, string $messengerType): ArchiveExtractionResult;
}
