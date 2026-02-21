<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisabilityPlacard extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'tribe_id',
        'vehicle_id',
        'placard_number',
        'placard_type',
        'status',
        'issued_at',
        'expiration_date',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expiration_date' => 'date',
            'approved_at' => 'datetime',
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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'user_id',
            'tribe_id',
            'vehicle_id',
            'placard_number',
            'placard_type',
            'status',
            'issued_at',
            'expiration_date',
            'approved_by',
            'approved_at',
            'rejection_reason',
            'metadata',
            'created_at',
        ]);
    }
}
