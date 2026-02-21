<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'tribe_id',
        'vin',
        'plate_number',
        'year',
        'make',
        'model',
        'color',
        'vehicle_type',
        'registration_status',
        'registration_date',
        'expiration_date',
        'title_number',
        'lienholder_name',
        'lienholder_address',
        'is_garaged_on_reservation',
        'mileage',
        'metadata',
    ];

    protected $appends = ['days_until_expiration', 'is_expiring_soon', 'is_expired'];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'expiration_date' => 'date',
            'is_garaged_on_reservation' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function fleetAssignments(): HasMany
    {
        return $this->hasMany(FleetVehicle::class);
    }

    public function businessAccounts(): BelongsToMany
    {
        return $this->belongsToMany(BusinessAccount::class, 'fleet_vehicles')
            ->withPivot(['id', 'assigned_driver_id', 'status', 'added_at', 'metadata', 'deleted_at'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
    }

    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function emissionsTests(): HasMany
    {
        return $this->hasMany(EmissionsTest::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }

    public function disabilityPlacards(): HasMany
    {
        return $this->hasMany(DisabilityPlacard::class);
    }

    public function scopeActive($query)
    {
        return $query->where('registration_status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('registration_status', 'active')
            ->whereBetween('expiration_date', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now())
            ->where('registration_status', 'active');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'owner_id',
            'tribe_id',
            'vin',
            'plate_number',
            'year',
            'make',
            'model',
            'color',
            'vehicle_type',
            'registration_status',
            'registration_date',
            'expiration_date',
            'mileage',
            'is_garaged_on_reservation',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopeApiDetail(Builder $query): Builder
    {
        return $query->select([
            'id',
            'owner_id',
            'tribe_id',
            'vin',
            'plate_number',
            'year',
            'make',
            'model',
            'color',
            'vehicle_type',
            'registration_status',
            'registration_date',
            'expiration_date',
            'title_number',
            'lienholder_name',
            'lienholder_address',
            'is_garaged_on_reservation',
            'mileage',
            'metadata',
            'created_at',
            'updated_at',
        ]);
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (! $this->expiration_date) {
            return null;
        }

        return now()->diffInDays($this->expiration_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = $this->days_until_expiration;

        return $days !== null && $days <= 30 && $days > 0;
    }

    public function getIsExpiredAttribute(): bool
    {
        return (bool) ($this->expiration_date && $this->expiration_date->isPast());
    }
}
