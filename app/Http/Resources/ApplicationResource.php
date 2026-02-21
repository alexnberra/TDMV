<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'user_id' => $this->user_id,
            'tribe_id' => $this->tribe_id,
            'vehicle_id' => $this->vehicle_id,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'priority' => $this->priority,
            'submitted_at' => $this->submitted_at,
            'reviewed_at' => $this->reviewed_at,
            'reviewed_by' => $this->reviewed_by,
            'completed_at' => $this->completed_at,
            'estimated_completion_date' => $this->estimated_completion_date,
            'vehicle_data' => $this->vehicle_data,
            'requirements_data' => $this->requirements_data,
            'reviewer_notes' => $this->reviewer_notes,
            'rejection_reason' => $this->rejection_reason,
            'documents_count' => $this->whenCounted('documents'),
            'payments_count' => $this->whenCounted('payments'),
            'user' => UserSummaryResource::make($this->whenLoaded('user')),
            'vehicle' => VehicleResource::make($this->whenLoaded('vehicle')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'timeline' => ApplicationTimelineResource::collection($this->whenLoaded('timeline')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
