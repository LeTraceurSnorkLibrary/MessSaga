<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;

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
     * Распаковывает архив для импорта (распаковка + извлечение export-файла/корня медиа).
     *
     * @param string $storagePath
     * @param string $messengerType
     *
     * @throws ArchiveExtractionFailedException
     * @return ArchiveExtractionResult Возвращает null, если архив не распакован/не валиден/и т.п.
     */
    public function extract(string $storagePath, string $messengerType): ArchiveExtractionResult;
}
