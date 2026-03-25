<?php

declare(strict_types=1);

namespace App\Services\Import\Archives\DTO;

/**
 * Результат подготовки архивного файла к импорту.
 */
final readonly class ArchiveExtractionResult
{
    /**
     * @param string|null $exportFileAbsolutePath Абсолютный путь к найденному export-файлу
     * @param string|null $mediaRootPath         Абсолютный путь к каталогу, где искать медиа
     * @param string|null $extractedDir          Storage-relative временный каталог распаковки (для cleanup)
     */
    public function __construct(
        private ?string $exportFileAbsolutePath,
        private ?string $mediaRootPath,
        private ?string $extractedDir,
    ) {
    }

    /**
     * @param string|null $extractedDir
     *
     * @return self
     */
    public static function notPrepared(?string $extractedDir = null): self
    {
        return new self(null, null, $extractedDir);
    }

    /**
     * Абсолютный путь к экспорт-файлу.
     */
    public function getExportFileAbsolutePath(): ?string
    {
        return $this->exportFileAbsolutePath;
    }

    /**
     * @return string|null
     */
    public function getMediaRootPath(): ?string
    {
        return $this->mediaRootPath;
    }

    /**
     * @return string|null
     */
    public function getExtractedDir(): ?string
    {
        return $this->extractedDir;
    }
}
