<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { applicationApi } from '@/lib/api';
import { getActiveApplicationId } from '@/composables/useActiveApplication';
import PortalLayout from '@/layouts/PortalLayout.vue';
import ChecklistItem from '@/components/portal/ChecklistItem.vue';

defineOptions({ layout: PortalLayout });

interface DocumentRecord {
    id: number;
    document_type: string;
    status: string;
    uploaded_at: string | null;
}

interface ApplicationRecord {
    id: number;
    case_number: string;
    service_type: string;
    status: string;
    documents: DocumentRecord[];
}

const application = ref<ApplicationRecord | null>(null);
const loading = ref(true);
const errorMessage = ref('');

const requiredDocs = [
    { type: 'insurance', helper: 'Upload a current policy card or declaration page.' },
    { type: 'title', helper: 'Provide front and back if available.' },
    { type: 'tribal_id', helper: 'Must be valid and readable.' },
];

const uploadedByType = computed(() => {
    const map = new Map<string, DocumentRecord>();

    for (const document of application.value?.documents ?? []) {
        if (!map.has(document.document_type)) {
            map.set(document.document_type, document);
        }
    }

    return map;
});

const checklist = computed(() => {
    return requiredDocs.map((item) => {
        const document = uploadedByType.value.get(item.type);
        const hasDoc = !!document;

        return {
            type: item.type,
            helper: item.helper,
            uploaded: hasDoc,
            status: hasDoc
                ? document?.status === 'rejected'
                    ? 'needs-review'
                    : 'complete'
                : 'incomplete',
            rawStatus: document?.status ?? 'missing',
        };
    });
});

const completedCount = computed(() => checklist.value.filter((item) => item.uploaded).length);
const progress = computed(() => Math.round((completedCount.value / checklist.value.length) * 100));
const allRequiredUploaded = computed(() => checklist.value.every((item) => item.uploaded));

const applicationId = getActiveApplicationId();

onMounted(async () => {
    if (!applicationId) {
        errorMessage.value = 'No active application selected. Start a service first.';
        loading.value = false;
        return;
    }

    try {
        const response = await applicationApi.one(applicationId);
        application.value = response.data.application;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load application requirements.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Step 2 of 4</p>
                    <h1 class="mt-1 text-3xl font-bold text-gray-900">Requirements Checklist</h1>
                    <p class="mt-2 text-sm text-gray-600">Complete all required items before review and payment.</p>
                </div>
                <Link href="/portal/service-selector" class="focus-ring rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Back
                </Link>
            </header>

            <div class="h-2 rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-blue-600" :style="{ width: '50%' }" />
            </div>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading requirements…</p>
            <p v-else-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else-if="application">
                <section class="surface-card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Case {{ application.case_number }}</p>
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ application.service_type.replace('_', ' ') }} • {{ application.status }}</p>
                        </div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ completedCount }}/{{ checklist.length }} complete
                        </span>
                    </div>

                    <div class="mt-4">
                        <div class="h-2 rounded-full bg-gray-200">
                            <div class="h-full rounded-full bg-blue-600 transition-all" :style="{ width: `${progress}%` }" />
                        </div>
                        <p class="mt-2 text-xs text-gray-600">{{ progress }}% complete</p>
                    </div>
                </section>

                <section class="space-y-3">
                    <ChecklistItem
                        v-for="item in checklist"
                        :key="item.type"
                        :title="item.type.replace('_', ' ').replace(/\b\w/g, (s) => s.toUpperCase())"
                        :description="item.uploaded ? `Uploaded (${item.rawStatus})` : 'Document required before submission'"
                        :helper-text="item.helper"
                        :status="item.status"
                    >
                        <template #action>
                            <Link
                                :href="`/portal/upload?application_id=${application.id}`"
                                class="focus-ring touch-target rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                            >
                                {{ item.uploaded ? 'Update' : 'Upload' }}
                            </Link>
                        </template>
                    </ChecklistItem>
                </section>

                <div class="flex flex-wrap justify-end gap-2">
                    <Link :href="`/portal/upload?application_id=${application.id}`" class="focus-ring rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        Manage Uploads
                    </Link>
                    <Link
                        :href="`/portal/review?application_id=${application.id}`"
                        class="focus-ring rounded-lg px-4 py-2 text-sm font-semibold text-white"
                        :class="allRequiredUploaded ? 'bg-blue-600 hover:bg-blue-700' : 'cursor-not-allowed bg-blue-300'"
                    >
                        Continue to Review
                    </Link>
                </div>
            </template>
        </div>
    </div>
</template>
