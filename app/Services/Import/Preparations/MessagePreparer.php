<?php

declare(strict_types=1);

namespace App\Services\Import\Preparations;

/**
 * Подготовка текста сообщения для записи в БД: только экранирование HTML.
 * Разметку ссылок делает фронтенд при отображении.
 */
final class MessagePreparer
{
    /**
     * Подготавливает текст сообщения для записи в БД:
     * - Экранирует символы
     *
     * @param string $text
     *
     * @return string
     */
    public function prepare(string $text): string
    {
        return $this->escapeHtml($text);
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
}
