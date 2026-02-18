<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Таблица для сообщений Telegram с поддержкой специфичных типов контента:
     * стикеры, голосовые сообщения, видео, GIF, сервисные сообщения и т.д.
     */
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();
            
            // Базовые поля (общие для всех мессенджеров)
            $table->string('external_id')->nullable()->index();
            $table->string('sender_name')->nullable();
            $table->string('sender_external_id')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->text('text')->nullable(); // Зашифрованное поле
            
            // Тип сообщения Telegram
            $table->string('message_type')->default('text'); // text, sticker, voice, video, photo, service, etc.
            
            // Специфичные поля для разных типов контента
            $table->string('sticker_id')->nullable(); // ID стикера
            $table->string('sticker_set_name')->nullable(); // Название набора стикеров
            
            $table->integer('voice_duration')->nullable(); // Длительность голосового сообщения в секундах
            $table->string('voice_file_id')->nullable();
            
            $table->string('video_file_id')->nullable();
            $table->integer('video_duration')->nullable();
            $table->string('video_thumbnail_id')->nullable();
            
            $table->string('photo_file_id')->nullable();
            $table->json('photo_sizes')->nullable(); // Разные размеры фото
            
            $table->string('gif_file_id')->nullable();
            $table->string('gif_thumbnail_id')->nullable();
            
            $table->string('document_file_id')->nullable();
            $table->string('document_file_name')->nullable();
            $table->string('document_mime_type')->nullable();
            $table->bigInteger('document_file_size')->nullable();
            
            // Сервисные сообщения (присоединился к группе, покинул группу и т.д.)
            $table->string('service_action')->nullable(); // joined_group, left_group, pinned_message, etc.
            $table->json('service_actor')->nullable(); // Кто выполнил действие
            
            // Пересылка сообщений
            $table->string('forwarded_from_chat_id')->nullable();
            $table->string('forwarded_from_message_id')->nullable();
            $table->string('forwarded_from_name')->nullable();
            
            // Редактирование
            $table->timestamp('edited_at')->nullable();
            
            // Реакции (если есть в экспорте)
            $table->json('reactions')->nullable();
            
            // Оригинальные данные из экспорта (для отладки и будущих расширений)
            $table->json('raw')->nullable();
            
            $table->timestamps();
            
            // Индексы для производительности
            $table->index(['conversation_id', 'sent_at']);
            $table->index(['conversation_id', 'external_id']);
            $table->index('message_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
