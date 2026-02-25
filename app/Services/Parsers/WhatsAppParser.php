<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WhatsAppParser extends AbstractParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public const PARSER_CORRESPONDING_MESSAGE_MODEL = WhatsAppMessage::class;

    /**
     * Паттерн для строки сообщения WhatsApp:
     * 22.12.2025, 11:27 - Имя: Текст сообщения
     */
    private const MESSAGE_PATTERN = '/^(\d{2}\.\d{2}\.\d{4}),\s(\d{2}:\d{2})\s-\s([^:]+):\s?(.*)$/u';

    /**
     * Паттерн для системных сообщений (шифрование, добавление медиа)
     */
    private const SYSTEM_PATTERN = '/^(\d{2}\.\d{2}\.\d{4}),\s(\d{2}:\d{2})\s-\s(.+)$/u';

    /**
     * @inheritdoc
     */
    public function parse(string $path): ConversationImportDTO
    {
        $content = file_get_contents($path);

        if ($content === false) {
            Log::error('WhatsAppParser: cannot read file', ['path' => $path]);
            throw new RuntimeException("Cannot read file: {$path}");
        }

        $lines = explode("\n", $content);
        $lines = array_filter($lines, fn ($line) => trim($line) !== '');

        if (empty($lines)) {
            Log::warning('WhatsAppParser: empty file', ['path' => $path]);

            return new ConversationImportDTO([], []);
        }

        try {
            [$conversationData, $participants] = $this->extractConversationData($lines);
            $messages = $this->parseMessages($lines, $participants);

            return new ConversationImportDTO($conversationData, $messages);
        } catch (Throwable $e) {
            Log::error('WhatsAppParser: unexpected error', [
                'path'  => $path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new RuntimeException('Failed to parse WhatsApp export: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Извлекает данные о переписке и определяет участников
     *
     * @param array $lines
     *
     * @return array{0: array<string, mixed>, 1: array<string>}
     */
    private function extractConversationData(array $lines): array
    {
        $participants = [];
        $contactName  = null;
        $phoneNumber  = null;

        foreach ($lines as $line) {
            if (preg_match(self::MESSAGE_PATTERN, $line, $matches)) {
                $sender = trim($matches[3]);

                // Определяем, является ли отправитель контактом или номером
                if (preg_match('/^\+?\d[\d\s\-]{7,}\d$/', $sender)) {
                    // Это номер телефона
                    $phoneNumber = $this->normalizePhoneNumber($sender);
                } else {
                    // Это имя контакта
                    $contactName ??= $sender;
                }

                $participants[$sender] = true;
            }
        }

        // Определяем название переписки: приоритет у имени контакта
        $title = $contactName ?? $phoneNumber ?? 'WhatsApp chat';

        // Создаём account_name для MessengerAccount
        $accountName = $contactName
            ? "WhatsApp: {$contactName}"
            : ($phoneNumber
                ? "WhatsApp: {$phoneNumber}"
                : 'WhatsApp');

        $conversationData = [
            'external_id'  => $phoneNumber, // Используем телефон как external_id если есть
            'title'        => $title,
            'participants' => array_keys($participants),
            'account_name' => $accountName,
            'account_meta' => [
                'phone_number' => $phoneNumber,
                'contact_name' => $contactName,
                'type'         => 'personal_chat',
            ],
        ];

        return [$conversationData, array_keys($participants)];
    }

    /**
     * Парсит сообщения из строк
     *
     * @param array $lines
     * @param array $participants
     *
     * @return array<array-key, array<string, mixed>>
     */
    private function parseMessages(array $lines, array $participants): array
    {
        $messages       = [];
        $currentMessage = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Пытаемся распарсить как новое сообщение
            if (preg_match(self::MESSAGE_PATTERN, $line, $matches)) {
                // Сохраняем предыдущее сообщение, если оно было
                if ($currentMessage) {
                    $messages[] = $this->finalizeMessage($currentMessage);
                }

                $date   = $matches[1];
                $time   = $matches[2];
                $sender = trim($matches[3]);
                $text   = trim($matches[4]);

                $currentMessage = [
                    'external_id'        => null, // У WhatsApp нет ID сообщений
                    'sender_name'        => $sender,
                    'sender_external_id' => $this->getSenderId($sender, $participants),
                    'sent_at'            => $this->parseDateTime($date, $time),
                    'text'               => $text
                        ?: null,
                    'message_type'       => 'text',
                    'raw'                => ['line' => $line],
                ];

                // Проверяем на медиа-файлы
                if (str_contains($text, '(файл добавлен)') || str_contains($line, '‎IMG-')) {
                    $currentMessage['message_type'] = 'media';
                    $currentMessage['media_file']   = $this->extractMediaFile($text);
                }

            } elseif (preg_match(self::SYSTEM_PATTERN, $line, $matches)) {
                // Системное сообщение
                if ($currentMessage) {
                    $messages[]     = $this->finalizeMessage($currentMessage);
                    $currentMessage = null;
                }

                $date = $matches[1];
                $time = $matches[2];
                $text = trim($matches[3]);

                $messages[] = [
                    'external_id'        => null,
                    'sender_name'        => 'System',
                    'sender_external_id' => null,
                    'sent_at'            => $this->parseDateTime($date, $time),
                    'text'               => $text,
                    'message_type'       => 'system',
                    'raw'                => ['line' => $line],
                ];

            } elseif ($currentMessage) {
                // Это продолжение предыдущего сообщения (многострочный текст)
                $currentMessage['text'] .= "\n" . $line;
            }
        }

        // Добавляем последнее сообщение
        if ($currentMessage) {
            $messages[] = $this->finalizeMessage($currentMessage);
        }

        return $messages;
    }

    /**
     * Финальная обработка сообщения перед сохранением
     */
    private function finalizeMessage(array $message): array
    {
        // Очищаем текст от маркеров медиа
        if ($message['message_type'] === 'media') {
            $message['text'] = preg_replace('/\s*\(файл добавлен\)$/', '', $message['text']);
            $message['text'] = preg_replace('/^‎/', '', $message['text']);
        }

        return $message;
    }

    /**
     * Парсит дату и время из формата WhatsApp
     */
    private function parseDateTime(string $date, string $time): Carbon
    {
        // Формат: 22.12.2025, 11:27
        return Carbon::createFromFormat('d.m.Y H:i', "{$date} {$time}");
    }

    /**
     * Нормализует номер телефона
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Удаляем все кроме цифр и плюса
        $phone = preg_replace('/[^\d+]/', '', $phone);

        return $phone;
    }

    /**
     * Извлекает имя файла из текста сообщения
     */
    private function extractMediaFile(string $text): ?string
    {
        if (preg_match('/‎?([^\/\\\]+\.\w+)/u', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Получает ID отправителя (для совместимости с другими парсерами)
     */
    private function getSenderId(string $sender, array $participants): ?string
    {
        // Для WhatsApp используем сам sender как ID
        return $sender;
    }
}
