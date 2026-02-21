<?php

namespace App\Services\Phase3;

use App\Models\Application;
use App\Models\Appointment;
use App\Models\AssistantInteraction;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AssistantService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function respond(User $user, string $query, array $options = []): array
    {
        $startedAt = hrtime(true);

        $applicationId = Arr::get($options, 'application_id');
        $context = Arr::get($options, 'context', []);
        $channel = (string) Arr::get($options, 'channel', 'portal');
        $intent = $this->detectIntent($query);

        $resolved = match ($intent) {
            'appointment_help' => $this->buildAppointmentResponse($user),
            'application_status' => $this->buildApplicationResponse($user, $applicationId),
            'renewal_help' => $this->buildRenewalResponse($user),
            'household_help' => $this->buildHouseholdResponse($user),
            'payment_help' => $this->buildPaymentResponse($user),
            default => $this->buildGeneralResponse($user),
        };

        $responseTimeMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        $interaction = AssistantInteraction::create([
            'tribe_id' => $user->tribe_id,
            'user_id' => $user->id,
            'application_id' => $applicationId,
            'channel' => $channel,
            'intent' => $intent,
            'query_text' => trim($query),
            'response_text' => $resolved['message'],
            'context' => is_array($context) ? $context : null,
            'response_time_ms' => $responseTimeMs,
            'metadata' => [
                'suggestions' => $resolved['suggestions'] ?? [],
                'data_points' => array_keys((array) ($resolved['data'] ?? [])),
            ],
        ]);

        return [
            'interaction_id' => $interaction->id,
            'intent' => $intent,
            'message' => $resolved['message'],
            'data' => $resolved['data'] ?? [],
            'suggestions' => $resolved['suggestions'] ?? [],
            'response_time_ms' => $responseTimeMs,
        ];
    }

    private function detectIntent(string $query): string
    {
        $normalized = Str::of($query)->lower()->value();

        if (Str::contains($normalized, ['appointment', 'schedule', 'visit', 'road test'])) {
            return 'appointment_help';
        }

        if (Str::contains($normalized, ['status', 'application', 'case', 'submitted', 'review'])) {
            return 'application_status';
        }

        if (Str::contains($normalized, ['renew', 'renewal', 'expire', 'expiration', 'registration'])) {
            return 'renewal_help';
        }

        if (Str::contains($normalized, ['household', 'family', 'minor', 'guardian'])) {
            return 'household_help';
        }

        if (Str::contains($normalized, ['payment', 'receipt', 'fee', 'amount'])) {
            return 'payment_help';
        }

        return 'general_help';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRenewalResponse(User $user): array
    {
        $vehicles = Vehicle::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->where('owner_id', $user->id)
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();

        $expiringSoon = $vehicles->filter(function (Vehicle $vehicle): bool {
            return (bool) ($vehicle->expiration_date?->isFuture() && $vehicle->expiration_date->lte(now()->addDays(30)));
        });
        $expired = $vehicles->filter(fn (Vehicle $vehicle): bool => (bool) $vehicle->expiration_date?->isPast());

        if ($vehicles->isEmpty()) {
            return [
                'message' => 'No vehicles were found on your account yet. You can start with a new registration.',
                'data' => ['vehicle_count' => 0, 'expiring_soon' => 0, 'expired' => 0],
                'suggestions' => ['Start a new registration', 'Upload title and insurance documents'],
            ];
        }

        return [
            'message' => sprintf(
                'You have %d vehicle(s). %d are expiring within 30 days and %d are already expired.',
                $vehicles->count(),
                $expiringSoon->count(),
                $expired->count()
            ),
            'data' => [
                'vehicle_count' => $vehicles->count(),
                'expiring_soon' => $expiringSoon->count(),
                'expired' => $expired->count(),
                'vehicles' => $this->mapVehiclesForResponse($vehicles),
            ],
            'suggestions' => ['Open Vehicle 2 profile', 'Start renewal workflow', 'Check required renewal documents'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildApplicationResponse(User $user, mixed $applicationId): array
    {
        $query = Application::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->where('user_id', $user->id)
            ->latest('id');

        if ($applicationId !== null) {
            $query->whereKey((int) $applicationId);
        }

        $applications = $query->limit(5)->get();
        $openStatuses = ['draft', 'submitted', 'under_review', 'info_requested', 'approved'];
        $openCount = $applications->whereIn('status', $openStatuses)->count();
        $latest = $applications->first();

        if (! $latest) {
            return [
                'message' => 'You do not have any applications yet.',
                'data' => ['open_count' => 0, 'applications' => []],
                'suggestions' => ['Start a new application', 'Use service selector to begin'],
            ];
        }

        return [
            'message' => "Your latest case {$latest->case_number} is currently {$latest->status}.",
            'data' => [
                'open_count' => $openCount,
                'latest_case_number' => $latest->case_number,
                'latest_status' => $latest->status,
                'applications' => $applications->map(fn (Application $application): array => [
                    'id' => $application->id,
                    'case_number' => $application->case_number,
                    'service_type' => $application->service_type,
                    'status' => $application->status,
                    'submitted_at' => $application->submitted_at,
                    'estimated_completion_date' => $application->estimated_completion_date,
                ])->values(),
            ],
            'suggestions' => ['View application timeline', 'Upload requested documents', 'Contact support for case help'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAppointmentResponse(User $user): array
    {
        $appointments = Appointment::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->where('user_id', $user->id)
            ->where('scheduled_for', '>=', now()->subDay())
            ->orderBy('scheduled_for')
            ->limit(5)
            ->get();

        if ($appointments->isEmpty()) {
            return [
                'message' => 'You do not have upcoming appointments.',
                'data' => ['upcoming_count' => 0, 'appointments' => []],
                'suggestions' => ['Schedule an office visit', 'Check office locations and wait times'],
            ];
        }

        $next = $appointments->first();

        return [
            'message' => "Your next appointment is {$next->appointment_type} on {$next->scheduled_for?->format('M j, Y g:i A')}.",
            'data' => [
                'upcoming_count' => $appointments->count(),
                'next_appointment' => [
                    'id' => $next->id,
                    'appointment_type' => $next->appointment_type,
                    'status' => $next->status,
                    'scheduled_for' => $next->scheduled_for,
                    'confirmation_code' => $next->confirmation_code,
                ],
                'appointments' => $appointments->map(fn (Appointment $appointment): array => [
                    'id' => $appointment->id,
                    'appointment_type' => $appointment->appointment_type,
                    'status' => $appointment->status,
                    'scheduled_for' => $appointment->scheduled_for,
                    'confirmation_code' => $appointment->confirmation_code,
                ])->values(),
            ],
            'suggestions' => ['Reschedule appointment', 'Cancel appointment', 'Review appointment checklist'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildHouseholdResponse(User $user): array
    {
        $ownedHouseholdIds = Household::query()
            ->where('tribe_id', $user->tribe_id)
            ->where('owner_user_id', $user->id)
            ->pluck('id');

        $memberHouseholdIds = HouseholdMember::query()
            ->where('user_id', $user->id)
            ->pluck('household_id');

        $managedHouseholdIds = $ownedHouseholdIds
            ->merge($memberHouseholdIds)
            ->unique()
            ->values();

        $minorCount = 0;
        if ($managedHouseholdIds->isNotEmpty()) {
            $minorCount = HouseholdMember::query()
                ->whereIn('household_id', $managedHouseholdIds)
                ->where('is_minor', true)
                ->count();
        }

        return [
            'message' => sprintf(
                'You are linked to %d household(s), with %d minor member profile(s).',
                $managedHouseholdIds->count(),
                $minorCount
            ),
            'data' => [
                'households_linked' => $managedHouseholdIds->count(),
                'minor_members' => $minorCount,
            ],
            'suggestions' => ['Open household management', 'Add a family member', 'Manage minor vehicle permissions'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPaymentResponse(User $user): array
    {
        $payments = Payment::query()
            ->apiList()
            ->where('tribe_id', $user->tribe_id)
            ->where('user_id', $user->id)
            ->latest('paid_at')
            ->limit(5)
            ->get();

        $totalCompleted = Payment::query()
            ->where('tribe_id', $user->tribe_id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->where('paid_at', '>=', now()->subYear())
            ->sum('amount');

        if ($payments->isEmpty()) {
            return [
                'message' => 'No payment history is available yet.',
                'data' => ['recent_payments' => [], 'total_paid_last_12_months' => 0],
                'suggestions' => ['Start a new service', 'Review current fees'],
            ];
        }

        return [
            'message' => 'Your payment history is up to date. You can download receipts from completed applications.',
            'data' => [
                'total_paid_last_12_months' => (float) $totalCompleted,
                'recent_payments' => $payments->map(fn (Payment $payment): array => [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'paid_at' => $payment->paid_at,
                ])->values(),
            ],
            'suggestions' => ['Open latest receipt', 'Review fee breakdown', 'Contact billing support'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildGeneralResponse(User $user): array
    {
        $vehicleCount = Vehicle::query()
            ->where('tribe_id', $user->tribe_id)
            ->where('owner_id', $user->id)
            ->count();

        $openApplicationCount = Application::query()
            ->where('tribe_id', $user->tribe_id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'info_requested', 'approved'])
            ->count();

        return [
            'message' => "You currently have {$vehicleCount} vehicle(s) and {$openApplicationCount} open application(s).",
            'data' => [
                'vehicle_count' => $vehicleCount,
                'open_application_count' => $openApplicationCount,
            ],
            'suggestions' => [
                'Ask about application status',
                'Ask about renewals expiring soon',
                'Ask about appointment scheduling',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapVehiclesForResponse(Collection $vehicles): array
    {
        return $vehicles->map(fn (Vehicle $vehicle): array => [
            'id' => $vehicle->id,
            'year' => $vehicle->year,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'plate_number' => $vehicle->plate_number,
            'registration_status' => $vehicle->registration_status,
            'expiration_date' => $vehicle->expiration_date,
            'days_until_expiration' => $vehicle->days_until_expiration,
            'is_expiring_soon' => $vehicle->is_expiring_soon,
            'is_expired' => $vehicle->is_expired,
        ])->values()->all();
    }
}
