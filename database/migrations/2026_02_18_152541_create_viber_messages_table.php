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
     * Таблица для сообщений Viber с поддержкой специфичных типов контента.
     */
    public function up(): void
    {
        Schema::create('viber_messages', function (Blueprint $table) {
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
            
            // Тип сообщения Viber
            $table->string('message_type')->default('text'); // text, picture, video, file, location, contact, sticker, etc.
            
            // Медиа файлы
            $table->string('media_url')->nullable();
            $table->string('media_file_name')->nullable();
            $table->string('media_mime_type')->nullable();
            $table->bigInteger('media_file_size')->nullable();
            $table->string('media_thumbnail_url')->nullable();
            
            // Видео
            $table->integer('video_duration')->nullable();
            
            // Локация
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Контакты
            $table->json('contact_data')->nullable();
            
            // Стикеры
            $table->string('sticker_id')->nullable();
            
            // URL в сообщениях
            $table->json('urls')->nullable();
            
            // Оригинальные данные из экспорта
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
        Schema::dropIfExists('viber_messages');
    }
};
