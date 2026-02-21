<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Compliance\StoreVehicleInspectionRequest;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleInspectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VehicleInspection::class);

        $user = $request->user();
        $query = VehicleInspection::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
            ])
            ->latest('inspection_date');

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

    public function store(StoreVehicleInspectionRequest $request): JsonResponse
    {
        $this->authorize('create', VehicleInspection::class);

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
                'message' => 'You can only submit inspections for your own vehicles.',
            ], 403);
        }

        $vehicleInspection = VehicleInspection::create([
            ...$validated,
            'tribe_id' => $user->tribe_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'vehicle_inspection' => $vehicleInspection->fresh()->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
            ]),
        ], 201);
    }

    public function show(VehicleInspection $vehicleInspection): JsonResponse
    {
        $this->authorize('view', $vehicleInspection);

        return response()->json([
            'vehicle_inspection' => $vehicleInspection->load([
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
            ]),
        ]);
    }
}
