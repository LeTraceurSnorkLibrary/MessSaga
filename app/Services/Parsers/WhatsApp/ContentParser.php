<?php

declare(strict_types=1);

namespace App\Services\Parsers\WhatsApp;

use App\Utils\PhoneUtil;

/**
 * Парсит контент WhatsApp-файла в сырые данные
 * Отвечает за преобразование строк в структуры данных
 */
class ContentParser
{
    /**
     * RegExp for general messages
     */
    private const MESSAGE_PATTERN = '/^(\d{2}\.\d{2}\.\d{4}), (\d{2}:\d{2}) - ([^:]+): ?(.*)$/u';

    /**
     * RegExp for system messages
     */
    private const SYSTEM_PATTERN = '/^(\d{2}\.\d{2}\.\d{4}), (\d{2}:\d{2}) - (.+)$/u';

    /**
     * @param PhoneUtil $phoneUtil
     */
    public function __construct(private readonly PhoneUtil $phoneUtil)
    {
    }

    /**
     * @param string $line
     *
     * @return array|null
     */
    public function parseMessageLine(string $line): ?array
    {
        if (!preg_match(self::MESSAGE_PATTERN, $line, $matches)) {
            return null;
        }

        return [
            'date'      => $matches[1],
            'time'      => $matches[2],
            'sender'    => trim($matches[3]),
            'firstLine' => trim($matches[4]),
        ];
    }

    /**
     * Process group that is a system message
     *
     * @param string $line
     *
     * @return array|null
     */
    public function parseSystemLine(string $line): ?array
    {
        if (!preg_match(self::SYSTEM_PATTERN, $line, $matches)) {
            return null;
        }

        /**
         * Проверяем, что это системное сообщение (нет двоеточия после отправителя)
         */
        if (str_contains($matches[3], ': ')) {
            return null;
        }

        return [
            'date' => $matches[1],
            'time' => $matches[2],
            'text' => trim($matches[3]),
        ];
    }

    /**
     * @param string $line
     *
     * @return string|null
     */
    public function parseSender(string $line): ?string
    {
        $data = $this->parseMessageLine($line);

        return $data['sender'] ?? null;
    }

    /**
     * @param array $lines
     *
     * @return array
     */
    public function parseParticipants(array $lines): array
    {
        $participants = [];
        foreach ($lines as $line) {
            $sender = $this->parseSender($line);
            if ($sender) {
                $participants[] = $sender;
            }
        }

        return $participants;
    }

    /**
     * @param array $lines
     * @param array $participants
     *
     * @return array
     */
    public function parseConversationData(array $lines, array $participants): array
    {
        $contactName = null;
        $phoneNumber = null;

        foreach ($lines as $line) {
            $sender = $this->parseSender($line);
            if (!$sender) {
                continue;
            }

            $isPhoneNumber = $this->phoneUtil->validator()->isPhoneNumber($sender);
            if ($isPhoneNumber) {
                $phoneNumber = $this->phoneUtil->formatter()::international($sender);
                $contactName = $phoneNumber;
                break;
            } else {
                $contactName = $sender;
            }
        }

        $title       = $contactName ?? $phoneNumber ?? 'WhatsApp chat';
        $accountName = $contactName
            ? "WhatsApp: {$contactName}"
            : ($phoneNumber
                ? "WhatsApp: {$phoneNumber}"
                : 'Неопознанный контакт');

        return [
            'external_id'  => $phoneNumber,
            'title'        => $title,
            'participants' => $participants,
            'account_name' => $accountName,
            'account_meta' => [
                'phone_number' => $phoneNumber,
                'contact_name' => $contactName,
                'type'         => 'personal_chat',
            ],
        ];
    }

    /**
     * @param string $line
     *
     * @return LineTypeEnum
     */
    public function detectLineType(string $line): LineTypeEnum
    {
        if ($this->parseMessageLine($line) !== null) {
            return LineTypeEnum::MESSAGE;
        }

        if ($this->parseSystemLine($line) !== null) {
            return LineTypeEnum::SYSTEM;
        }

        return LineTypeEnum::CONTINUATION;
    }
}
