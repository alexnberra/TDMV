<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'user_id' => $this->user_id,
            'document_type' => $this->document_type,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'human_file_size' => $this->human_file_size,
            'mime_type' => $this->mime_type,
            'url' => $this->url,
            'status' => $this->status,
            'uploaded_at' => $this->uploaded_at,
            'reviewed_at' => $this->reviewed_at,
            'reviewed_by' => $this->reviewed_by,
            'rejection_reason' => $this->rejection_reason,
            'expiration_date' => $this->expiration_date,
            'created_at' => $this->created_at,
        ];
    }
}
