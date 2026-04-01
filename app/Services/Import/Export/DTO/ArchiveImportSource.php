<?php

declare(strict_types=1);

namespace App\Services\Import\Export\DTO;

/**
 * Описывает, откуда читать данные импорта после распаковки архива.
 */
final readonly class ArchiveImportSource
{
    /**
     * @param string $exportFileRelativePath Путь до export-файла относительно корня распаковки
     * @param string $mediaRootAbsolutePath  Абсолютный корень поиска медиа-вложений
     */
    public function __construct(
        private string $exportFileRelativePath,
        private string $mediaRootAbsolutePath,
    ) {
    }

    /**
     * @return string
     */
    public function getExportFileRelativePath(): string
    {
        return $this->exportFileRelativePath;
    }

    /**
     * @return string
     */
    public function getMediaRootAbsolutePath(): string
    {
        return $this->mediaRootAbsolutePath;
    }
}
