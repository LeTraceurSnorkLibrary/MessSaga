<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Tariffs\TariffResource;

use App\Filament\Admin\Resources\Tariffs\TariffResource;
use Filament\Schemas\Schema;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversMethod(TariffResource::class, 'form')]
final class FormTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_configures_non_empty_tariff_form_schema(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->expects($this->once())
            ->method('components')
            ->with($this->callback(static fn(array $components): bool => count($components) === 5))
            ->willReturnSelf();

        $this->assertSame($schema, TariffResource::form($schema));
    }
}
