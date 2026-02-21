<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'tribe_id',
        'transaction_id',
        'payment_method',
        'amount',
        'fee_breakdown',
        'status',
        'payment_gateway',
        'gateway_response',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'fee_breakdown' => 'array',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeApiList(Builder $query): Builder
    {
        return $query->select([
            'id',
            'application_id',
            'user_id',
            'tribe_id',
            'transaction_id',
            'payment_method',
            'amount',
            'fee_breakdown',
            'status',
            'payment_gateway',
            'paid_at',
            'refunded_at',
            'refund_amount',
            'refund_reason',
            'created_at',
        ]);
    }
}
