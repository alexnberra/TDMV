<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { applicationApi } from '@/lib/api';
import { formatDate, formatDateTime } from '@/lib/ui';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusChip from '@/components/portal/StatusChip.vue';
import TimelineStep from '@/components/portal/TimelineStep.vue';
import AppButton from '@/components/ui/AppButton.vue';

defineOptions({ layout: PortalLayout });

const props = defineProps<{ id: string }>();

interface TimelineEntry {
    id: number;
    event_type: string;
    description: string;
    created_at: string;
}

interface ApplicationRecord {
    id: number;
    case_number: string;
    service_type: string;
    status: string;
    priority: string;
    submitted_at: string | null;
    estimated_completion_date: string | null;
    reviewer_notes: string | null;
    rejection_reason: string | null;
    documents: Array<{ id: number; document_type: string; status: string }>;
    payments: Array<{ id: number; amount: number; status: string; paid_at: string | null }>;
}

const application = ref<ApplicationRecord | null>(null);
const timeline = ref<TimelineEntry[]>([]);
const loading = ref(true);
const actionLoading = ref(false);
const errorMessage = ref('');

const canCancel = computed(() => ['draft', 'submitted'].includes(application.value?.status ?? ''));

const orderedTimeline = computed(() => {
    const list = [...timeline.value].sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());

    return list.map((item, index) => {
        const isLast = index === list.length - 1;
        const status = isLast ? 'current' : 'complete';

        return {
            ...item,
            status,
            isLast,
        };
    });
});

async function loadStatus() {
    loading.value = true;
    errorMessage.value = '';

    try {
        const [applicationResponse, timelineResponse] = await Promise.all([
            applicationApi.one(props.id),
            applicationApi.timeline(props.id),
        ]);

        application.value = applicationResponse.data.application;
        timeline.value = timelineResponse.data.timeline ?? [];
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load application status.';
    } finally {
        loading.value = false;
    }
}

async function cancelApplication() {
    if (!application.value || !canCancel.value) {
        return;
    }

    actionLoading.value = true;
    errorMessage.value = '';

    try {
        await applicationApi.cancel(application.value.id);
        await loadStatus();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to cancel application.';
    } finally {
        actionLoading.value = false;
    }
}

onMounted(loadStatus);
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Application Status</h1>
                    <p class="mt-2 text-sm text-gray-600">Track each stage and see what action is needed next.</p>
                </div>
                <AppButton variant="secondary" @click="loadStatus">Refresh</AppButton>
            </header>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading application status…</p>
            <p v-else-if="errorMessage && !application" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else-if="application">
                <section class="surface-card p-6 md:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ application.service_type.replace('_', ' ') }}</p>
                            <h2 class="mt-1 text-2xl font-bold text-gray-900">Case {{ application.case_number }}</h2>
                            <p class="mt-2 text-sm text-gray-600">
                                Submitted {{ application.submitted_at ? formatDate(application.submitted_at) : 'Not submitted' }}
                                <span v-if="application.estimated_completion_date"> • Est. completion {{ formatDate(application.estimated_completion_date) }}</span>
                            </p>
                        </div>
                        <StatusChip :status="application.status" size="md" />
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Priority</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ application.priority }}</p>
                        </article>
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Documents</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ application.documents.length }}</p>
                        </article>
                        <article class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Payments</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ application.payments.length }}</p>
                        </article>
                    </div>

                    <p v-if="application.reviewer_notes" class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                        {{ application.reviewer_notes }}
                    </p>
                    <p v-if="application.rejection_reason" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                        {{ application.rejection_reason }}
                    </p>
                </section>

                <section class="surface-card p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Timeline</h2>

                    <p v-if="orderedTimeline.length === 0" class="text-sm text-gray-600">No timeline events available yet.</p>

                    <div v-else>
                        <TimelineStep
                            v-for="item in orderedTimeline"
                            :key="item.id"
                            :title="item.event_type.replace('_', ' ')"
                            :description="item.description"
                            :timestamp="formatDateTime(item.created_at)"
                            :status="item.status"
                            :is-last="item.isLast"
                        />
                    </div>
                </section>

                <p v-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</p>

                <div class="flex flex-wrap justify-end gap-2">
                    <Link href="/portal/support" class="focus-ring rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        Contact Support
                    </Link>
                    <AppButton v-if="canCancel" variant="destructive" :loading="actionLoading" @click="cancelApplication">
                        {{ actionLoading ? 'Cancelling...' : 'Cancel Application' }}
                    </AppButton>
                </div>
            </template>
        </div>
    </div>
</template>
