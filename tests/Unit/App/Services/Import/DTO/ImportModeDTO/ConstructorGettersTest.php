<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\DTO\ImportModeDTO;

use App\Services\Import\DTO\ImportModeDTO;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ImportModeDTO::class, '__construct')]
#[CoversMethod(ImportModeDTO::class, 'getMode')]
#[CoversMethod(ImportModeDTO::class, 'getUserId')]
#[CoversMethod(ImportModeDTO::class, 'getTargetConversationId')]
final class ConstructorGettersTest extends TestCase
{
    public function test_returns_constructor_values_with_target_conversation_id(): void
    {
        $dto = new ImportModeDTO('select', 101, 505);

        $this->assertSame('select', $dto->getMode());
        $this->assertSame(101, $dto->getUserId());
        $this->assertSame(505, $dto->getTargetConversationId());
    }

    public function test_returns_null_target_conversation_id_by_default(): void
    {
        $dto = new ImportModeDTO('auto', 33);

        $this->assertSame('auto', $dto->getMode());
        $this->assertSame(33, $dto->getUserId());
        $this->assertNull($dto->getTargetConversationId());
    }
}
