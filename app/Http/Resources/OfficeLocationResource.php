<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeLocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tribe_id' => $this->tribe_id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'hours' => $this->hours,
            'is_active' => $this->is_active,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'current_wait_time' => $this->current_wait_time,
            'updated_at' => $this->updated_at,
        ];
    }
}
