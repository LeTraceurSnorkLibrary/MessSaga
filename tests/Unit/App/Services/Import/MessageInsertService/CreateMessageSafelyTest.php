<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\MessageInsertService;

use App\Models\Message;
use App\Services\Import\MessageInsertService;
use App\Services\Import\MessagePreparationService;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversMethod(MessageInsertService::class, '__construct')]
#[CoversMethod(MessageInsertService::class, 'createMessageSafely')]
#[CoversMethod(MessageInsertService::class, 'isUniqueConstraintViolation')]
final class CreateMessageSafelyTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_returns_created_message_on_successful_create(): void
    {
        $created                           = new FakeInsertMessage();
        FakeInsertMessage::$createBehavior = static fn(array $row): Message => $created;

        $prep = $this->createMock(MessagePreparationService::class);
        $prep->expects($this->never())->method('normalizeExternalId');

        $service = new MessageInsertService($prep);

        $result = $service->createMessageSafely(FakeInsertMessage::class, ['conversation_id' => 1]);

        $this->assertSame($created, $result);
    }

    /**
     * @throws Exception
     */
    public function test_rethrows_non_unique_query_exception(): void
    {
        FakeInsertMessage::$createBehavior = static fn(array $row): Message => throw new QueryException(
            'testing',
            'insert into ...',
            [],
            new RuntimeException('syntax error')
        );

        $prep = $this->createMock(MessagePreparationService::class);
        $prep->expects($this->never())->method('normalizeExternalId');

        $service = new MessageInsertService($prep);

        $this->expectException(QueryException::class);
        $service->createMessageSafely(FakeInsertMessage::class, ['conversation_id' => 1]);
    }

    /**
     * @throws Exception
     */
    public function test_returns_null_when_duplicate_and_existing_found_by_external_id(): void
    {
        FakeInsertMessage::$createBehavior = static fn(array $row): Message => throw new QueryException(
            'testing',
            'insert into ...',
            [],
            new RuntimeException('duplicate key value violates unique constraint')
        );

        $builder                         = new FakeQueryBuilder();
        $builder->firstResult            = new FakeInsertMessage();
        FakeInsertMessage::$queryBuilder = $builder;

        $prep = $this->createMock(MessagePreparationService::class);
        $prep->expects($this->once())
            ->method('normalizeExternalId')
            ->with(' ext-1 ')
            ->willReturn('ext-1');

        $service = new MessageInsertService($prep);

        $result = $service->createMessageSafely(FakeInsertMessage::class, [
            'conversation_id' => 7,
            'external_id'     => ' ext-1 ',
            'dedup_hash'      => 'hash-1',
        ]);

        $this->assertNull($result);
        $this->assertContains(['conversation_id', 7], $builder->calls);
        $this->assertContains(['external_id', 'ext-1'], $builder->calls);
    }

    /**
     * @throws Exception
     */
    public function test_returns_null_when_duplicate_and_existing_found_by_dedup_hash(): void
    {
        FakeInsertMessage::$createBehavior = static fn(array $row): Message => throw new QueryException(
            'testing',
            'insert into ...',
            [],
            new RuntimeException('UNIQUE VIOLATION')
        );

        $builder                         = new FakeQueryBuilder();
        $builder->firstResult            = new FakeInsertMessage();
        FakeInsertMessage::$queryBuilder = $builder;

        $prep = $this->createMock(MessagePreparationService::class);
        $prep->expects($this->once())
            ->method('normalizeExternalId')
            ->with(null)
            ->willReturn(null);

        $service = new MessageInsertService($prep);

        $result = $service->createMessageSafely(FakeInsertMessage::class, [
            'conversation_id' => 8,
            'dedup_hash'      => 'hash-2',
        ]);

        $this->assertNull($result);
        $this->assertContains(['dedup_hash', 'hash-2'], $builder->calls);
    }

    /**
     * @throws Exception
     */
    public function test_rethrows_duplicate_exception_when_existing_message_not_found(): void
    {
        $exception = new QueryException(
            'testing',
            'insert into ...',
            [],
            new RuntimeException('duplicate key')
        );

        FakeInsertMessage::$createBehavior = static fn(array $row): Message => throw $exception;
        FakeInsertMessage::$queryBuilder   = new FakeQueryBuilder();

        $prep = $this->createStub(MessagePreparationService::class);
        $prep->method('normalizeExternalId')->willReturn('ext');

        $service = new MessageInsertService($prep);

        $this->expectExceptionObject($exception);
        $service->createMessageSafely(FakeInsertMessage::class, ['conversation_id' => 1, 'external_id' => 'x']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        FakeInsertMessage::$createBehavior = null;
        FakeInsertMessage::$queryBuilder   = null;
    }
}

final class FakeInsertMessage extends Message
{
    /** @var null|callable(array<string,mixed>): Message */
    public static $createBehavior = null;

    public static ?FakeQueryBuilder $queryBuilder = null;

    protected $table = 'fake_insert_messages';

    public static function create(array $attributes = [])
    {
        if (is_callable(self::$createBehavior)) {
            return (self::$createBehavior)($attributes);
        }

        return new static();
    }

    public static function query()
    {
        return self::$queryBuilder ?? new FakeQueryBuilder();
    }
}

final class FakeQueryBuilder
{
    /** @var array<int, array{0:string,1:mixed}> */
    public array $calls = [];

    public ?Message $firstResult = null;

    public function where($column, $value = null): self
    {
        if (is_callable($column)) {
            $column($this);

            return $this;
        }

        $this->calls[] = [(string)$column, $value];

        return $this;
    }

    public function first(): ?Message
    {
        return $this->firstResult;
    }
}
