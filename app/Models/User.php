<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'tribe_id',
        'tribal_enrollment_id',
        'name',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'email',
        'phone',
        'password',
        'role',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'owner_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function businessAccountsOwned(): HasMany
    {
        return $this->hasMany(BusinessAccount::class, 'owner_user_id');
    }

    public function businessAccounts(): BelongsToMany
    {
        return $this->belongsToMany(BusinessAccount::class, 'business_account_user')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function fleetAssignments(): HasMany
    {
        return $this->hasMany(FleetVehicle::class, 'assigned_driver_id');
    }

    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function emissionsTests(): HasMany
    {
        return $this->hasMany(EmissionsTest::class);
    }

    public function vehicleInspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }

    public function memberBenefits(): HasMany
    {
        return $this->hasMany(MemberBenefit::class);
    }

    public function disabilityPlacards(): HasMany
    {
        return $this->hasMany(DisabilityPlacard::class);
    }

    public function householdsOwned(): HasMany
    {
        return $this->hasMany(Household::class, 'owner_user_id');
    }

    public function householdMemberships(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function assistantInteractions(): HasMany
    {
        return $this->hasMany(AssistantInteraction::class);
    }

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreferences::class);
    }

    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['staff', 'admin']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApiAuth(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'tribal_enrollment_id',
            'name',
            'first_name',
            'last_name',
            'middle_name',
            'date_of_birth',
            'email',
            'email_verified_at',
            'phone',
            'phone_verified_at',
            'password',
            'role',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'zip_code',
            'is_active',
            'last_login_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopeApiProfile(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'tribal_enrollment_id',
            'name',
            'first_name',
            'last_name',
            'middle_name',
            'date_of_birth',
            'email',
            'email_verified_at',
            'phone',
            'phone_verified_at',
            'role',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'zip_code',
            'is_active',
            'last_login_at',
            'created_at',
            'updated_at',
        ]);
    }

    public function scopeApiSummary(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'name',
            'first_name',
            'last_name',
            'email',
            'phone',
            'role',
        ]);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'admin'], true);
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (! $user->name && ($user->first_name || $user->last_name)) {
                $user->name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));
            }

            if (! $user->first_name && $user->name) {
                [$first, $rest] = array_pad(explode(' ', $user->name, 2), 2, null);
                $user->first_name = $first;
                $user->last_name = $rest;
            }
        });

        static::created(function (User $user): void {
            if (! $user->notificationPreferences()->exists()) {
                $user->notificationPreferences()->create([]);
            }
        });
    }
}
