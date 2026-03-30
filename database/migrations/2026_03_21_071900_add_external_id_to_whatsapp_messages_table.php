<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ранее whatsapp_messages создавалась без external_id, хотя в модели было значение по умолчанию —
 * при insert Laravel пытался записать несуществующую колонку.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_messages') && !Schema::hasColumn('whatsapp_messages', 'external_id')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->string('external_id')->nullable()->index()->after('conversation_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('whatsapp_messages', 'external_id')) {
            Schema::table('whatsapp_messages', function (Blueprint $table) {
                $table->dropColumn('external_id');
            });
        }
    }
};
