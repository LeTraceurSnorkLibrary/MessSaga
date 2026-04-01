<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Models\MediaTypes\SupportedMediaTypesEnum;
use App\Models\Message;
use App\Services\Import\DTO\PreparedMessageRowResult;
use App\Services\Media\MediaFileStorageService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class MessagePreparationService
{
    /**
     * @param MediaFileStorageService $mediaFileStorageService
     */
    public function __construct(
        private readonly MediaFileStorageService $mediaFileStorageService
    ) {
    }

    /**
     * @param mixed $externalId
     *
     * @return string|null
     */
    public function normalizeExternalId(mixed $externalId): ?string
    {
        if (!is_scalar($externalId)) {
            return null;
        }

        $normalized = trim((string)$externalId);

        return $normalized !== ''
            ? $normalized
            : null;
    }

    /**
     * @param array<string, mixed> $message
     *
     * @return string
     */
    public function buildDeduplicationHash(array $message): string
    {
        $sentAt = $message['sent_at'] ?? '';
        if ($sentAt instanceof Carbon) {
            $sentAtFormatted = $sentAt->format('Y-m-d H:i:s');
        } elseif (is_string($sentAt)) {
            try {
                $sentAtFormatted = Carbon::parse($sentAt)->format('Y-m-d H:i:s');
            } catch (Exception) {
                $sentAtFormatted = $sentAt;
            }
        } else {
            $sentAtFormatted = (string)$sentAt;
        }

        return hash(
            'sha256',
            $sentAtFormatted
            . ($message['text'] ?? '')
            . ($message['sender_name'] ?? '')
            . ($message['sender_external_id'] ?? '')
        );
    }

    /**
     * @param string|null          $mediaRootPath
     * @param array<string, mixed> $message
     * @param int                  $conversationId
     *
     * @return string|null
     */
    public function copyAttachmentForMessage(
        ?string $mediaRootPath,
        array   $message,
        int     $conversationId
    ): ?string {
        if ($mediaRootPath === null || empty($message['attachment_export_path'])) {
            return null;
        }

        return $this->mediaFileStorageService->copyForConversation(
            $mediaRootPath,
            (string)$message['attachment_export_path'],
            $conversationId
        );
    }

    /**
     * @param array<string, mixed>  $message
     * @param int                   $conversationId
     * @param class-string<Message> $messageModelClass
     * @param string|null           $attachmentStoredPath
     *
     * @return PreparedMessageRowResult
     */
    public function prepareMessageRowForInsert(
        array   $message,
        int     $conversationId,
        string  $messageModelClass,
        ?string $attachmentStoredPath = null
    ): PreparedMessageRowResult {
        $exportRaw        = $message['attachment_export_path'] ?? null;
        $exportNormalized = $this->normalizeExportPath($exportRaw);

        unset($message['attachment_export_path'], $message['attachment_stored_path']);

        $text = $message['text'] ?? null;

        $sentAt = $message['sent_at'] ?? null;
        if ($sentAt instanceof Carbon) {
            $sentAt = $sentAt->format('Y-m-d H:i:s');
        }

        $row = array_merge($message, [
            'conversation_id' => $conversationId,
            'sent_at'         => $sentAt,
            'text'            => $text
                ? Crypt::encryptString($text)
                : null,
        ]);

        /**
         * @var Model $model
         */
        $model   = $messageModelClass::make();
        $allowed = $model->getFillable();
        $row     = array_merge(
            array_fill_keys($allowed, null),
            array_intersect_key($row, array_flip($allowed))
        );

        $mediaPayload = null;
        if ($exportNormalized !== null || $attachmentStoredPath !== null) {
            $mime = null;
            if ($attachmentStoredPath !== null && Storage::exists($attachmentStoredPath)) {
                $mime = Storage::mimeType($attachmentStoredPath);
            }
            $mediaPayload = [
                'stored_path'       => $attachmentStoredPath,
                'export_path'       => $exportNormalized,
                'media_type'        => SupportedMediaTypesEnum::detect($mime, $exportNormalized)?->value,
                'mime_type'         => $mime,
                'original_filename' => $exportNormalized
                    ? basename(str_replace('\\', '/', $exportNormalized))
                    : ($attachmentStoredPath
                        ? basename($attachmentStoredPath)
                        : null),
            ];
        }

        return new PreparedMessageRowResult($row, $mediaPayload);
    }

    /**
     * @param mixed $exportRaw
     *
     * @return string|null
     */
    private function normalizeExportPath(mixed $exportRaw): ?string
    {
        if (!is_string($exportRaw)) {
            return null;
        }

        $normalized = trim(str_replace('\\', '/', $exportRaw));
        if ($normalized === '') {
            return null;
        }

        return $normalized;
    }
}
