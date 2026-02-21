<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Benefits\StoreDisabilityPlacardRequest;
use App\Http\Requests\Api\Benefits\UpdateDisabilityPlacardRequest;
use App\Models\DisabilityPlacard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisabilityPlacardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', DisabilityPlacard::class);

        $user = $request->user();
        $query = DisabilityPlacard::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'user:id,first_name,last_name,email',
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'approver:id,first_name,last_name,email',
            ])
            ->latest();

        if ($user->isStaff() && $request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        } elseif (! $user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreDisabilityPlacardRequest $request): JsonResponse
    {
        $this->authorize('create', DisabilityPlacard::class);

        $validated = $request->validated();
        $requestUser = $request->user();
        $targetUserId = $validated['user_id'] ?? $requestUser->id;

        if (! $requestUser->isStaff() && $targetUserId !== $requestUser->id) {
            return response()->json([
                'message' => 'You cannot create placards for another user.',
            ], 403);
        }

        $targetUser = User::query()
            ->apiSummary()
            ->where('tribe_id', $requestUser->tribe_id)
            ->find($targetUserId);

        if (! $targetUser) {
            return response()->json([
                'message' => 'Selected member must belong to the same tribe.',
            ], 422);
        }

        $vehicleId = $validated['vehicle_id'] ?? null;
        if ($vehicleId) {
            $vehicle = Vehicle::query()
                ->apiDetail()
                ->where('tribe_id', $requestUser->tribe_id)
                ->find($vehicleId);

            if (! $vehicle) {
                return response()->json([
                    'message' => 'Selected vehicle must belong to the same tribe.',
                ], 422);
            }

            if (! $requestUser->isStaff() && $vehicle->owner_id !== $requestUser->id) {
                return response()->json([
                    'message' => 'You can only request placards for your own vehicles.',
                ], 403);
            }
        }

        $status = $validated['status'] ?? 'pending';
        if (! $requestUser->isStaff()) {
            $status = 'pending';
        }

        $placardNumber = null;
        if ($requestUser->isStaff() && $status === 'approved') {
            $placardNumber = $this->generatePlacardNumber();
        }

        $disabilityPlacard = DisabilityPlacard::create([
            ...$validated,
            'user_id' => $targetUser->id,
            'tribe_id' => $requestUser->tribe_id,
            'status' => $status,
            'placard_number' => $placardNumber,
            'approved_by' => $requestUser->isStaff() && $status === 'approved' ? $requestUser->id : null,
            'approved_at' => $requestUser->isStaff() && $status === 'approved' ? now() : null,
        ]);

        return response()->json([
            'disability_placard' => $disabilityPlacard->fresh()->load([
                'user:id,first_name,last_name,email',
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'approver:id,first_name,last_name,email',
            ]),
        ], 201);
    }

    public function show(DisabilityPlacard $disabilityPlacard): JsonResponse
    {
        $this->authorize('view', $disabilityPlacard);

        return response()->json([
            'disability_placard' => $disabilityPlacard->load([
                'user:id,first_name,last_name,email',
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'approver:id,first_name,last_name,email',
            ]),
        ]);
    }

    public function update(UpdateDisabilityPlacardRequest $request, DisabilityPlacard $disabilityPlacard): JsonResponse
    {
        $this->authorize('update', $disabilityPlacard);

        $validated = $request->validated();
        $status = $validated['status'] ?? $disabilityPlacard->status;

        if ($status === 'approved') {
            $validated['approved_by'] = $request->user()->id;
            $validated['approved_at'] = now();
            $validated['placard_number'] = $disabilityPlacard->placard_number ?: $this->generatePlacardNumber();
            $validated['rejection_reason'] = null;
        }

        if (in_array($status, ['rejected', 'revoked', 'expired'], true)) {
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
        }

        $disabilityPlacard->update($validated);

        return response()->json([
            'disability_placard' => $disabilityPlacard->fresh()->load([
                'user:id,first_name,last_name,email',
                'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                'approver:id,first_name,last_name,email',
            ]),
        ]);
    }

    public function destroy(DisabilityPlacard $disabilityPlacard): JsonResponse
    {
        $this->authorize('delete', $disabilityPlacard);

        $disabilityPlacard->delete();

        return response()->json([
            'message' => 'Placard removed successfully.',
        ]);
    }

    private function generatePlacardNumber(): string
    {
        do {
            $placardNumber = sprintf('DP-%s-%04d', now()->format('Y'), random_int(1000, 9999));
        } while (DisabilityPlacard::where('placard_number', $placardNumber)->exists());

        return $placardNumber;
    }
}
