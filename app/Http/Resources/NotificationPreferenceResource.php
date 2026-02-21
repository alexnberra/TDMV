<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationPreferenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'expiration_reminders' => $this->expiration_reminders,
            'status_updates' => $this->status_updates,
            'document_requests' => $this->document_requests,
            'payment_confirmations' => $this->payment_confirmations,
            'office_announcements' => $this->office_announcements,
            'email_enabled' => $this->email_enabled,
            'sms_enabled' => $this->sms_enabled,
            'push_enabled' => $this->push_enabled,
        ];
    }
}
