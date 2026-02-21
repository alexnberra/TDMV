<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Application::query()
            ->apiList()
            ->with([
                'user' => fn ($query) => $query->apiSummary(),
                'vehicle' => fn ($query) => $query->apiList(),
                'documents' => fn ($query) => $query->apiList(),
                'payments' => fn ($query) => $query->apiList(),
            ])
            ->withCount(['documents', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->value();

            $query->where(function ($q) use ($search): void {
                $q->where('case_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $applications = $query->latest('id')->paginate(50);

        return response()->json(
            $applications->through(
                fn (Application $application) => ApplicationResource::make($application)->resolve()
            )
        );
    }

    public function updateStatus(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:under_review,approved,rejected,completed',
            'reviewer_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $application->update([
            ...$validated,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json([
            'application' => ApplicationResource::make(
                Application::query()
                    ->apiList()
                    ->whereKey($application->id)
                    ->firstOrFail()
            )->resolve(),
        ]);
    }

    public function requestMoreInfo(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $application->update([
            'status' => 'info_requested',
            'reviewer_notes' => $validated['message'],
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        $application->timeline()->create([
            'event_type' => 'info_requested',
            'description' => 'More information requested',
            'performed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Information request sent',
        ]);
    }
}
