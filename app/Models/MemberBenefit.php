<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberBenefit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'tribe_id',
        'benefit_type',
        'status',
        'effective_date',
        'expiration_date',
        'verified_by',
        'verified_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'expiration_date' => 'date',
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'user_id',
            'tribe_id',
            'benefit_type',
            'status',
            'effective_date',
            'expiration_date',
            'verified_by',
            'verified_at',
            'notes',
            'metadata',
            'created_at',
        ]);
    }
}
