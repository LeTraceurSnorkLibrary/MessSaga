<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessConversationMediaUpload;
use App\Models\Conversation;
use App\Models\MediaAttachment;
use App\Services\Media\Storage\MediaStorageInterface;
use App\Services\Parsers\ParserRegistry;
use App\Support\FilenameSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Teapot\StatusCode\Http;

class ConversationController extends Controller
{
    /**
     * @param ParserRegistry        $parserRegistry
     * @param MediaStorageInterface $mediaStorage
     */
    public function __construct(
        private readonly ParserRegistry        $parserRegistry,
        private readonly MediaStorageInterface $mediaStorage
    ) {
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $messengerType = $request->string('messenger')->toString();

        $query = Conversation::query()
            ->whereHas('messengerAccount', function (Builder $q) use ($request, $messengerType) {
                $q->where('user_id', $request->user()->id);

                if ($messengerType) {
                    $q->where('type', $messengerType);
                }
            })
            ->with('messengerAccount');

        $conversations = $query
            ->orderByDesc('id')
            ->get()
            ->map(function (Conversation $conversation) {
                $messengerType = $conversation->messengerAccount->type;

                /**
                 * Получаем парсер из реестра
                 */
                $parser = $this->parserRegistry->get($messengerType);

                /**
                 * Используем метод парсера для получения последнего сообщения
                 */
                $lastMessage = $parser->getMessagesRelation($conversation)
                    ->latest('sent_at')
                    ->first();

                return [
                    'id'      => $conversation->id,
                    'title'   => $conversation->title,
                    'preview' => $lastMessage?->text,
                    'type'    => $messengerType,
                ];
            });

        return response()->json($conversations);
    }

    /**
     * @param Request      $request
     * @param Conversation $conversation
     *
     * @return JsonResponse
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);

        $messengerType = $conversation->messengerAccount->type;

        /**
         * Получаем парсер из реестра
         */
        $parser = $this->parserRegistry->get($messengerType);

        /**
         * Получаем сообщения через парсер
         */
        $messages = $parser->getMessagesRelation($conversation)
            ->with('mediaAttachment')
            ->orderBy('sent_at')
            ->get(['id', 'sender_name', 'sent_at', 'text', 'message_type', 'media_attachment_id']);

        $messages = $messages->map(function ($msg) use ($conversation) {
            $item = $msg->toArray();
            /**
             * @var MediaAttachment|null $media
             */
            $media         = $msg->mediaAttachment;
            $item['media'] = $media?->toApiArray($conversation->id, $msg->id);

            $mediaStoredPath               = $media?->stored_path ?? '';
            $hasStoredFile                 = $mediaStoredPath !== '';
            $item['is_media_without_file'] = !empty($media) && !$hasStoredFile;

            return $item;
        });

        $messagesHash = md5(
            json_encode(
                $messages,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
                ?: ''
        );

        return response()
            ->json($messages)
            ->header('X-Messages-Hash', $messagesHash);
    }

    /**
     * @param Request      $request
     * @param Conversation $conversation
     *
     * @return Response
     */
    public function destroy(Request $request, Conversation $conversation): Response
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);

        /**
         * Каскадно удалит сообщения за счёт foreign key
         */
        $conversation->delete();

        return response()->noContent();
    }

    /**
     * Отдаёт файл вложения сообщения (медиа). Проверяет доступ пользователя к переписке.
     *
     * @param Request      $request
     * @param Conversation $conversation
     * @param int          $messageId
     *
     * @return Response|StreamedResponse
     */
    public function attachment(Request $request, Conversation $conversation, int $messageId): Response|StreamedResponse
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);

        $parser  = $this->parserRegistry->get($conversation->messengerAccount->type);
        $message = $parser->getMessagesRelation($conversation)
            ->with('mediaAttachment')
            ->find($messageId);

        $storedPath = $message?->mediaAttachment?->stored_path;
        if (!$message || $storedPath === null || $storedPath === '') {
            abort(Http::NOT_FOUND);
        }

        if (!$this->mediaStorage->exists($storedPath)) {
            abort(Http::NOT_FOUND);
        }

        $mime = $message?->mediaAttachment?->mime_type;
        if (!is_string($mime) || $mime === '') {
            $mime = $this->mediaStorage->mimeType($storedPath);
        }
        $mime     = $mime
            ?: 'application/octet-stream';
        $filename = FilenameSanitizer::sanitize(basename($storedPath));

        return response()->streamDownload(
            function () use ($storedPath) {
                $stream = $this->mediaStorage->readStream($storedPath);
                if (is_resource($stream)) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            $filename,
            [
                'Content-Type'        => $mime,
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]
        );
    }

    /**
     * Догрузка медиа в существующую переписку: загрузка архива, сопоставление по export_path в media_attachments.
     *
     * @param Request      $request
     * @param Conversation $conversation
     *
     * @return JsonResponse
     */
    public function uploadMedia(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);
        $user = $request->user();
        $reason = $user->getMediaUploadBlockReason();
        if ($reason !== null) {
            return response()->json([
                'status'  => 'rejected',
                'message' => 'Загрузка медиа недоступна.',
                'reason'  => $reason,
                'quota'   => [
                    'tariff' => $user->tariff()->getName(),
                    'storage' => [
                        'used' => $user->getUsedMediaStorageBytes(),
                        'limit' => $user->tariff()->getMaxStorageBytes(),
                        'remaining' => $user->getRemainingMediaStorageBytes(),
                    ],
                    'files' => [
                        'used' => $user->getUsedMediaFilesCount(),
                        'limit' => $user->tariff()->getMaxMediaFilesCount(),
                        'remaining' => $user->getRemainingMediaFilesCount(),
                    ],
                ],
            ], Http::PAYMENT_REQUIRED);
        }

        $request->validate([
            'file' => 'required|file|mimes:zip|max:262144',
        ]);

        $importsTmpDisk = (string)config('filesystems.imports_tmp_disk', 'imports_tmp');
        $path           = $request->file('file')->store('chat_imports', $importsTmpDisk);

        ProcessConversationMediaUpload::dispatch(
            userId: $user->id,
            conversationId: $conversation->id,
            path: $path,
        );

        return response()->json([
            'status'  => 'queued',
            'message' => 'Догрузка медиа поставлена в очередь',
        ]);
    }
}
