<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use RuntimeException;

/**
 * Реестр парсеров по типу мессенджера.
 * Добавление нового мессенджера = регистрация парсера, без изменений в ImportService.
 */
final class ParserRegistry
{
    /**
     * @var array<string, ParserInterface>
     */
    private array $parsers = [];

    /**
     * @param string          $messengerType
     * @param ParserInterface $parser
     *
     * @return $this
     */
    public function register(string $messengerType, ParserInterface $parser): self
    {
        $this->parsers[$messengerType] = $parser;

        return $this;
    }

    /**
     * @param string $messengerType
     *
     * @return ParserInterface
     */
    public function get(string $messengerType): ParserInterface
    {
        if (!isset($this->parsers[$messengerType])) {
            throw new RuntimeException("Unknown messenger type: {$messengerType}. No parser registered.");
        }

        return $this->parsers[$messengerType];
    }

    /**
     * @param string $messengerType
     *
     * @return bool
     */
    public function has(string $messengerType): bool
    {
        return isset($this->parsers[$messengerType]);
    }
}
