<?php

namespace App\Services\Phase3;

use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\User;
use App\Models\WorkflowRule;
use Illuminate\Support\Facades\DB;

class AutomationService
{
    /**
     * @param  array<int, string>|null  $ruleKeys
     * @return array<string, mixed>
     */
    public function runForUser(User $actor, bool $dryRun = true, ?array $ruleKeys = null): array
    {
        $rules = WorkflowRule::query()
            ->forTribe($actor->tribe_id)
            ->active()
            ->when($ruleKeys !== null && $ruleKeys !== [], function ($query) use ($ruleKeys): void {
                $query->whereIn('key', $ruleKeys);
            })
            ->orderBy('id')
            ->get();

        $results = [];
        $totalMatched = 0;
        $totalUpdated = 0;

        foreach ($rules as $rule) {
            $result = $this->executeRule($rule, $actor, $dryRun);
            $totalMatched += $result['matched_count'];
            $totalUpdated += $result['updated_count'];
            $results[] = $result;
        }

        return [
            'dry_run' => $dryRun,
            'rule_count' => $rules->count(),
            'matched_count' => $totalMatched,
            'updated_count' => $totalUpdated,
            'results' => $results,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executeRule(WorkflowRule $rule, User $actor, bool $dryRun): array
    {
        return match ($rule->key) {
            'auto_approve_simple_renewals' => $this->runAutoApproveSimpleRenewals($rule, $actor, $dryRun),
            default => [
                'rule_key' => $rule->key,
                'rule_name' => $rule->name,
                'matched_count' => 0,
                'updated_count' => 0,
                'matched_application_ids' => [],
                'notes' => ['Rule is active but has no executor configured yet.'],
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function runAutoApproveSimpleRenewals(WorkflowRule $rule, User $actor, bool $dryRun): array
    {
        $config = (array) ($rule->config ?? []);
        $requiredDocumentTypes = array_values(array_unique((array) ($config['required_documents'] ?? ['insurance', 'title', 'tribal_id'])));
        $requireCompletedPayment = (bool) ($config['require_completed_payment'] ?? true);
        $maxVehicleAgeYears = (int) ($config['max_vehicle_age_years'] ?? 20);
        $maxBatch = max(1, (int) ($config['max_batch'] ?? 100));

        $applications = Application::query()
            ->apiList()
            ->where('tribe_id', $actor->tribe_id)
            ->where('service_type', 'renewal')
            ->where('status', 'submitted')
            ->with([
                'vehicle' => fn ($query) => $query->select(['id', 'year', 'registration_status']),
                'documents' => fn ($query) => $query
                    ->select(['id', 'application_id', 'document_type', 'status'])
                    ->whereIn('document_type', $requiredDocumentTypes),
                'payments' => fn ($query) => $query->select(['id', 'application_id', 'status']),
            ])
            ->orderBy('submitted_at')
            ->limit($maxBatch)
            ->get();

        $matched = $applications->filter(function (Application $application) use (
            $requiredDocumentTypes,
            $requireCompletedPayment,
            $maxVehicleAgeYears
        ): bool {
            if (! $application->vehicle || $application->vehicle->registration_status === 'suspended') {
                return false;
            }

            $vehicleAge = max(0, now()->year - (int) $application->vehicle->year);
            if ($maxVehicleAgeYears > 0 && $vehicleAge > $maxVehicleAgeYears) {
                return false;
            }

            $acceptedDocuments = $application->documents
                ->filter(fn ($document) => $document->status === 'accepted')
                ->pluck('document_type')
                ->unique()
                ->values()
                ->all();

            if (array_diff($requiredDocumentTypes, $acceptedDocuments) !== []) {
                return false;
            }

            if ($requireCompletedPayment) {
                $hasCompletedPayment = $application->payments
                    ->contains(fn ($payment) => $payment->status === 'completed');

                if (! $hasCompletedPayment) {
                    return false;
                }
            }

            return true;
        })->values();

        $matchedIds = $matched->pluck('id')->map(fn ($id) => (int) $id)->all();
        $updatedCount = 0;

        if (! $dryRun && $matched->isNotEmpty()) {
            DB::transaction(function () use ($matched, $actor, $rule, &$updatedCount): void {
                foreach ($matched as $application) {
                    $application->update([
                        'status' => 'approved',
                        'reviewed_at' => now(),
                        'reviewed_by' => $actor->id,
                        'reviewer_notes' => 'Approved by workflow automation rule.',
                    ]);

                    ApplicationTimeline::create([
                        'application_id' => $application->id,
                        'event_type' => 'workflow_auto_approved',
                        'description' => "Automatically approved by workflow rule: {$rule->name}",
                        'performed_by' => $actor->id,
                        'metadata' => [
                            'rule_key' => $rule->key,
                            'rule_id' => $rule->id,
                        ],
                    ]);
                }

                $updatedCount = $matched->count();
            });
        }

        if (! $dryRun) {
            $rule->forceFill([
                'last_run_at' => now(),
                'run_count' => $rule->run_count + 1,
                'updated_by' => $actor->id,
            ])->save();
        }

        return [
            'rule_key' => $rule->key,
            'rule_name' => $rule->name,
            'matched_count' => count($matchedIds),
            'updated_count' => $updatedCount,
            'matched_application_ids' => $matchedIds,
            'notes' => [
                'required_documents' => $requiredDocumentTypes,
                'require_completed_payment' => $requireCompletedPayment,
                'max_vehicle_age_years' => $maxVehicleAgeYears,
            ],
        ];
    }

    public function dryRunSummary(User $actor): array
    {
        $summary = $this->runForUser($actor, true);
        $summary['results'] = collect($summary['results'])
            ->map(fn (array $result): array => [
                'rule_key' => $result['rule_key'],
                'rule_name' => $result['rule_name'],
                'matched_count' => $result['matched_count'],
                'updated_count' => $result['updated_count'],
            ])
            ->values()
            ->all();

        return $summary;
    }
}
