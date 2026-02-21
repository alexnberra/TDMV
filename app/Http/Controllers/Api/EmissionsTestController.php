<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Compliance\StoreEmissionsTestRequest;
use App\Models\EmissionsTest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmissionsTestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmissionsTest::class);

        $user = $request->user();
        $query = EmissionsTest::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
            ])
            ->latest('test_date');

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

    public function store(StoreEmissionsTestRequest $request): JsonResponse
    {
        $this->authorize('create', EmissionsTest::class);

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
                'message' => 'You can only submit tests for your own vehicles.',
            ], 403);
        }

        $emissionsTest = EmissionsTest::create([
            ...$validated,
            'tribe_id' => $user->tribe_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'emissions_test' => $emissionsTest->fresh()->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
            ]),
        ], 201);
    }

    public function show(EmissionsTest $emissionsTest): JsonResponse
    {
        $this->authorize('view', $emissionsTest);

        return response()->json([
            'emissions_test' => $emissionsTest->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
            ]),
        ]);
    }
}
