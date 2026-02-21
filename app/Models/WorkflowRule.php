<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowRule extends Model
{
    /** @use HasFactory<\Database\Factories\WorkflowRuleFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'tribe_id',
        'key',
        'name',
        'description',
        'is_active',
        'config',
        'last_run_at',
        'run_count',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config' => 'array',
            'last_run_at' => 'datetime',
            'run_count' => 'integer',
        ];
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTribe(Builder $query, int $tribeId): Builder
    {
        return $query->where('tribe_id', $tribeId);
    }
}
