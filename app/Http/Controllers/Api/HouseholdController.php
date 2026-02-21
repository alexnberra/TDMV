<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Household\AddHouseholdMemberRequest;
use App\Http\Requests\Api\Household\StoreHouseholdRequest;
use App\Http\Requests\Api\Household\UpdateHouseholdRequest;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Household::class);

        $user = $request->user();
        $query = Household::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'owner:id,first_name,last_name,email',
                'members' => fn ($members) => $members->apiList()->with([
                    'user:id,first_name,last_name,email,date_of_birth',
                ]),
            ])
            ->withCount('appointments')
            ->latest();

        if (! $user->isStaff()) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('owner_user_id', $user->id)
                    ->orWhereHas('members', function ($memberQuery) use ($user): void {
                        $memberQuery->where('user_id', $user->id);
                    });
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreHouseholdRequest $request): JsonResponse
    {
        $this->authorize('create', Household::class);

        $user = $request->user();
        $validated = $request->validated();

        $household = DB::transaction(function () use ($validated, $user): Household {
            $household = Household::create([
                ...$validated,
                'tribe_id' => $user->tribe_id,
                'owner_user_id' => $user->id,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $household->members()->create([
                'user_id' => $user->id,
                'relationship_type' => 'self',
                'is_primary' => true,
                'can_manage_minor_vehicles' => true,
                'is_minor' => false,
                'date_joined' => now()->toDateString(),
                'metadata' => ['auto_added_owner' => true],
            ]);

            return $household;
        });

        return response()->json([
            'household' => $household->fresh()->load([
                'owner:id,first_name,last_name,email',
                'members' => fn ($members) => $members->apiList()->with([
                    'user:id,first_name,last_name,email,date_of_birth',
                ]),
            ]),
        ], 201);
    }

    public function show(Household $household): JsonResponse
    {
        $this->authorize('view', $household);

        return response()->json([
            'household' => $household->load([
                'owner:id,first_name,last_name,email',
                'members' => fn ($members) => $members->apiList()->with([
                    'user:id,first_name,last_name,email,date_of_birth',
                ]),
                'appointments' => fn ($appointments) => $appointments->apiList()
                    ->with([
                        'officeLocation:id,name,address,phone',
                    ])
                    ->latest('scheduled_for')
                    ->limit(10),
            ]),
        ]);
    }

    public function update(UpdateHouseholdRequest $request, Household $household): JsonResponse
    {
        $this->authorize('update', $household);

        $household->update($request->validated());

        return response()->json([
            'household' => $household->fresh()->load([
                'owner:id,first_name,last_name,email',
                'members' => fn ($members) => $members->apiList()->with([
                    'user:id,first_name,last_name,email,date_of_birth',
                ]),
            ]),
        ]);
    }

    public function destroy(Household $household): JsonResponse
    {
        $this->authorize('delete', $household);

        $household->delete();

        return response()->json([
            'message' => 'Household deleted successfully.',
        ]);
    }

    public function addMember(AddHouseholdMemberRequest $request, Household $household): JsonResponse
    {
        $this->authorize('update', $household);

        $validated = $request->validated();
        $memberUser = User::query()
            ->apiSummary()
            ->where('tribe_id', $household->tribe_id)
            ->find($validated['user_id']);

        if (! $memberUser) {
            return response()->json([
                'message' => 'Member must belong to the same tribe.',
            ], 422);
        }

        DB::transaction(function () use ($household, $validated): void {
            if (($validated['is_primary'] ?? false) === true) {
                $household->members()->update(['is_primary' => false]);
            }

            $householdMember = $household->members()
                ->withTrashed()
                ->where('user_id', $validated['user_id'])
                ->first();

            if ($householdMember) {
                $householdMember->restore();
                $householdMember->update([
                    'relationship_type' => $validated['relationship_type'],
                    'is_primary' => $validated['is_primary'] ?? false,
                    'can_manage_minor_vehicles' => $validated['can_manage_minor_vehicles'] ?? false,
                    'is_minor' => $validated['is_minor'] ?? false,
                    'date_joined' => $validated['date_joined'] ?? now()->toDateString(),
                    'metadata' => $validated['metadata'] ?? null,
                ]);

                return;
            }

            $household->members()->create([
                'user_id' => $validated['user_id'],
                'relationship_type' => $validated['relationship_type'],
                'is_primary' => $validated['is_primary'] ?? false,
                'can_manage_minor_vehicles' => $validated['can_manage_minor_vehicles'] ?? false,
                'is_minor' => $validated['is_minor'] ?? false,
                'date_joined' => $validated['date_joined'] ?? now()->toDateString(),
                'metadata' => $validated['metadata'] ?? null,
            ]);
        });

        return response()->json([
            'members' => $household->members()
                ->apiList()
                ->with(['user:id,first_name,last_name,email,date_of_birth'])
                ->get(),
        ]);
    }

    public function removeMember(Household $household, HouseholdMember $householdMember): JsonResponse
    {
        $this->authorize('update', $household);

        if ($householdMember->household_id !== $household->id) {
            return response()->json([
                'message' => 'Household member does not belong to this household.',
            ], 404);
        }

        $this->authorize('delete', $householdMember);

        if ($householdMember->user_id === $household->owner_user_id) {
            return response()->json([
                'message' => 'Household owner cannot be removed.',
            ], 422);
        }

        $householdMember->delete();

        return response()->json([
            'message' => 'Household member removed.',
        ]);
    }
}
