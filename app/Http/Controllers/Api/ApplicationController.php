<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ApplicationTimelineResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $applications = $request->user()
            ->applications()
            ->apiList()
            ->with([
                'vehicle' => fn ($query) => $query->apiList(),
                'documents' => fn ($query) => $query->apiList(),
                'payments' => fn ($query) => $query->apiList(),
            ])
            ->withCount(['documents', 'payments'])
            ->latest('id')
            ->paginate(20);

        return response()->json(
            $applications->through(
                fn (Application $application) => ApplicationResource::make($application)->resolve()
            )
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_type' => 'required|in:renewal,new_registration,title_transfer,plate_replacement,duplicate_title',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'vehicle_data' => 'required|array',
            'requirements_data' => 'nullable|array',
        ]);

        $application = $request->user()->applications()->create([
            ...$validated,
            'tribe_id' => $request->user()->tribe_id,
            'status' => 'draft',
        ]);

        $application->timeline()->create([
            'event_type' => 'application_started',
            'description' => 'Application created',
            'performed_by' => $request->user()->id,
        ]);

        return response()->json([
            'application' => ApplicationResource::make($application)->resolve(),
        ], 201);
    }

    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $application->loadMissing([
            'user' => fn ($query) => $query->apiSummary(),
            'vehicle' => fn ($query) => $query->apiDetail(),
            'documents' => fn ($query) => $query->apiList(),
            'payments' => fn ($query) => $query->apiList(),
            'timeline' => fn ($query) => $query
                ->apiList()
                ->orderByDesc('created_at'),
            'timeline.performer' => fn ($query) => $query->apiSummary(),
        ]);
        $application->loadCount(['documents', 'payments']);

        return response()->json([
            'application' => ApplicationResource::make($application)->resolve(),
        ]);
    }

    public function update(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'vehicle_data' => 'sometimes|array',
            'requirements_data' => 'sometimes|array',
        ]);

        $application->update($validated);

        return response()->json([
            'application' => ApplicationResource::make(
                Application::query()
                    ->apiList()
                    ->whereKey($application->id)
                    ->firstOrFail()
            )->resolve(),
        ]);
    }

    public function submit(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (! in_array($application->status, ['draft', 'info_requested'], true)) {
            return response()->json([
                'message' => 'Application cannot be submitted in its current status.',
            ], 422);
        }

        $validated = $request->validate([
            'requirements_data' => 'required|array',
        ]);

        $requiredDocs = ['insurance', 'title', 'tribal_id'];
        $uploadedDocs = $application->documents()
            ->select('document_type')
            ->distinct()
            ->pluck('document_type')
            ->all();

        if (count(array_diff($requiredDocs, $uploadedDocs)) > 0) {
            return response()->json([
                'message' => 'All required documents must be uploaded before submission',
            ], 422);
        }

        $application->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'estimated_completion_date' => now()->addWeekdays(3),
            'requirements_data' => $validated['requirements_data'],
        ]);

        $application->timeline()->create([
            'event_type' => 'application_submitted',
            'description' => 'Application submitted for review',
            'performed_by' => $request->user()->id,
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

    public function timeline(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $timeline = $application->timeline()
            ->apiList()
            ->with([
                'performer' => fn ($query) => $query->apiSummary(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'timeline' => $timeline
                ->map(fn ($entry) => ApplicationTimelineResource::make($entry)->resolve())
                ->values(),
        ]);
    }

    public function cancel(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        if (! in_array($application->status, ['draft', 'submitted'], true)) {
            return response()->json([
                'message' => 'Application cannot be cancelled at this stage',
            ], 422);
        }

        $application->update(['status' => 'cancelled']);

        $application->timeline()->create([
            'event_type' => 'application_cancelled',
            'description' => 'Application cancelled by user',
            'performed_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Application cancelled successfully',
        ]);
    }

    public function destroy(Application $application): JsonResponse
    {
        $this->authorize('delete', $application);

        $application->delete();

        return response()->json([
            'message' => 'Application deleted successfully',
        ]);
    }
}
