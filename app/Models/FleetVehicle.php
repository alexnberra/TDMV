<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetVehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'business_account_id',
        'vehicle_id',
        'assigned_driver_id',
        'status',
        'added_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function businessAccount(): BelongsTo
    {
        return $this->belongsTo(BusinessAccount::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'business_account_id',
            'vehicle_id',
            'assigned_driver_id',
            'status',
            'added_at',
            'metadata',
            'created_at',
        ]);
    }
}
