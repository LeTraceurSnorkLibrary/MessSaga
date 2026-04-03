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

    public function test_escapes_html_special_characters(): void
    {
        $service = new MessagePreparer();

        $prepared = $service->prepare('a < b && c > d "quote"');

        $this->assertSame('a &lt; b &amp;&amp; c &gt; d &quot;quote&quot;', $prepared);
    }

    public function test_wraps_http_url_to_anchor(): void
    {
        $service = new MessagePreparer();

        $prepared = $service->prepare('go to https://example.com/path?a=1&b=2 now');

        $this->assertSame(
            'go to <a href="https://example.com/path?a=1&amp;b=2" target="_blank" rel="noopener noreferrer">https://example.com/path?a=1&amp;b=2</a> now',
            $prepared
        );
    }

    public function test_wraps_www_url_and_adds_https_to_href(): void
    {
        $service = new MessagePreparer();

        $prepared = $service->prepare('visit www.example.com');

        $this->assertSame(
            'visit <a href="https://www.example.com" target="_blank" rel="noopener noreferrer">www.example.com</a>',
            $prepared
        );
    }

    public function test_keeps_trailing_punctuation_outside_link(): void
    {
        $service = new MessagePreparer();

        $prepared = $service->prepare('check https://example.com/test, please');

        $this->assertSame(
            'check <a href="https://example.com/test" target="_blank" rel="noopener noreferrer">https://example.com/test</a>, please',
            $prepared
        );
    }
}
