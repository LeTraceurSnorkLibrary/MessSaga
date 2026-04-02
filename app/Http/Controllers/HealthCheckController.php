<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Teapot\StatusCode\Http;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Liveness- и Startup-проба
     *
     * @return JsonResponse
     */
    public function liveness(): JsonResponse
    {
        return response()->json(
            [
                'status' => 'ok',
            ],
            Http::OK
        );
    }

    /**
     * Readiness-проба
     *
     * @return JsonResponse
     */
    public function readiness(): JsonResponse
    {
        $mediaDiskName   = (string)config('filesystems.media_disk', config('filesystems.default'));
        $importsDiskName = (string)config('filesystems.imports_tmp_disk', 'imports_tmp');

        $checks = [
            'media_disk'       => $this->checkDiskWritable($mediaDiskName),
            'imports_tmp_disk' => $this->checkDiskWritable($importsDiskName),
        ];

        $isReady = collect($checks)
            ->every(static fn(array $check): bool => $check['ok'] === true);

        return response()->json(
            [
                'status' => $isReady
                    ? 'ok'
                    : 'fail',
                'checks' => $checks,
            ],
            $isReady
                ? Http::OK
                : Http::SERVICE_UNAVAILABLE
        );
    }

    /**
     * @return array{
     *     ok: bool,
     *     disk: string,
     *     error: string|null
     * }
     */
    private function checkDiskWritable(string $diskName): array
    {
        $probePath = sprintf(
            'healthchecks/%s-%s.txt',
            now()->format('YmdHis'),
            Str::uuid()->toString()
        );

        try {
            $disk    = Storage::disk($diskName);
            $written = $disk->put($probePath, 'ok');
            if ($written !== true) {
                return [
                    'ok'    => false,
                    'disk'  => $diskName,
                    'error' => 'write_failed',
                ];
            }

            $disk->delete($probePath);

            return [
                'ok'    => true,
                'disk'  => $diskName,
                'error' => null,
            ];
        } catch (Throwable $e) {
            return [
                'ok'    => false,
                'disk'  => $diskName,
                'error' => $e->getMessage(),
            ];
        }
    }
}
