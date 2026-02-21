<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tribe_id',
        'name',
        'address',
        'phone',
        'email',
        'hours',
        'is_active',
        'latitude',
        'longitude',
        'current_wait_time',
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'array',
            'is_active' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApiPublic(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'name',
            'address',
            'phone',
            'email',
            'hours',
            'is_active',
            'latitude',
            'longitude',
            'current_wait_time',
            'updated_at',
        ]);
    }
}
