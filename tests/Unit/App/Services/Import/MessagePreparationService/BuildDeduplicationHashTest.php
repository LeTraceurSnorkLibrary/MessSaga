<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\MessagePreparationService;

use App\Services\Import\MessagePreparationService;
use App\Services\Media\ImportedMediaResolverService;
use App\Services\Media\Storage\MediaStorageInterface;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversMethod(MessagePreparationService::class, '__construct')]
#[CoversMethod(MessagePreparationService::class, 'buildDeduplicationHash')]
final class BuildDeduplicationHashTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_builds_expected_hash_with_carbon_date(): void
    {
        $service = new MessagePreparationService(
            $this->createStub(ImportedMediaResolverService::class),
            $this->createStub(MediaStorageInterface::class)
        );
        $message = [
            'sent_at'            => Carbon::parse('2026-01-02 03:04:05'),
            'text'               => 'hello',
            'sender_name'        => 'alice',
            'sender_external_id' => '42',
        ];

        $expected = hash('sha256', '2026-01-02 03:04:05helloalice42');

        $this->assertSame($expected, $service->buildDeduplicationHash($message));
    }

    /**
     * @throws Exception
     */
    public function test_uses_raw_string_when_sent_at_is_unparseable(): void
    {
        $service = new MessagePreparationService(
            $this->createStub(ImportedMediaResolverService::class),
            $this->createStub(MediaStorageInterface::class)
        );
        $message = [
            'sent_at'            => 'not-a-date',
            'text'               => 't',
            'sender_name'        => 's',
            'sender_external_id' => 'e',
        ];

        $expected = hash('sha256', 'not-a-datetse');

        $this->assertSame($expected, $service->buildDeduplicationHash($message));
    }
}
