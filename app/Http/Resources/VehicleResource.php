<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributes = $this->resource->getAttributes();

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'tribe_id' => $this->tribe_id,
            'vin' => $this->vin,
            'plate_number' => $this->plate_number,
            'year' => $this->year,
            'make' => $this->make,
            'model' => $this->model,
            'color' => $this->color,
            'vehicle_type' => $this->vehicle_type,
            'registration_status' => $this->registration_status,
            'registration_date' => $this->registration_date,
            'expiration_date' => $this->expiration_date,
            'title_number' => $this->when(array_key_exists('title_number', $attributes), fn () => $this->title_number),
            'lienholder_name' => $this->when(array_key_exists('lienholder_name', $attributes), fn () => $this->lienholder_name),
            'lienholder_address' => $this->when(array_key_exists('lienholder_address', $attributes), fn () => $this->lienholder_address),
            'is_garaged_on_reservation' => $this->is_garaged_on_reservation,
            'mileage' => $this->mileage,
            'metadata' => $this->when(array_key_exists('metadata', $attributes), fn () => $this->metadata),
            'days_until_expiration' => $this->days_until_expiration,
            'is_expiring_soon' => $this->is_expiring_soon,
            'is_expired' => $this->is_expired,
            'applications_count' => $this->whenCounted('applications'),
            'owner' => UserSummaryResource::make($this->whenLoaded('owner')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
