<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('stored_path', 512)->nullable();
            $table->string('export_path', 512)->nullable();
            $table->string('media_type', 32)->nullable();
            $table->string('mime_type', 128)->nullable();
            $table->string('original_filename', 512)->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'stored_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};
