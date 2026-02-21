<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreferences extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expiration_reminders',
        'status_updates',
        'document_requests',
        'payment_confirmations',
        'office_announcements',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
    ];

    protected function casts(): array
    {
        return [
            'expiration_reminders' => 'boolean',
            'status_updates' => 'boolean',
            'document_requests' => 'boolean',
            'payment_confirmations' => 'boolean',
            'office_announcements' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApiSelect(Builder $query): Builder
    {
        return $query->select([
            'id',
            'user_id',
            'expiration_reminders',
            'status_updates',
            'document_requests',
            'payment_confirmations',
            'office_announcements',
            'email_enabled',
            'sms_enabled',
            'push_enabled',
        ]);
    }
}
