<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\RunAutomationRequest;
use App\Services\Phase3\AutomationService;
use Illuminate\Http\JsonResponse;

class Phase3AutomationController extends Controller
{
    public function run(RunAutomationRequest $request, AutomationService $automationService): JsonResponse
    {
        $validated = $request->validated();

        $result = $automationService->runForUser(
            actor: $request->user(),
            dryRun: (bool) ($validated['dry_run'] ?? true),
            ruleKeys: $validated['rule_keys'] ?? null,
        );

        return response()->json($result);
    }
}
