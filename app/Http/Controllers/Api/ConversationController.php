<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessConversationMediaUpload;
use App\Models\Conversation;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\FilenameSanitizer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Teapot\StatusCode\Http;

class ConversationController extends Controller
{
    /**
     * @param ParserRegistry $parserRegistry
     */
    public function __construct(
        private readonly ParserRegistry $parserRegistry
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
            ->orderBy('sent_at')
            ->get(['id', 'sender_name', 'sent_at', 'text', 'message_type', 'attachment_export_path', 'attachment_stored_path']);

        // Формируем ответ для фронта:
        // - attachment_url: URL для отображения/скачивания (если файл загружен)
        // - is_media_without_file: true = медиа-сообщение, но файла нет → показать «Медиа-вложение не загружено» и опционально attachment_export_path (имя файла из экспорта)
        $messages = $messages->map(function ($msg) use ($conversation) {
            $item = $msg->toArray();
            $item['attachment_url'] = $msg->attachment_stored_path
                ? route('api.conversations.messages.attachment', [
                    'conversation' => $conversation->id,
                    'messageId' => $msg->id,
                ])
                : null;
            $item['is_media_without_file'] = $this->isMediaMessageType($msg->message_type) && !$msg->attachment_stored_path;
            return $item;
        });

        return response()->json($messages);
    }

    private function isMediaMessageType(?string $messageType): bool
    {
        if ($messageType === null || $messageType === '') {
            return false;
        }
        $mediaTypes = ['photo', 'voice_message', 'video_message', 'media', 'sticker', 'document', 'gif', 'video'];
        return in_array(strtolower($messageType), $mediaTypes, true);
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

        $parser = $this->parserRegistry->get($conversation->messengerAccount->type);
        $message = $parser->getMessagesRelation($conversation)->find($messageId);
        if (!$message || !$message->attachment_stored_path) {
            abort(Http::NOT_FOUND);
        }

        if (!Storage::exists($message->attachment_stored_path)) {
            abort(Http::NOT_FOUND);
        }

        $mime = \Illuminate\Support\Facades\File::mimeType(Storage::path($message->attachment_stored_path));
        $mime = $mime ?: 'application/octet-stream';
        $filename = FilenameSanitizer::sanitize(basename($message->attachment_stored_path));

        return response()->streamDownload(
            function () use ($message) {
                $stream = Storage::readStream($message->attachment_stored_path);
                if (is_resource($stream)) {
                    fpassthru($stream);
                    fclose($stream);
                }
            },
            $filename,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            ]
        );
    }

    /**
     * Догрузка медиа в существующую переписку: загрузка архива, сопоставление по attachment_export_path.
     *
     * @param Request      $request
     * @param Conversation $conversation
     *
     * @return JsonResponse
     */
    public function uploadMedia(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);

        $request->validate([
            'file' => 'required|file|mimes:zip|max:102400',
        ]);

        $path = $request->file('file')->store('chat_imports');

        ProcessConversationMediaUpload::dispatch(
            userId: $request->user()->id,
            conversationId: $conversation->id,
            path: $path,
        );

        return response()->json([
            'status'  => 'queued',
            'message' => 'Догрузка медиа поставлена в очередь',
        ]);
    }
}
