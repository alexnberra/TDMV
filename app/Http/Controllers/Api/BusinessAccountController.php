<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BusinessAccount\AddBusinessAccountMemberRequest;
use App\Http\Requests\Api\BusinessAccount\StoreBusinessAccountRequest;
use App\Http\Requests\Api\BusinessAccount\UpdateBusinessAccountRequest;
use App\Models\BusinessAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessAccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BusinessAccount::class);

        $user = $request->user();
        $query = BusinessAccount::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'owner:id,first_name,last_name,email',
                'members:id,first_name,last_name,email',
            ])
            ->withCount('fleetVehicles')
            ->latest();

        if (! $user->isStaff()) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('owner_user_id', $user->id)
                    ->orWhereHas('members', function ($memberQuery) use ($user): void {
                        $memberQuery->whereKey($user->id);
                    });
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreBusinessAccountRequest $request): JsonResponse
    {
        $this->authorize('create', BusinessAccount::class);

        $user = $request->user();
        $validated = $request->validated();

        $businessAccount = DB::transaction(function () use ($validated, $user): BusinessAccount {
            $businessAccount = BusinessAccount::create([
                ...$validated,
                'tribe_id' => $user->tribe_id,
                'owner_user_id' => $user->id,
                'business_type' => $validated['business_type'] ?? 'tribal_business',
                'is_active' => $validated['is_active'] ?? true,
                'tax_exempt' => $validated['tax_exempt'] ?? false,
            ]);

            $businessAccount->members()->syncWithoutDetaching([
                $user->id => [
                    'role' => 'owner',
                    'is_primary' => true,
                ],
            ]);

            return $businessAccount;
        });

        return response()->json([
            'business_account' => $businessAccount->fresh([
                'owner:id,first_name,last_name,email',
                'members:id,first_name,last_name,email',
            ]),
        ], 201);
    }

    public function show(BusinessAccount $businessAccount): JsonResponse
    {
        $this->authorize('view', $businessAccount);

        return response()->json([
            'business_account' => $businessAccount->load([
                'owner:id,first_name,last_name,email',
                'members:id,first_name,last_name,email',
                'fleetVehicles' => fn ($query) => $query->apiList()->with([
                    'vehicle' => fn ($vehicleQuery) => $vehicleQuery->apiList(),
                    'assignedDriver:id,first_name,last_name,email',
                ]),
            ]),
        ]);
    }

    public function update(UpdateBusinessAccountRequest $request, BusinessAccount $businessAccount): JsonResponse
    {
        $this->authorize('update', $businessAccount);

        $businessAccount->update($request->validated());

        return response()->json([
            'business_account' => $businessAccount->fresh([
                'owner:id,first_name,last_name,email',
                'members:id,first_name,last_name,email',
            ]),
        ]);
    }

    public function destroy(BusinessAccount $businessAccount): JsonResponse
    {
        $this->authorize('delete', $businessAccount);

        $businessAccount->delete();

        return response()->json([
            'message' => 'Business account deleted successfully',
        ]);
    }

    public function addMember(AddBusinessAccountMemberRequest $request, BusinessAccount $businessAccount): JsonResponse
    {
        $this->authorize('update', $businessAccount);

        $validated = $request->validated();
        $user = User::query()
            ->apiSummary()
            ->where('tribe_id', $businessAccount->tribe_id)
            ->find($validated['user_id']);

        if (! $user) {
            return response()->json([
                'message' => 'Selected user must belong to the same tribe.',
            ], 422);
        }

        $isPrimary = (bool) ($validated['is_primary'] ?? false);

        DB::transaction(function () use ($businessAccount, $validated, $isPrimary): void {
            if ($isPrimary) {
                $businessAccount->members()
                    ->newPivotStatement()
                    ->where('business_account_id', $businessAccount->id)
                    ->update(['is_primary' => false]);
            }

            $businessAccount->members()->syncWithoutDetaching([
                $validated['user_id'] => [
                    'role' => $validated['role'],
                    'is_primary' => $isPrimary,
                ],
            ]);
        });

        return response()->json([
            'members' => $businessAccount->members()
                ->select(['users.id', 'users.first_name', 'users.last_name', 'users.email'])
                ->get(),
        ]);
    }
}
