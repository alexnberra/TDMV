<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { adminApi, assistantApi } from '@/lib/api';
import PortalLayout from '@/layouts/PortalLayout.vue';

defineOptions({ layout: PortalLayout });

interface AssistantResponse {
    interaction_id: number;
    intent: string;
    message: string;
    data: Record<string, unknown>;
    suggestions: string[];
    response_time_ms: number;
}

interface InsightsResponse {
    applications: {
        by_status: Record<string, number>;
        pending_review: number;
        at_risk_cases: number;
    };
    vehicles: {
        expiring_within_30_days: number;
        expiring_within_7_days: number;
        expired_active: number;
    };
    appointments: {
        next_14_days_total: number;
        next_14_days_by_day: Array<{ day: string; total: number }>;
    };
    assistant: {
        interactions_today: number;
        interactions_last_7_days: number;
    };
    automation: {
        active_rules: number;
        rules: Array<{
            id: number;
            key: string;
            name: string;
            is_active: boolean;
            last_run_at: string | null;
            run_count: number;
        }>;
        dry_run_preview: {
            matched_count: number;
            updated_count: number;
            results: Array<{
                rule_key: string;
                rule_name: string;
                matched_count: number;
                updated_count: number;
            }>;
        };
    };
}

interface AutomationResult {
    dry_run: boolean;
    matched_count: number;
    updated_count: number;
    results: Array<{
        rule_key: string;
        rule_name: string;
        matched_count: number;
        updated_count: number;
    }>;
}

const page = usePage();
const role = computed(() => (page.props.auth?.user as { role?: string } | undefined)?.role ?? 'member');
const canUseAdminOps = computed(() => role.value === 'admin' || role.value === 'staff');

const assistantQuery = ref('');
const assistantLoading = ref(false);
const assistantError = ref('');
const assistantResult = ref<AssistantResponse | null>(null);

const insightsLoading = ref(false);
const insightsError = ref('');
const insights = ref<InsightsResponse | null>(null);

const automationLoading = ref(false);
const automationError = ref('');
const automationResult = ref<AutomationResult | null>(null);

const topStatuses = computed(() => {
    const byStatus = insights.value?.applications.by_status ?? {};

    return Object.entries(byStatus)
        .map(([status, count]) => ({ status, count }))
        .sort((a, b) => b.count - a.count)
        .slice(0, 4);
});

async function askAssistant() {
    if (!assistantQuery.value.trim()) {
        assistantError.value = 'Ask a question first.';
        return;
    }

    assistantLoading.value = true;
    assistantError.value = '';

    try {
        const response = await assistantApi.query({
            query: assistantQuery.value.trim(),
            channel: 'portal',
            context: { section: 'phase-3' },
        });
        assistantResult.value = response.data as AssistantResponse;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        assistantError.value = message ?? 'Unable to get assistant response.';
    } finally {
        assistantLoading.value = false;
    }
}

async function loadInsights() {
    if (!canUseAdminOps.value) {
        return;
    }

    insightsLoading.value = true;
    insightsError.value = '';

    try {
        const response = await adminApi.phase3Insights();
        insights.value = response.data as InsightsResponse;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        insightsError.value = message ?? 'Unable to load AI operations insights.';
    } finally {
        insightsLoading.value = false;
    }
}

async function runAutomation(dryRun: boolean) {
    if (!canUseAdminOps.value) {
        return;
    }

    automationLoading.value = true;
    automationError.value = '';

    try {
        const response = await adminApi.runPhase3Automation({ dry_run: dryRun });
        automationResult.value = response.data as AutomationResult;
        await loadInsights();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        automationError.value = message ?? 'Unable to execute automation.';
    } finally {
        automationLoading.value = false;
    }
}

onMounted(loadInsights);
</script>

<template>
    <div class="p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">AI Assistant & Automation</h1>
                <p class="text-gray-600">AI assistant, automation rules, and admin insights.</p>
            </div>
            <Link href="/portal" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Back to Dashboard
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <section class="rounded-xl border border-gray-200 bg-white p-5 xl:col-span-1">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Portal Assistant</h2>
                <label class="text-sm">
                    <span class="mb-1 block text-gray-700">Ask about applications, renewals, appointments, or payments</span>
                    <textarea
                        v-model="assistantQuery"
                        rows="5"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                        placeholder="Example: What is the status of my latest application?"
                    />
                </label>
                <button
                    type="button"
                    class="mt-3 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-blue-300"
                    :disabled="assistantLoading"
                    @click="askAssistant"
                >
                    {{ assistantLoading ? 'Thinking...' : 'Ask Assistant' }}
                </button>
                <p v-if="assistantError" class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ assistantError }}
                </p>
                <div v-if="assistantResult" class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <p class="text-sm font-semibold text-blue-900">Intent: {{ assistantResult.intent }}</p>
                    <p class="mt-2 text-sm text-blue-900">{{ assistantResult.message }}</p>
                    <p class="mt-2 text-xs text-blue-700">Response time: {{ assistantResult.response_time_ms }}ms</p>
                    <div v-if="assistantResult.suggestions?.length" class="mt-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Suggestions</p>
                        <ul class="mt-1 space-y-1 text-sm text-blue-900">
                            <li v-for="suggestion in assistantResult.suggestions" :key="suggestion">{{ suggestion }}</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 xl:col-span-2">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Admin Insights & Automation</h2>
                    <div v-if="canUseAdminOps" class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
                            :disabled="insightsLoading || automationLoading"
                            @click="loadInsights"
                        >
                            Refresh
                        </button>
                        <button
                            type="button"
                            class="rounded-md border border-blue-300 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50"
                            :disabled="automationLoading"
                            @click="runAutomation(true)"
                        >
                            Dry Run
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:bg-blue-300"
                            :disabled="automationLoading"
                            @click="runAutomation(false)"
                        >
                            Apply Automation
                        </button>
                    </div>
                </div>

                <p v-if="!canUseAdminOps" class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Admin insights are available to staff and admin accounts.
                </p>

                <p v-else-if="insightsLoading" class="text-sm text-gray-600">Loading AI operations insights...</p>
                <p v-else-if="insightsError" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ insightsError }}
                </p>

                <template v-else-if="insights">
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">At-Risk Cases</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ insights.applications.at_risk_cases }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Pending Review</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ insights.applications.pending_review }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Expiring (30d)</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ insights.vehicles.expiring_within_30_days }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Assistant (7d)</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900">{{ insights.assistant.interactions_last_7_days }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Application Status Distribution</p>
                            <div class="space-y-1 text-sm">
                                <p v-for="bucket in topStatuses" :key="bucket.status" class="flex items-center justify-between text-gray-700">
                                    <span class="uppercase">{{ bucket.status }}</span>
                                    <span class="font-semibold">{{ bucket.count }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Upcoming Appointment Demand (14d)</p>
                            <p class="mb-2 text-sm text-gray-700">Total: {{ insights.appointments.next_14_days_total }}</p>
                            <div class="max-h-44 space-y-1 overflow-auto text-sm text-gray-700">
                                <p
                                    v-for="demandPoint in insights.appointments.next_14_days_by_day"
                                    :key="demandPoint.day"
                                    class="flex items-center justify-between"
                                >
                                    <span>{{ demandPoint.day }}</span>
                                    <span class="font-semibold">{{ demandPoint.total }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-lg border border-gray-200 p-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Workflow Rules</p>
                        <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                            <article v-for="rule in insights.automation.rules" :key="rule.id" class="rounded-md border border-gray-200 p-3">
                                <p class="font-semibold text-gray-900">{{ rule.name }}</p>
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ rule.key }}</p>
                                <p class="mt-1 text-gray-700">Run count: {{ rule.run_count }}</p>
                                <p class="text-gray-700">Last run: {{ rule.last_run_at ?? 'Never' }}</p>
                            </article>
                        </div>
                    </div>
                </template>

                <p v-if="automationError" class="mt-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ automationError }}
                </p>

                <div v-if="automationResult" class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <p class="text-sm font-semibold text-blue-900">
                        Automation {{ automationResult.dry_run ? 'Dry Run' : 'Execution' }}:
                        matched {{ automationResult.matched_count }},
                        updated {{ automationResult.updated_count }}
                    </p>
                    <div class="mt-2 space-y-1 text-sm text-blue-900">
                        <p v-for="result in automationResult.results" :key="result.rule_key">
                            {{ result.rule_name }}: matched {{ result.matched_count }}, updated {{ result.updated_count }}
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
