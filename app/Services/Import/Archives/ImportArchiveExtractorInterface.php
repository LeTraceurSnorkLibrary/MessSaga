<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

/**
 * Контракт подготовки импортного архива конкретного формата (zip/rar/...).
 */
interface ImportArchiveExtractorInterface
{
    /**
     * Возвращает true, если preparer поддерживает переданный файл.
     */
    public function supports(string $storagePath): bool;

    /**
     * Распаковывает архив и определяет export-файл + media root.
     *
     * @return array{
     *      path_to_use: ?string,
     *      media_root_path: ?string,
     *      extracted_dir: ?string
     *  }
     */
    public function extract(string $storagePath, string $messengerType): array;
}
