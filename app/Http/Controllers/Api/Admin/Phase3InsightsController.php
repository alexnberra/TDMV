<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Appointment;
use App\Models\AssistantInteraction;
use App\Models\Vehicle;
use App\Models\WorkflowRule;
use App\Services\Phase3\AutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Phase3InsightsController extends Controller
{
    public function stats(Request $request, AutomationService $automationService): JsonResponse
    {
        $user = $request->user();

        $applicationStatus = Application::query()
            ->where('tribe_id', $user->tribe_id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $atRiskCases = Application::query()
            ->where('tribe_id', $user->tribe_id)
            ->whereIn('status', ['submitted', 'under_review', 'info_requested'])
            ->where(function ($query): void {
                $query->where('priority', 'urgent')
                    ->orWhere('submitted_at', '<=', now()->subDays(5));
            })
            ->count();

        $appointmentsByDay = Appointment::query()
            ->where('tribe_id', $user->tribe_id)
            ->whereBetween('scheduled_for', [now(), now()->addDays(14)])
            ->selectRaw('date(scheduled_for) as day, count(*) as total')
            ->groupBy(DB::raw('date(scheduled_for)'))
            ->orderBy(DB::raw('date(scheduled_for)'))
            ->get()
            ->map(fn ($row): array => ['day' => $row->day, 'total' => (int) $row->total])
            ->values();

        $workflowRules = WorkflowRule::query()
            ->forTribe($user->tribe_id)
            ->select(['id', 'key', 'name', 'is_active', 'last_run_at', 'run_count', 'updated_at'])
            ->orderBy('id')
            ->get();

        $assistantToday = AssistantInteraction::query()
            ->forTribe($user->tribe_id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $assistantSevenDays = AssistantInteraction::query()
            ->forTribe($user->tribe_id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $automationPreview = $automationService->dryRunSummary($user);

        return response()->json([
            'applications' => [
                'by_status' => $applicationStatus,
                'pending_review' => (int) ($applicationStatus['submitted'] ?? 0),
                'at_risk_cases' => $atRiskCases,
            ],
            'vehicles' => [
                'expiring_within_30_days' => Vehicle::query()
                    ->where('tribe_id', $user->tribe_id)
                    ->expiringSoon(30)
                    ->count(),
                'expiring_within_7_days' => Vehicle::query()
                    ->where('tribe_id', $user->tribe_id)
                    ->expiringSoon(7)
                    ->count(),
                'expired_active' => Vehicle::query()
                    ->where('tribe_id', $user->tribe_id)
                    ->expired()
                    ->count(),
            ],
            'appointments' => [
                'next_14_days_total' => (int) $appointmentsByDay->sum('total'),
                'next_14_days_by_day' => $appointmentsByDay,
            ],
            'assistant' => [
                'interactions_today' => $assistantToday,
                'interactions_last_7_days' => $assistantSevenDays,
            ],
            'automation' => [
                'active_rules' => $workflowRules->where('is_active', true)->count(),
                'rules' => $workflowRules,
                'dry_run_preview' => $automationPreview,
            ],
        ]);
    }
}
