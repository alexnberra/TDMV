<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { applicationApi, vehicleApi } from '@/lib/api';
import { formatDate } from '@/lib/ui';
import PortalLayout from '@/layouts/PortalLayout.vue';
import ActionCard from '@/components/portal/ActionCard.vue';
import StatusChip from '@/components/portal/StatusChip.vue';
import VehicleCard from '@/components/portal/VehicleCard.vue';

defineOptions({ layout: PortalLayout });

interface Vehicle {
    id: number;
    year: number;
    make: string;
    model: string;
    plate_number: string | null;
    registration_status: string;
    expiration_date: string | null;
    days_until_expiration?: number | null;
}

interface Application {
    id: number;
    case_number: string;
    service_type: string;
    status: string;
    submitted_at: string | null;
}

const vehicles = ref<Vehicle[]>([]);
const applications = ref<Application[]>([]);
const loading = ref(true);
const errorMessage = ref('');

const page = usePage();
const user = page.props.auth?.user as { first_name?: string } | undefined;

const expiringCount = computed(() => {
    return vehicles.value.filter((vehicle) => (vehicle.days_until_expiration ?? 999) <= 30).length;
});

const stats = computed(() => {
    return [
        { label: 'Active Vehicles', value: vehicles.value.length },
        { label: 'Pending Applications', value: applications.value.filter((item) => item.status !== 'completed').length },
        { label: 'Expiring Soon', value: expiringCount.value },
        { label: 'Recent Cases', value: applications.value.slice(0, 5).length },
    ];
});

const primaryActions = [
    {
        href: '/portal/service-selector?service=renewal',
        title: 'Renew Tag',
        description: 'Renew registration for an existing vehicle',
        icon: '🔁',
        iconClass: 'bg-blue-100 text-blue-700',
    },
    {
        href: '/portal/service-selector?service=new_registration',
        title: 'New Registration',
        description: 'Start a first-time registration flow',
        icon: '🆕',
        iconClass: 'bg-emerald-100 text-emerald-700',
    },
    {
        href: '/portal/service-selector?service=title_transfer',
        title: 'Title Transfer',
        description: 'Transfer ownership between parties',
        icon: '📄',
        iconClass: 'bg-violet-100 text-violet-700',
    },
    {
        href: '/portal/upload',
        title: 'Upload Documents',
        description: 'Submit required records for open cases',
        icon: '📎',
        iconClass: 'bg-amber-100 text-amber-700',
    },
];

onMounted(async () => {
    try {
        const [vehicleResponse, applicationResponse] = await Promise.all([vehicleApi.all(), applicationApi.all()]);

        vehicles.value = vehicleResponse.data.vehicles ?? [];
        applications.value = applicationResponse.data.data ?? [];
    } catch (error: any) {
        errorMessage.value = error.response?.data?.message || 'Unable to load dashboard data.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <section class="rounded-b-3xl bg-gradient-to-br from-blue-600 to-blue-800 px-4 pb-10 pt-8 text-white md:px-8 md:pt-10">
            <div class="mx-auto max-w-6xl">
                <p class="text-sm text-blue-100">Welcome back,</p>
                <h1 class="mt-1 text-3xl font-bold md:text-4xl">{{ user?.first_name ?? 'Member' }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-blue-100 md:text-base">Track vehicles, complete service requests, and follow your case timeline from one place.</p>
            </div>
        </section>

        <div class="mx-auto -mt-6 max-w-6xl space-y-6 px-4 pb-8 md:px-8">
            <section class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <article v-for="card in stats" :key="card.label" class="surface-card p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ card.label }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ card.value }}</p>
                </article>
            </section>

            <p v-if="loading" class="rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-600">Loading dashboard…</p>
            <p v-else-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else>
                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="section-title">Primary Actions</h2>
                        <Link href="/portal/service-selector" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Open service intake</Link>
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <Link v-for="action in primaryActions" :key="action.title" :href="action.href" class="focus-ring rounded-2xl">
                            <ActionCard :title="action.title" :description="action.description" :icon="action.icon" :icon-class="action.iconClass" />
                        </Link>
                    </div>
                </section>

                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="section-title">My Vehicles</h2>
                        <Link href="/portal/vehicle/2" class="text-sm font-semibold text-blue-700 hover:text-blue-800">Go to Vehicle 2</Link>
                    </div>
                    <div v-if="vehicles.length === 0" class="surface-card p-6 text-sm text-gray-600">
                        No vehicles found yet. Start a new registration to add your first vehicle.
                    </div>
                    <div v-else class="grid gap-3 md:grid-cols-2">
                        <Link v-for="vehicle in vehicles" :key="vehicle.id" :href="`/portal/vehicle/${vehicle.id}`" class="focus-ring rounded-2xl">
                            <VehicleCard
                                :id="vehicle.id"
                                :year="vehicle.year"
                                :make="vehicle.make"
                                :model="vehicle.model"
                                :plate-number="vehicle.plate_number"
                                :status="vehicle.registration_status"
                                :expiration-date="vehicle.expiration_date"
                                :days-until-expiration="vehicle.days_until_expiration ?? null"
                            />
                        </Link>
                    </div>
                </section>

                <section>
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="section-title">Recent Applications</h2>
                        <Link href="/portal/notifications" class="text-sm font-semibold text-blue-700 hover:text-blue-800">View alerts</Link>
                    </div>
                    <div v-if="applications.length === 0" class="surface-card p-6 text-sm text-gray-600">No applications created yet.</div>
                    <div v-else class="space-y-3">
                        <Link
                            v-for="application in applications.slice(0, 5)"
                            :key="application.id"
                            :href="`/portal/status/${application.id}`"
                            class="focus-ring flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 hover:border-blue-300"
                        >
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ application.case_number }}</p>
                                <p class="mt-0.5 text-xs text-gray-600">
                                    {{ application.service_type.replace('_', ' ') }} •
                                    {{ application.submitted_at ? formatDate(application.submitted_at) : 'Draft' }}
                                </p>
                            </div>
                            <StatusChip :status="application.status" />
                        </Link>
                    </div>
                </section>
            </template>
        </div>
    </div>
</template>
