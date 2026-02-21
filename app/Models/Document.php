<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'user_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
        'status',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
        'expiration_date',
        'metadata',
    ];

    protected $appends = ['url', 'human_file_size'];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'expiration_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'application_id',
            'user_id',
            'document_type',
            'file_name',
            'file_path',
            'file_size',
            'mime_type',
            'status',
            'uploaded_at',
            'reviewed_at',
            'reviewed_by',
            'rejection_reason',
            'expiration_date',
            'created_at',
        ]);
    }

    public function getUrlAttribute(): string
    {
        if (config('filesystems.default') === 's3') {
            return Storage::disk('s3')->temporaryUrl($this->file_path, now()->addMinutes(30));
        }

        return Storage::url($this->file_path);
    }

    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
