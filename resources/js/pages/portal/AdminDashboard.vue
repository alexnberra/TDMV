<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { adminApi } from '@/lib/api';
import PortalLayout from '@/layouts/PortalLayout.vue';

defineOptions({ layout: PortalLayout });

interface DashboardStats {
    pending_applications: number;
    total_revenue: number;
    expiring_soon: number;
    applications_by_status: Array<{ status: string; count: number }>;
}

interface AdminApplication {
    id: number;
    case_number: string;
    service_type: string;
    status: string;
    user?: {
        first_name?: string;
        last_name?: string;
        name?: string;
        email?: string;
    };
    created_at: string;
}

interface AdminApplicationResponse {
    data: AdminApplication[];
}

const stats = ref<DashboardStats | null>(null);
const applications = ref<AdminApplication[]>([]);
const loading = ref(true);
const updatingId = ref<number | null>(null);
const errorMessage = ref('');
const successMessage = ref('');
const search = ref('');
const selectedStatus = ref('');

const applicationStatuses = ['submitted', 'under_review', 'info_requested', 'approved', 'rejected', 'completed'];

const filteredApplications = computed(() => applications.value);

function userName(application: AdminApplication): string {
    const first = application.user?.first_name ?? '';
    const last = application.user?.last_name ?? '';
    const fullName = `${first} ${last}`.trim();
    return fullName || application.user?.name || 'Unknown User';
}

async function loadAdminData() {
    loading.value = true;
    errorMessage.value = '';

    try {
        const [statsResponse, applicationsResponse] = await Promise.all([
            adminApi.stats(),
            adminApi.applications({
                ...(search.value ? { search: search.value } : {}),
                ...(selectedStatus.value ? { status: selectedStatus.value } : {}),
            }),
        ]);

        stats.value = statsResponse.data as DashboardStats;
        applications.value = (applicationsResponse.data as AdminApplicationResponse).data;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load admin dashboard';
    } finally {
        loading.value = false;
    }
}

async function updateStatus(applicationId: number, status: string) {
    updatingId.value = applicationId;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await adminApi.updateApplicationStatus(applicationId, {
            status,
            reviewer_notes: `Updated to ${status} via admin dashboard.`,
        });
        successMessage.value = `Application ${applicationId} updated to ${status}.`;
        await loadAdminData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to update status';
    } finally {
        updatingId.value = null;
    }
}

async function requestInfo(applicationId: number) {
    updatingId.value = applicationId;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await adminApi.requestMoreInfo(applicationId, 'Please provide updated proof of residency and insurance.');
        successMessage.value = `Information request sent for application ${applicationId}.`;
        await loadAdminData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to request more information';
    } finally {
        updatingId.value = null;
    }
}

onMounted(loadAdminData);
</script>

<template>
    <div class="p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
            <Link href="/portal/phase-3" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Open AI Ops
            </Link>
        </div>

        <p v-if="loading" class="text-gray-600">Loading admin stats...</p>
        <p v-else-if="errorMessage && !stats" class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">{{ errorMessage }}</p>

        <template v-else>
            <div v-if="stats" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Pending Applications</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_applications }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-semibold text-gray-900">${{ Number(stats.total_revenue).toFixed(2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Expiring in 30 Days</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ stats.expiring_soon }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-sm text-gray-500">Status Buckets</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ stats.applications_by_status.length }}</p>
                </div>
            </div>

            <section class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <input
                        v-model="search"
                        type="search"
                        class="w-full max-w-sm rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Search case # or applicant..."
                    />
                    <select
                        v-model="selectedStatus"
                        class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                    >
                        <option value="">All statuses</option>
                        <option v-for="status in applicationStatuses" :key="status" :value="status">{{ status }}</option>
                    </select>
                    <button type="button" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="loadAdminData">
                        Apply
                    </button>
                </div>

                <div v-if="filteredApplications.length === 0" class="text-sm text-gray-600">No applications found.</div>

                <div v-else class="space-y-3">
                    <article v-for="application in filteredApplications" :key="application.id" class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ application.case_number }}</h3>
                                <p class="text-sm text-gray-700">{{ userName(application) }} • {{ application.service_type }}</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">
                                {{ application.status }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="status in ['under_review', 'approved', 'rejected', 'completed']"
                                :key="status"
                                type="button"
                                class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="updatingId === application.id"
                                @click="updateStatus(application.id, status)"
                            >
                                Mark {{ status }}
                            </button>
                            <button
                                type="button"
                                class="rounded-md border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="updatingId === application.id"
                                @click="requestInfo(application.id)"
                            >
                                Request Info
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <div v-if="errorMessage" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
                {{ successMessage }}
            </div>
        </template>
    </div>
</template>
