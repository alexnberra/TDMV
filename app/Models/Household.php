<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Household extends Model
{
    /** @use HasFactory<\Database\Factories\HouseholdFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'tribe_id',
        'owner_user_id',
        'household_name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'owner_user_id',
            'household_name',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'zip_code',
            'is_active',
            'metadata',
            'created_at',
            'updated_at',
        ]);
    }
}
