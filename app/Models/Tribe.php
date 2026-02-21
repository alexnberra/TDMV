<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tribe extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'logo_url',
        'primary_color',
        'contact_email',
        'contact_phone',
        'address',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function businessAccounts(): HasMany
    {
        return $this->hasMany(BusinessAccount::class);
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

    public function households(): HasMany
    {
        return $this->hasMany(Household::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function workflowRules(): HasMany
    {
        return $this->hasMany(WorkflowRule::class);
    }

    public function assistantInteractions(): HasMany
    {
        return $this->hasMany(AssistantInteraction::class);
    }

    public function officeLocations(): HasMany
    {
        return $this->hasMany(OfficeLocation::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApiPublic(Builder $query): Builder
    {
        return $query->select([
            'id',
            'name',
            'slug',
            'code',
            'primary_color',
            'contact_email',
            'contact_phone',
        ]);
    }
}
