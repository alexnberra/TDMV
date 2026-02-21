<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Benefits\StoreMemberBenefitRequest;
use App\Http\Requests\Api\Benefits\UpdateMemberBenefitRequest;
use App\Models\MemberBenefit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberBenefitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MemberBenefit::class);

        $user = $request->user();
        $query = MemberBenefit::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'user:id,first_name,last_name,email',
                'verifier:id,first_name,last_name,email',
            ])
            ->latest();

        if ($user->isStaff() && $request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        } elseif (! $user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreMemberBenefitRequest $request): JsonResponse
    {
        $this->authorize('create', MemberBenefit::class);

        $validated = $request->validated();
        $requestUser = $request->user();
        $targetUserId = $validated['user_id'] ?? $requestUser->id;

        if (! $requestUser->isStaff() && $targetUserId !== $requestUser->id) {
            return response()->json([
                'message' => 'You cannot create benefits for another user.',
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

        $status = $validated['status'] ?? 'pending';
        if (! $requestUser->isStaff()) {
            $status = 'pending';
        }

        $memberBenefit = MemberBenefit::create([
            ...$validated,
            'user_id' => $targetUser->id,
            'tribe_id' => $requestUser->tribe_id,
            'status' => $status,
            'verified_by' => $requestUser->isStaff() && $status === 'active' ? $requestUser->id : null,
            'verified_at' => $requestUser->isStaff() && $status === 'active' ? now() : null,
        ]);

        return response()->json([
            'member_benefit' => $memberBenefit->fresh()->load([
                'user:id,first_name,last_name,email',
                'verifier:id,first_name,last_name,email',
            ]),
        ], 201);
    }

    public function show(MemberBenefit $memberBenefit): JsonResponse
    {
        $this->authorize('view', $memberBenefit);

        return response()->json([
            'member_benefit' => $memberBenefit->load([
                'user:id,first_name,last_name,email',
                'verifier:id,first_name,last_name,email',
            ]),
        ]);
    }

    public function update(UpdateMemberBenefitRequest $request, MemberBenefit $memberBenefit): JsonResponse
    {
        $this->authorize('update', $memberBenefit);

        $validated = $request->validated();
        $status = $validated['status'] ?? $memberBenefit->status;

        if ($status === 'active') {
            $validated['verified_by'] = $request->user()->id;
            $validated['verified_at'] = now();
        }

        if (in_array($status, ['rejected', 'expired'], true)) {
            $validated['verified_by'] = null;
            $validated['verified_at'] = null;
        }

        $memberBenefit->update($validated);

        return response()->json([
            'member_benefit' => $memberBenefit->fresh()->load([
                'user:id,first_name,last_name,email',
                'verifier:id,first_name,last_name,email',
            ]),
        ]);
    }
}
