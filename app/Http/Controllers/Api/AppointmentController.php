<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Appointment\CancelAppointmentRequest;
use App\Http\Requests\Api\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Api\Appointment\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Household;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $user = $request->user();
        $query = Appointment::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->with([
                'user:id,first_name,last_name,email',
                'household:id,household_name',
                'officeLocation:id,name,address,phone',
            ])
            ->latest('scheduled_for');

        if (! $user->isStaff()) {
            $query->where(function ($builder) use ($user): void {
                $builder->where('user_id', $user->id)
                    ->orWhereHas('household.members', function ($memberQuery) use ($user): void {
                        $memberQuery->where('user_id', $user->id);
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('upcoming') && (string) $request->input('upcoming') === '1') {
            $query->where('scheduled_for', '>=', now());
        }

        return response()->json($query->paginate(20));
    }

    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $this->authorize('create', Appointment::class);

        $validated = $request->validated();
        $requestUser = $request->user();
        $targetUserId = $requestUser->isStaff() ? ($validated['user_id'] ?? $requestUser->id) : $requestUser->id;

        $targetUser = User::query()
            ->apiSummary()
            ->where('tribe_id', $requestUser->tribe_id)
            ->find($targetUserId);

        if (! $targetUser) {
            return response()->json([
                'message' => 'Selected appointment member must belong to the same tribe.',
            ], 422);
        }

        $householdId = $validated['household_id'] ?? null;
        if ($householdId) {
            $household = Household::query()
                ->apiList()
                ->where('tribe_id', $requestUser->tribe_id)
                ->find($householdId);

            if (! $household) {
                return response()->json([
                    'message' => 'Selected household must belong to the same tribe.',
                ], 422);
            }

            if (
                ! $requestUser->isStaff()
                && $household->owner_user_id !== $requestUser->id
                && ! $household->members()->where('user_id', $requestUser->id)->exists()
            ) {
                return response()->json([
                    'message' => 'You do not have access to this household.',
                ], 403);
            }
        }

        $officeLocationId = $validated['office_location_id'] ?? null;
        if ($officeLocationId) {
            $office = OfficeLocation::query()
                ->select(['id', 'tribe_id', 'is_active'])
                ->where('tribe_id', $requestUser->tribe_id)
                ->find($officeLocationId);

            if (! $office || ! $office->is_active) {
                return response()->json([
                    'message' => 'Selected office is invalid or inactive.',
                ], 422);
            }
        }

        $appointment = Appointment::create([
            ...$validated,
            'tribe_id' => $requestUser->tribe_id,
            'user_id' => $targetUser->id,
            'household_id' => $householdId,
            'office_location_id' => $officeLocationId,
            'duration_minutes' => $validated['duration_minutes'] ?? 30,
            'status' => 'requested',
        ]);

        return response()->json([
            'appointment' => $appointment->fresh()->load([
                'user:id,first_name,last_name,email',
                'household:id,household_name',
                'officeLocation:id,name,address,phone',
            ]),
        ], 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        return response()->json([
            'appointment' => $appointment->load([
                'user:id,first_name,last_name,email',
                'household:id,household_name',
                'officeLocation:id,name,address,phone',
                'canceller:id,first_name,last_name,email',
            ]),
        ]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('update', $appointment);

        $validated = $request->validated();
        $requestUser = $request->user();
        $newStatus = $validated['status'] ?? $appointment->status;

        if (isset($validated['status']) && ! $requestUser->isStaff()) {
            return response()->json([
                'message' => 'Only staff may directly change appointment status.',
            ], 403);
        }

        if (isset($validated['office_location_id'])) {
            $office = OfficeLocation::query()
                ->select(['id', 'tribe_id', 'is_active'])
                ->where('tribe_id', $appointment->tribe_id)
                ->find($validated['office_location_id']);

            if (! $office || ! $office->is_active) {
                return response()->json([
                    'message' => 'Selected office is invalid or inactive.',
                ], 422);
            }
        }

        if ($requestUser->isStaff()) {
            if ($newStatus === 'checked_in') {
                $validated['check_in_at'] = now();
            }

            if ($newStatus === 'completed') {
                $validated['completed_at'] = now();
            }
        } else {
            $validated['status'] = 'rescheduled';
        }

        $appointment->update($validated);

        return response()->json([
            'appointment' => $appointment->fresh()->load([
                'user:id,first_name,last_name,email',
                'household:id,household_name',
                'officeLocation:id,name,address,phone',
            ]),
        ]);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return response()->json([
            'message' => 'Appointment removed.',
        ]);
    }

    public function cancel(CancelAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);

        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'], true)) {
            return response()->json([
                'message' => 'Appointment cannot be cancelled at this stage.',
            ], 422);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancel_reason' => $request->validated('cancel_reason'),
        ]);

        return response()->json([
            'appointment' => $appointment->fresh()->load([
                'user:id,first_name,last_name,email',
                'officeLocation:id,name,address,phone',
                'canceller:id,first_name,last_name,email',
            ]),
        ]);
    }
}
