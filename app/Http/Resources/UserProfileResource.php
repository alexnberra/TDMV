<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tribe_id' => $this->tribe_id,
            'tribal_enrollment_id' => $this->tribal_enrollment_id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'date_of_birth' => $this->date_of_birth,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at,
            'role' => $this->role,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at,
            'tribe' => TribeResource::make($this->whenLoaded('tribe')),
            'notificationPreferences' => NotificationPreferenceResource::make($this->whenLoaded('notificationPreferences')),
        ];
    }
}
