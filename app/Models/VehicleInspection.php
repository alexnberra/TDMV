<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleInspection extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'user_id',
        'tribe_id',
        'inspection_date',
        'result',
        'facility_name',
        'certificate_number',
        'expires_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'inspection_date' => 'date',
            'expires_at' => 'date',
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

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'vehicle_id',
            'user_id',
            'tribe_id',
            'inspection_date',
            'result',
            'facility_name',
            'certificate_number',
            'expires_at',
            'notes',
            'metadata',
            'created_at',
        ]);
    }
}
