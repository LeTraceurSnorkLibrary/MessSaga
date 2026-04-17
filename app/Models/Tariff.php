<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'label',
        'price',
        'max_storage_mb',
        'max_media_files_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price'                 => 'decimal:2',
            'max_storage_mb'        => 'integer',
            'max_media_files_count' => 'integer',
        ];
    }
}
