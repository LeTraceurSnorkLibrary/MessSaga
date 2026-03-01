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

            // Специфичные для WhatsApp поля
            $table->string('sender_name');
            $table->string('sender_external_id')->nullable();
            $table->timestamp('sent_at');
            $table->text('text')->nullable();
            $table->string('message_type')->default('text'); // text, media, system
            $table->string('media_file')->nullable();

            // Оригинальные данные
            $table->json('raw')->nullable();

            $table->timestamps();

            // Индексы
            $table->index('sent_at');
            $table->index('sender_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
