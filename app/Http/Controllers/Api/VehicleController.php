<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vehicles = $request->user()
            ->vehicles()
            ->apiDetail()
            ->latest('id')
            ->get();

        return response()->json([
            'vehicles' => $vehicles
                ->map(fn (Vehicle $vehicle) => VehicleResource::make($vehicle)->resolve())
                ->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vin' => 'required|string|size:17|unique:vehicles,vin',
            'year' => 'required|integer|min:1900|max:'.(date('Y') + 1),
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'color' => 'required|string|max:50',
            'vehicle_type' => 'required|in:car,truck,suv,motorcycle,rv,trailer,commercial',
            'is_garaged_on_reservation' => 'required|boolean',
        ]);

        $vehicle = $request->user()->vehicles()->create([
            ...$validated,
            'tribe_id' => $request->user()->tribe_id,
            'registration_status' => 'pending',
        ]);

        return response()->json([
            'vehicle' => VehicleResource::make($vehicle)->resolve(),
        ], 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        $vehicle->loadMissing([
            'owner' => fn ($query) => $query->apiSummary(),
        ])->loadCount('applications');

        return response()->json([
            'vehicle' => VehicleResource::make($vehicle)->resolve(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'color' => 'sometimes|string|max:50',
            'is_garaged_on_reservation' => 'sometimes|boolean',
            'mileage' => 'sometimes|integer|nullable|min:0',
        ]);

        $vehicle->update($validated);

        return response()->json([
            'vehicle' => VehicleResource::make(
                Vehicle::query()
                    ->apiDetail()
                    ->whereKey($vehicle->id)
                    ->firstOrFail()
            )->resolve(),
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    public function renewalHistory(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('view', $vehicle);

        $history = $vehicle->applications()
            ->apiList()
            ->where('service_type', 'renewal')
            ->with([
                'payments' => fn ($query) => $query->apiList(),
            ])
            ->latest('id')
            ->get();

        return response()->json([
            'history' => $history
                ->map(fn ($application) => ApplicationResource::make($application)->resolve())
                ->values(),
        ]);
    }
}
