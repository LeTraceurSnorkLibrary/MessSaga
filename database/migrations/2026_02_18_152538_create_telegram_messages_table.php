<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $table->string('external_id')->nullable()->index();
            $table->string('sender_name')->nullable();
            $table->string('sender_external_id')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->text('text')->nullable();
            $table->string('dedup_hash', 64)->nullable();
            $table->string('message_type')->default('message'); // type из Telegram export: message/service/...
            $table->json('raw')->nullable();

            $table->timestamps();
            $table->index(['conversation_id', 'sent_at']);
            $table->unique(['conversation_id', 'external_id']);
            $table->unique(['conversation_id', 'dedup_hash']);
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
