<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { applicationApi, paymentApi } from '@/lib/api';
import { clearActiveApplicationId, getActiveApplicationId } from '@/composables/useActiveApplication';
import PortalLayout from '@/layouts/PortalLayout.vue';
import FeeSummaryCard from '@/components/portal/FeeSummaryCard.vue';
import AppButton from '@/components/ui/AppButton.vue';

defineOptions({ layout: PortalLayout });

interface PaymentRecord {
    id: number;
    amount: number;
    status: string;
    paid_at: string | null;
}

interface ApplicationRecord {
    id: number;
    case_number: string;
    service_type: string;
    status: string;
    vehicle_data?: {
        year?: number;
        make?: string;
        model?: string;
    };
    payments: PaymentRecord[];
}

const application = ref<ApplicationRecord | null>(null);
const loading = ref(true);
const processing = ref(false);
const errorMessage = ref('');
const paymentMethod = ref<'card' | 'ach'>('card');
const acceptedTerms = ref(false);

const applicationId = getActiveApplicationId();

const feeBreakdown = computed<Record<string, number>>(() => {
    switch (application.value?.service_type) {
        case 'new_registration':
            return { registration: 65, plate: 25, processing: 8 };
        case 'title_transfer':
            return { transfer: 55, processing: 7 };
        case 'plate_replacement':
            return { plate: 20, processing: 5 };
        case 'duplicate_title':
            return { title: 35, processing: 5 };
        default:
            return { registration: 45, plate: 15, processing: 5 };
    }
});

onMounted(async () => {
    if (!applicationId) {
        errorMessage.value = 'No active application selected. Start from service selector.';
        loading.value = false;
        return;
    }

    try {
        const response = await applicationApi.one(applicationId);
        application.value = response.data.application;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load application.';
    } finally {
        loading.value = false;
    }
});

async function completeApplication() {
    if (!application.value) {
        return;
    }

    if (!acceptedTerms.value) {
        errorMessage.value = 'You must accept terms to continue.';
        return;
    }

    processing.value = true;
    errorMessage.value = '';

    try {
        if ((application.value.payments?.length ?? 0) === 0) {
            await paymentApi.create(application.value.id, {
                payment_method: paymentMethod.value,
                payment_token: 'tok_demo_visa',
                fee_breakdown: feeBreakdown.value,
            });
        }

        await applicationApi.submit(application.value.id, {
            requirements_data: {
                payment_completed: true,
                accepted_terms: true,
            },
        });

        clearActiveApplicationId();
        window.location.href = `/portal/status/${application.value.id}`;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to complete application.';
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Step 4 of 4</p>
                    <h1 class="mt-1 text-3xl font-bold text-gray-900">Review & Payment</h1>
                    <p class="mt-2 text-sm text-gray-600">Confirm details, review fees, and submit your application.</p>
                </div>
                <Link href="/portal/upload" class="focus-ring rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Back to Uploads
                </Link>
            </header>

            <div class="h-2 rounded-full bg-gray-200">
                <div class="h-full rounded-full bg-blue-600" :style="{ width: '100%' }" />
            </div>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading review details…</p>
            <p v-else-if="errorMessage && !application" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else-if="application">
                <div class="grid gap-5 lg:grid-cols-3">
                    <section class="surface-card p-5 lg:col-span-2">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Application summary</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <dt class="text-gray-500">Case Number</dt>
                                <dd class="font-semibold text-gray-900">{{ application.case_number }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <dt class="text-gray-500">Service</dt>
                                <dd class="font-semibold text-gray-900">{{ application.service_type.replace('_', ' ') }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <dt class="text-gray-500">Vehicle</dt>
                                <dd class="font-semibold text-gray-900">
                                    {{ application.vehicle_data?.year }} {{ application.vehicle_data?.make }} {{ application.vehicle_data?.model }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Payment status</dt>
                                <dd class="font-semibold text-gray-900">{{ application.payments?.length ? 'Pending final submit' : 'Not paid yet' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6">
                            <p class="mb-2 text-sm font-semibold text-gray-800">Payment method</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="focus-ring rounded-lg border px-4 py-2 text-sm font-semibold"
                                    :class="paymentMethod === 'card' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700'"
                                    @click="paymentMethod = 'card'"
                                >
                                    Card
                                </button>
                                <button
                                    type="button"
                                    class="focus-ring rounded-lg border px-4 py-2 text-sm font-semibold"
                                    :class="paymentMethod === 'ach' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700'"
                                    @click="paymentMethod = 'ach'"
                                >
                                    ACH
                                </button>
                            </div>
                        </div>

                        <label class="mt-6 flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                            <input v-model="acceptedTerms" type="checkbox" class="mt-0.5 h-4 w-4 accent-blue-600" />
                            <span>
                                I certify the provided information is accurate and I agree to the service terms for this application.
                            </span>
                        </label>
                    </section>

                    <FeeSummaryCard :fees="feeBreakdown" />
                </div>

                <p v-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</p>

                <div class="flex justify-end">
                    <AppButton :loading="processing" size="lg" :disabled="!acceptedTerms" @click="completeApplication">
                        {{ processing ? 'Processing...' : 'Pay and Submit Application' }}
                    </AppButton>
                </div>
            </template>
        </div>
    </div>
</template>
