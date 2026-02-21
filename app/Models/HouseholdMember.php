<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseholdMember extends Model
{
    /** @use HasFactory<\Database\Factories\HouseholdMemberFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'household_id',
        'user_id',
        'relationship_type',
        'is_primary',
        'can_manage_minor_vehicles',
        'is_minor',
        'date_joined',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'can_manage_minor_vehicles' => 'boolean',
            'is_minor' => 'boolean',
            'date_joined' => 'date',
            'metadata' => 'array',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'household_id',
            'user_id',
            'relationship_type',
            'is_primary',
            'can_manage_minor_vehicles',
            'is_minor',
            'date_joined',
            'metadata',
            'created_at',
        ]);
    }
}
