<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Insurance\StoreInsurancePolicyRequest;
use App\Http\Requests\Api\Insurance\UpdateInsurancePolicyRequest;
use App\Models\InsurancePolicy;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsurancePolicyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InsurancePolicy::class);

        $user = $request->user();
        $query = InsurancePolicy::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'verifier:id,first_name,last_name,email',
            ])
            ->latest('effective_date');

        if (! $user->isStaff()) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('user_id', $user->id)
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($user): void {
                        $vehicleQuery->where('owner_id', $user->id);
                    });
            });
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', (int) $request->input('vehicle_id'));
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreInsurancePolicyRequest $request): JsonResponse
    {
        $this->authorize('create', InsurancePolicy::class);

        $validated = $request->validated();
        $user = $request->user();
        $vehicle = Vehicle::query()
            ->apiDetail()
            ->where('tribe_id', $user->tribe_id)
            ->find($validated['vehicle_id']);

        if (! $vehicle) {
            return response()->json([
                'message' => 'Vehicle not found for this tribe.',
            ], 422);
        }

        if (! $user->isStaff() && $vehicle->owner_id !== $user->id) {
            return response()->json([
                'message' => 'You can only add insurance for your own vehicles.',
            ], 403);
        }

        $insurancePolicy = InsurancePolicy::create([
            ...$validated,
            'tribe_id' => $user->tribe_id,
            'user_id' => $vehicle->owner_id,
            'status' => $validated['status'] ?? 'pending',
            'is_verified' => false,
        ]);

        return response()->json([
            'insurance_policy' => $insurancePolicy->fresh()->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'verifier:id,first_name,last_name,email',
            ]),
        ], 201);
    }

    public function show(InsurancePolicy $insurancePolicy): JsonResponse
    {
        $this->authorize('view', $insurancePolicy);

        return response()->json([
            'insurance_policy' => $insurancePolicy->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'verifier:id,first_name,last_name,email',
            ]),
        ]);
    }

    public function update(UpdateInsurancePolicyRequest $request, InsurancePolicy $insurancePolicy): JsonResponse
    {
        $this->authorize('update', $insurancePolicy);

        $validated = $request->validated();
        $user = $request->user();

        if (! $user->isStaff() && isset($validated['is_verified'])) {
            return response()->json([
                'message' => 'Only staff can verify insurance policies.',
            ], 403);
        }

        if (! $user->isStaff() && isset($validated['status']) && $validated['status'] !== 'cancelled') {
            return response()->json([
                'message' => 'Members can only cancel their insurance policy records.',
            ], 403);
        }

        if (isset($validated['is_verified']) && $user->isStaff()) {
            if ($validated['is_verified']) {
                $validated['verified_at'] = now();
                $validated['verified_by'] = $user->id;
            } else {
                $validated['verified_at'] = null;
                $validated['verified_by'] = null;
            }
        }

        $insurancePolicy->update($validated);

        return response()->json([
            'insurance_policy' => $insurancePolicy->fresh()->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'verifier:id,first_name,last_name,email',
            ]),
        ]);
    }
}
