<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\ExportFile;

/**
 * Базовая реализация finder'а с общей рекурсивной навигацией по дереву файлов.
 *
 * Наследники описывают только правила фильтрации нужного export-файла.
 */
abstract class AbstractExportFileLocator implements ExportFileLocatorInterface
{
    /**
     * @param string $absoluteDir
     * @param string $exactName
     *
     * @return string|null
     */
    protected function findExactName(string $absoluteDir, string $exactName): ?string
    {
        return $this->findRecursive(
            $absoluteDir,
            '',
            static fn (string $name): bool => strcasecmp($name, $exactName) === 0
        );
    }

    protected function findFirstByExtension(string $absoluteDir, string $extension): ?string
    {
        $normalizedExtension = '.' . ltrim(strtolower($extension), '.');

        return $this->findRecursive(
            $absoluteDir,
            '',
            static fn (string $name): bool => str_ends_with(strtolower($name), $normalizedExtension)
        );
    }

    protected function findFirstTxtContaining(string $absoluteDir, string $needle): ?string
    {
        $normalizedNeedle = strtolower($needle);

        return $this->findRecursive(
            $absoluteDir,
            '',
            static function (string $name) use ($normalizedNeedle): bool {
                $lower = strtolower($name);

                return str_ends_with($lower, '.txt') && str_contains($lower, $normalizedNeedle);
            }
        );
    }

    protected function findRecursive(string $absoluteDir, string $relativePrefix, callable $predicate): ?string
    {
        if (!is_dir($absoluteDir)) {
            return null;
        }

        $sep   = DIRECTORY_SEPARATOR;
        $items = @scandir($absoluteDir);
        if ($items === false) {
            return null;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $absoluteDir . $sep . $item;
            if (is_file($full) && $predicate($item)) {
                return $relativePrefix !== ''
                    ? $relativePrefix . '/' . $item
                    : $item;
            }
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $absoluteDir . $sep . $item;
            if (is_dir($full)) {
                $prefix = $relativePrefix !== ''
                    ? $relativePrefix . '/' . $item
                    : $item;
                $found  = $this->findRecursive($full, $prefix, $predicate);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
