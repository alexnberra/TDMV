<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'case_number',
        'user_id',
        'tribe_id',
        'vehicle_id',
        'service_type',
        'status',
        'priority',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'completed_at',
        'estimated_completion_date',
        'vehicle_data',
        'requirements_data',
        'reviewer_notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_completion_date' => 'date',
            'vehicle_data' => 'array',
            'requirements_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(ApplicationTimeline::class);
    }

    public function assistantInteractions(): HasMany
    {
        return $this->hasMany(AssistantInteraction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function booted(): void
    {
        static::creating(function (Application $application): void {
            if (! $application->case_number) {
                $application->case_number = self::generateCaseNumber();
            }
        });

        static::updated(function (Application $application): void {
            if ($application->wasChanged('status')) {
                $application->logStatusChange();
            }
        });
    }

    public static function generateCaseNumber(): string
    {
        $year = now()->year;
        $lastCase = self::whereYear('created_at', $year)->latest('id')->first();
        $nextNum = $lastCase ? ((int) substr($lastCase->case_number, -3)) + 1 : 1;

        return sprintf('APP-%d-%03d', $year, $nextNum);
    }

    public function logStatusChange(): void
    {
        $this->timeline()->create([
            'event_type' => 'status_changed',
            'description' => "Status changed to: {$this->status}",
            'performed_by' => auth()->id(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'case_number',
            'user_id',
            'tribe_id',
            'vehicle_id',
            'service_type',
            'status',
            'priority',
            'submitted_at',
            'reviewed_at',
            'reviewed_by',
            'completed_at',
            'estimated_completion_date',
            'vehicle_data',
            'requirements_data',
            'reviewer_notes',
            'rejection_reason',
            'created_at',
            'updated_at',
        ]);
    }
}
