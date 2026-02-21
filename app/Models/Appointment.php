<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'tribe_id',
        'user_id',
        'household_id',
        'office_location_id',
        'appointment_type',
        'status',
        'scheduled_for',
        'duration_minutes',
        'check_in_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'notes',
        'confirmation_code',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'check_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'tribe_id',
            'user_id',
            'household_id',
            'office_location_id',
            'appointment_type',
            'status',
            'scheduled_for',
            'duration_minutes',
            'check_in_at',
            'completed_at',
            'cancelled_at',
            'cancelled_by',
            'cancel_reason',
            'notes',
            'confirmation_code',
            'metadata',
            'created_at',
            'updated_at',
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment): void {
            if ($appointment->confirmation_code) {
                return;
            }

            do {
                $code = strtoupper(Str::random(8));
            } while (self::query()->where('confirmation_code', $code)->exists());

            $appointment->confirmation_code = $code;
        });
    }
}
