<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationTimelineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'event_type' => $this->event_type,
            'description' => $this->description,
            'performed_by' => $this->performed_by,
            'metadata' => $this->metadata,
            'performer' => UserSummaryResource::make($this->whenLoaded('performer')),
            'created_at' => $this->created_at,
        ];
    }
}
