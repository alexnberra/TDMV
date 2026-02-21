<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationTimeline extends Model
{
    use HasFactory;

    protected $table = 'application_timeline';

    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'event_type',
        'description',
        'performed_by',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'application_id',
            'event_type',
            'description',
            'performed_by',
            'metadata',
            'created_at',
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (ApplicationTimeline $timeline): void {
            if (! $timeline->created_at) {
                $timeline->created_at = now();
            }
        });
    }
}
