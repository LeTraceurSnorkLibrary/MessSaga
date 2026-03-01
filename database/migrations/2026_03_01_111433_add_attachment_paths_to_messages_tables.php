<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Добавляет поля для хранения пути к медиа из экспорта и пути к сохранённому файлу.
     * attachment_export_path — путь из экспорта (для сопоставления при догрузке).
     * attachment_stored_path — путь в нашем хранилище (null = не загружено).
     */
    public function up(): void
    {
        $tables = ['telegram_messages', 'whatsapp_messages', 'viber_messages'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('attachment_export_path', 512)->nullable()->after('raw');
                $table->string('attachment_stored_path', 512)->nullable()->after('attachment_export_path');
            });
        }
    }

    public function down(): void
    {
        $tables = ['telegram_messages', 'whatsapp_messages', 'viber_messages'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['attachment_export_path', 'attachment_stored_path']);
            });
        }
    }
};
