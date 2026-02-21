<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistantInteraction extends Model
{
    /** @use HasFactory<\Database\Factories\AssistantInteractionFactory> */
    use HasFactory;

    protected $fillable = [
        'tribe_id',
        'user_id',
        'application_id',
        'channel',
        'intent',
        'query_text',
        'response_text',
        'context',
        'response_time_ms',
        'was_helpful',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'metadata' => 'array',
            'response_time_ms' => 'integer',
            'was_helpful' => 'boolean',
        ];
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeForTribe(Builder $query, int $tribeId): Builder
    {
        return $query->where('tribe_id', $tribeId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
