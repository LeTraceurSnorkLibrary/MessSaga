<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Preparations;

use App\Services\Import\Preparations\MessagePreparer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessagePreparer::class)]
final class MessagePreparerTest extends TestCase
{
    public function test_returns_empty_string_for_empty_text(): void
    {
        $service = new MessagePreparer();

        $this->assertSame('', $service->prepare(''));
    }

    public function test_prepare_is_equivalent_to_escape_html(): void
    {
        $service = new MessagePreparer();
        $raw     = 'x';

        $this->assertSame($service->escapeHtml($raw), $service->prepare($raw));
    }

    public function test_escapes_html_special_characters(): void
    {
        $service = new MessagePreparer();

        $prepared = $service->prepare('a < b && c > d "quote"');

        $this->assertSame('a &lt; b &amp;&amp; c &gt; d &quot;quote&quot;', $prepared);
    }

    public function test_does_not_wrap_urls_in_anchor_tags(): void
    {
        $service = new MessagePreparer();

        $prepared = $service->prepare('go to https://example.com/path?a=1&b=2 now');

        $this->assertSame(
            'go to https://example.com/path?a=1&amp;b=2 now',
            $prepared
        );
    }
}
