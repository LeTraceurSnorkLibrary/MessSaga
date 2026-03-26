<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();

            // Как у остальных мессенджеров (дедупликация / будущие идентификаторы из экспорта)
            $table->string('external_id')->nullable()->index();

            // Специфичные для WhatsApp поля
            $table->string('sender_name');
            $table->string('sender_external_id')->nullable();
            $table->timestamp('sent_at');
            $table->text('text')->nullable();
            $table->string('dedup_hash', 64)->nullable();
            $table->string('message_type')->default('text'); // text, media, system

            // Оригинальные данные
            $table->json('raw')->nullable();

            $table->timestamps();

            // Индексы
            $table->index('sent_at');
            $table->index('sender_name');
            $table->unique(['conversation_id', 'external_id']);
            $table->unique(['conversation_id', 'dedup_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
