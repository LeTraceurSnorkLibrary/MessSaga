<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Таблица для сообщений WhatsApp с поддержкой специфичных типов контента:
     * статусы, реакции, пересылки, голосовые заметки и т.д.
     */
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
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
            
            // Тип сообщения WhatsApp
            $table->string('message_type')->default('text'); // text, image, video, audio, document, location, contact, etc.
            
            // Статусы сообщений (отправлено, доставлено, прочитано)
            $table->string('status')->nullable(); // sent, delivered, read
            $table->timestamp('status_updated_at')->nullable();
            
            // Пересылка сообщений
            $table->boolean('is_forwarded')->default(false);
            $table->string('forwarded_from_name')->nullable();
            $table->timestamp('forwarded_at')->nullable();
            
            // Голосовые заметки
            $table->string('voice_note_file_id')->nullable();
            $table->integer('voice_note_duration')->nullable();
            
            // Медиа файлы
            $table->string('media_file_id')->nullable();
            $table->string('media_file_name')->nullable();
            $table->string('media_mime_type')->nullable();
            $table->bigInteger('media_file_size')->nullable();
            $table->string('media_caption')->nullable();
            $table->string('media_thumbnail_id')->nullable();
            
            // Локация
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
            
            // Контакты
            $table->json('contact_data')->nullable(); // Имя, телефон, email и т.д.
            
            // Реакции (лайки, сердечки и т.д.)
            $table->json('reactions')->nullable();
            
            // Упоминания (@username)
            $table->json('mentions')->nullable();
            
            // Цитаты (ответы на сообщения)
            $table->string('quoted_message_id')->nullable();
            $table->text('quoted_text')->nullable();
            
            // Ярлыки/Пометки
            $table->json('labels')->nullable();
            
            // Оригинальные данные из экспорта
            $table->json('raw')->nullable();
            
            $table->timestamps();
            
            // Индексы для производительности
            $table->index(['conversation_id', 'sent_at']);
            $table->index(['conversation_id', 'external_id']);
            $table->index('message_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
