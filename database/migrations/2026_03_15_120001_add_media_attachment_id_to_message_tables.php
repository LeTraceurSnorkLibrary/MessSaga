<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['telegram_messages', 'whatsapp_messages', 'viber_messages'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $blueprint) use ($tableName) {
                $afterColumn = Schema::hasColumn($tableName, 'attachment_stored_path')
                    ? 'attachment_stored_path'
                    : 'raw';

                $blueprint->foreignId('media_attachment_id')
                    ->nullable()
                    ->after($afterColumn)
                    ->constrained('media_attachments')
                    ->nullOnDelete();
            });
        }

        foreach ($tables as $tableName) {
            $hasExport = Schema::hasColumn($tableName, 'attachment_export_path');
            $hasStored = Schema::hasColumn($tableName, 'attachment_stored_path');
            if (!$hasExport && !$hasStored) {
                continue;
            }

            $select = ['id', 'conversation_id'];
            if ($hasStored) {
                $select[] = 'attachment_stored_path';
            }
            if ($hasExport) {
                $select[] = 'attachment_export_path';
            }

            $rows = DB::table($tableName)
                ->where(function ($q) use ($hasStored, $hasExport) {
                    if ($hasStored && $hasExport) {
                        $q->whereNotNull('attachment_stored_path')
                            ->orWhereNotNull('attachment_export_path');
                    } elseif ($hasStored) {
                        $q->whereNotNull('attachment_stored_path');
                    } else {
                        $q->whereNotNull('attachment_export_path');
                    }
                })
                ->get($select);

            foreach ($rows as $row) {
                $storedPath = $hasStored ? $row->attachment_stored_path : null;
                $exportPath = $hasExport ? $row->attachment_export_path : null;

                $mime = null;
                if (!empty($storedPath)) {
                    $full = storage_path('app/private/' . ltrim((string)$storedPath, '/'));
                    if (is_file($full)) {
                        $mime = @mime_content_type($full) ?: null;
                    }
                }

                $mediaId = DB::table('media_attachments')->insertGetId([
                    'conversation_id'   => $row->conversation_id,
                    'stored_path'       => $storedPath,
                    'export_path'       => $exportPath,
                    'media_type'        => SupportedMediaTypesEnum::detect($mime, $exportPath)?->value,
                    'mime_type'         => $mime,
                    'original_filename' => $storedPath
                        ? basename((string)$storedPath)
                        : ($exportPath ? basename((string)$exportPath) : null),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                DB::table($tableName)->where('id', $row->id)->update(['media_attachment_id' => $mediaId]);
            }
        }

        foreach ($tables as $tableName) {
            $hasExport = Schema::hasColumn($tableName, 'attachment_export_path');
            $hasStored = Schema::hasColumn($tableName, 'attachment_stored_path');
            if (!$hasExport && !$hasStored) {
                continue;
            }

            $toDrop = array_values(array_filter([
                $hasExport ? 'attachment_export_path' : null,
                $hasStored ? 'attachment_stored_path' : null,
            ]));

            Schema::table($tableName, function (Blueprint $blueprint) use ($toDrop) {
                $blueprint->dropColumn($toDrop);
            });
        }
    }

    public function down(): void
    {
        $tables = ['telegram_messages', 'whatsapp_messages', 'viber_messages'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $blueprint) {
                $blueprint->string('attachment_export_path', 512)->nullable()->after('raw');
                $blueprint->string('attachment_stored_path', 512)->nullable()->after('attachment_export_path');
            });
        }

        foreach ($tables as $tableName) {
            $rows = DB::table($tableName)->whereNotNull('media_attachment_id')->get();
            foreach ($rows as $row) {
                $media = DB::table('media_attachments')->where('id', $row->media_attachment_id)->first();
                if ($media) {
                    DB::table($tableName)->where('id', $row->id)->update([
                        'attachment_export_path' => $media->export_path,
                        'attachment_stored_path' => $media->stored_path,
                    ]);
                }
            }
        }

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['media_attachment_id']);
                $blueprint->dropColumn('media_attachment_id');
            });
        }
    }
};
