<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Media\MediaFileStorageService;

use App\Services\Media\MediaFileStorageService;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversMethod(MediaFileStorageService::class, 'extractCandidateBasenames')]
final class ExtractCandidateBasenamesTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_builds_legacy_candidate_list_without_slashes(): void
    {
        $service = new MediaFileStorageService();
        $method  = new ReflectionClass($service)->getMethod('extractCandidateBasenames');
        $method->setAccessible(true);

        $candidates = $method->invoke($service, '001_ABC_photo.jpg');

        $this->assertSame(
            ['001_ABC_photo.jpg', 'ABC_photo.jpg', 'photo.jpg'],
            $candidates
        );
    }

    /**
     * @throws ReflectionException
     */
    public function test_returns_only_basename_when_path_contains_directories(): void
    {
        $service = new MediaFileStorageService();
        $method  = new ReflectionClass($service)->getMethod('extractCandidateBasenames');
        $method->setAccessible(true);

        $candidates = $method->invoke($service, 'media/sub/file.jpg');

        $this->assertSame(['file.jpg'], $candidates);
    }
}
