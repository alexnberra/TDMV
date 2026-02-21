<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'payment_method' => 'required|in:card,ach',
            'payment_token' => 'required|string',
            'fee_breakdown' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $amount = array_sum($validated['fee_breakdown']);

            $payment = $application->payments()->create([
                'user_id' => $request->user()->id,
                'tribe_id' => $application->tribe_id,
                'payment_method' => $validated['payment_method'],
                'amount' => $amount,
                'fee_breakdown' => $validated['fee_breakdown'],
                'status' => 'completed',
                'transaction_id' => 'TXN-'.strtoupper(uniqid()),
                'payment_gateway' => 'stripe',
                'paid_at' => now(),
                'gateway_response' => ['success' => true, 'mock' => true],
            ]);

            $application->timeline()->create([
                'event_type' => 'payment_received',
                'description' => 'Payment received: $'.number_format((float) $amount, 2),
                'performed_by' => $request->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'payment' => PaymentResource::make(
                    Payment::query()
                        ->apiList()
                        ->whereKey($payment->id)
                        ->firstOrFail()
                )->resolve(),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        $payment->loadMissing([
            'application' => fn ($query) => $query->apiList(),
        ]);

        return response()->json([
            'payment' => PaymentResource::make($payment)->resolve(),
        ]);
    }
}
