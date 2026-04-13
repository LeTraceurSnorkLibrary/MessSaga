<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\UserResource;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Schemas\Schema;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversMethod(UserResource::class, 'form')]
final class FormTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_configures_empty_form_schema(): void
    {
        $schema = $this->createMock(Schema::class);
        $schema->expects($this->once())
            ->method('components')
            ->with([])
            ->willReturnSelf();

        $this->assertSame($schema, UserResource::form($schema));
    }
}
