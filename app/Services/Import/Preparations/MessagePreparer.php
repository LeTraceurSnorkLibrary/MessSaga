<?php

declare(strict_types=1);

namespace App\Services\Import\Preparations;

use App\Support\MessagePreparator\TailedUrl;
use App\Utils\Formatters\UrlFormatter;

final class MessagePreparer
{
    /**
     * Подготавливает текст сообщения для записи в БД:
     * - Экранирует символы
     * - Оборачивает ссылки в тег <a>
     *
     * @param string $text
     *
     * @return string
     */
    public function prepare(string $text): string
    {
        $escapedText = $this->escapeHtml($text);

        return $this->linkify($escapedText);
    }

    /**
     * Экранирует текст.
     *
     * @param string $text
     *
     * @return string
     */
    public function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Ищет URL и оборачивает их в теги <a>.
     *
     * @param string $escapedText
     *
     * @return string
     */
    public function linkify(string $escapedText): string
    {
        if ($escapedText === '') {
            return '';
        }

        $buffer  = '';
        $offset  = 0;
        $matches = [];

        preg_match_all('/((?:https?:\/\/|www\.)[^\s<>"\']+)/iu', $escapedText, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[1] ?? [] as $match) {
            [$rawUrl, $position] = $match;
            if (!is_string($rawUrl) || !is_int($position)) {
                continue;
            }

            if ($position > $offset) {
                $buffer .= substr($escapedText, $offset, $position - $offset);
            }

            $urlPart = $this->separateUrlAndTail($rawUrl);
            $url     = $urlPart->getUrl();

            /**
             * @TODO: потенциально, можно вынести в шаблон
             */
            $buffer .= sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer nofollow">%2$s</a>%3$s',
                UrlFormatter::normalizeForHref($url),
                $url,
                $urlPart->getTail()
            );

            $offset = $position + strlen($rawUrl);
        }

        if ($offset < strlen($escapedText)) {
            $buffer .= substr($escapedText, $offset);
        }

        return $buffer;
    }

    /**
     * @param string $rawUrl
     *
     * @return TailedUrl
     */
    private function separateUrlAndTail(string $rawUrl): TailedUrl
    {
        $trimmed = rtrim($rawUrl, ".,!?;:");
        $tail    = substr($rawUrl, strlen($trimmed));

        return new TailedUrl(
            $trimmed,
            $tail
                ?: ''
        );
    }
}
