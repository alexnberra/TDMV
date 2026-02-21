<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'tribe_id' => $this->tribe_id,
            'transaction_id' => $this->transaction_id,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'fee_breakdown' => $this->fee_breakdown,
            'status' => $this->status,
            'payment_gateway' => $this->payment_gateway,
            'paid_at' => $this->paid_at,
            'refunded_at' => $this->refunded_at,
            'refund_amount' => $this->refund_amount,
            'refund_reason' => $this->refund_reason,
            'application' => ApplicationResource::make($this->whenLoaded('application')),
            'created_at' => $this->created_at,
        ];
    }
}
