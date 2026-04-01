<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MessageTypes\TelegramMessageTypesEnum;

use App\Models\MessageTypes\TelegramMessageTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(TelegramMessageTypesEnum::class, 'fromExportType')]
final class FromExportTypeTest extends TestCase
{
    public function test_maps_known_export_type_strings(): void
    {
        $this->assertSame(
            TelegramMessageTypesEnum::MESSAGE,
            TelegramMessageTypesEnum::fromExportType('message')
        );
        $this->assertSame(
            TelegramMessageTypesEnum::SERVICE,
            TelegramMessageTypesEnum::fromExportType('service')
        );
        $this->assertSame(
            TelegramMessageTypesEnum::BOT_SERVICE,
            TelegramMessageTypesEnum::fromExportType('bot_service')
        );
    }

    public function test_maps_unknown_export_type_to_unknown(): void
    {
        $this->assertSame(
            TelegramMessageTypesEnum::UNKNOWN,
            TelegramMessageTypesEnum::fromExportType('edited_message')
        );
        $this->assertSame(
            TelegramMessageTypesEnum::UNKNOWN,
            TelegramMessageTypesEnum::fromExportType('')
        );
    }
}
