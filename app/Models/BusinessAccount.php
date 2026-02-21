<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tribe_id',
        'owner_user_id',
        'business_name',
        'business_type',
        'tax_id',
        'contact_email',
        'contact_phone',
        'address',
        'tax_exempt',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tax_exempt' => 'boolean',
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

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_account_user')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function fleetVehicles(): HasMany
    {
        return $this->hasMany(FleetVehicle::class);
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'fleet_vehicles')
            ->withPivot(['id', 'assigned_driver_id', 'status', 'added_at', 'metadata', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'owner_user_id',
            'business_name',
            'business_type',
            'tax_id',
            'contact_email',
            'contact_phone',
            'address',
            'tax_exempt',
            'is_active',
            'metadata',
            'created_at',
            'updated_at',
        ]);
    }
}
