<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsurancePolicy extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'tribe_id',
        'provider_name',
        'policy_number',
        'effective_date',
        'expiration_date',
        'status',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'expiration_date' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
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
            'vehicle_id',
            'user_id',
            'tribe_id',
            'provider_name',
            'policy_number',
            'effective_date',
            'expiration_date',
            'status',
            'is_verified',
            'verified_at',
            'verified_by',
            'verification_source',
            'metadata',
            'created_at',
        ]);
    }
}
