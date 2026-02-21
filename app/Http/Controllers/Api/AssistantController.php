<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Assistant\AssistantQueryRequest;
use App\Models\Application;
use App\Services\Phase3\AssistantService;
use Illuminate\Http\JsonResponse;

class AssistantController extends Controller
{
    public function query(AssistantQueryRequest $request, AssistantService $assistantService): JsonResponse
    {
        $user = $request->user();
        $applicationId = $request->validated('application_id');

        if ($applicationId !== null) {
            $applicationExists = Application::query()
                ->whereKey((int) $applicationId)
                ->where('tribe_id', $user->tribe_id)
                ->where(function ($query) use ($user): void {
                    if ($user->isStaff()) {
                        return;
                    }

                    $query->where('user_id', $user->id);
                })
                ->exists();

            if (! $applicationExists) {
                return response()->json([
                    'message' => 'Application context is invalid for this account.',
                ], 422);
            }
        }

        $payload = $assistantService->respond(
            user: $user,
            query: (string) $request->validated('query'),
            options: [
                'application_id' => $applicationId,
                'channel' => $request->validated('channel', 'portal'),
                'context' => $request->validated('context', []),
            ],
        );

        return response()->json($payload);
    }
}
