<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Fleet\AssignFleetVehicleRequest;
use App\Models\BusinessAccount;
use App\Models\FleetVehicle;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetVehicleController extends Controller
{
    public function store(AssignFleetVehicleRequest $request, BusinessAccount $businessAccount): JsonResponse
    {
        $this->authorize('update', $businessAccount);

        $validated = $request->validated();
        $vehicle = Vehicle::query()
            ->apiDetail()
            ->where('tribe_id', $businessAccount->tribe_id)
            ->find($validated['vehicle_id']);

        if (! $vehicle) {
            return response()->json([
                'message' => 'Vehicle not found for this tribe.',
            ], 422);
        }

        if (! $request->user()->isStaff() && $vehicle->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You can only assign fleet vehicles that you own.',
            ], 403);
        }

        $assignedDriverId = $validated['assigned_driver_id'] ?? null;
        if ($assignedDriverId) {
            $driver = User::query()
                ->apiSummary()
                ->where('tribe_id', $businessAccount->tribe_id)
                ->find($assignedDriverId);

            if (! $driver) {
                return response()->json([
                    'message' => 'Assigned driver must belong to the same tribe.',
                ], 422);
            }
        }

        $fleetVehicle = $businessAccount->fleetVehicles()
            ->withTrashed()
            ->where('vehicle_id', $vehicle->id)
            ->first();

        if ($fleetVehicle) {
            $fleetVehicle->restore();
            $fleetVehicle->update([
                'assigned_driver_id' => $assignedDriverId,
                'status' => $validated['status'] ?? $fleetVehicle->status ?? 'active',
                'added_at' => $validated['added_at'] ?? $fleetVehicle->added_at ?? now(),
                'metadata' => $validated['metadata'] ?? $fleetVehicle->metadata,
            ]);
        } else {
            $fleetVehicle = $businessAccount->fleetVehicles()->create([
                'vehicle_id' => $vehicle->id,
                'assigned_driver_id' => $assignedDriverId,
                'status' => $validated['status'] ?? 'active',
                'added_at' => $validated['added_at'] ?? now(),
                'metadata' => $validated['metadata'] ?? null,
            ]);
        }

        return response()->json([
            'fleet_vehicle' => $fleetVehicle->fresh()->load([
                'vehicle' => fn ($query) => $query->apiList(),
                'assignedDriver:id,first_name,last_name,email',
            ]),
        ], 201);
    }

    public function destroy(Request $request, BusinessAccount $businessAccount, FleetVehicle $fleetVehicle): JsonResponse
    {
        $this->authorize('update', $businessAccount);

        if ($fleetVehicle->business_account_id !== $businessAccount->id) {
            return response()->json([
                'message' => 'Fleet vehicle does not belong to this business account.',
            ], 404);
        }

        $this->authorize('delete', $fleetVehicle);

        $fleetVehicle->delete();

        return response()->json([
            'message' => 'Fleet vehicle removed successfully.',
        ]);
    }
}
