<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'id',
    'user_id',
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
])]
class TemporaryUpload extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (TemporaryUpload $temporaryUpload): void {
            if (! $temporaryUpload->getAttribute('id')) {
                $temporaryUpload->id = (string) Str::ulid();
            }
        });
    }
}
