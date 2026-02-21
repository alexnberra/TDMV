<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { vehicleApi } from '@/lib/api';
import { formatDate } from '@/lib/ui';
import PortalLayout from '@/layouts/PortalLayout.vue';
import StatusChip from '@/components/portal/StatusChip.vue';

defineOptions({ layout: PortalLayout });

const props = defineProps<{ id: string | number }>();

interface Vehicle {
    id: number;
    vin: string;
    plate_number: string | null;
    year: number;
    make: string;
    model: string;
    color: string;
    vehicle_type: string;
    registration_status: string;
    registration_date: string | null;
    expiration_date: string | null;
    mileage: number | null;
    is_expiring_soon?: boolean;
    days_until_expiration?: number | null;
}

const loading = ref(true);
const vehicle = ref<Vehicle | null>(null);
const errorMessage = ref('');

onMounted(async () => {
    try {
        const response = await vehicleApi.one(props.id);
        vehicle.value = response.data.vehicle;
    } catch (error: any) {
        errorMessage.value = error.response?.data?.message || 'Unable to load vehicle.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading vehicle profile…</p>
            <p v-else-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else-if="vehicle">
                <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 to-blue-800 p-6 text-white md:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-blue-100">Vehicle Profile</p>
                            <h1 class="mt-1 text-3xl font-bold md:text-4xl">{{ vehicle.year }} {{ vehicle.make }} {{ vehicle.model }}</h1>
                            <p class="mt-2 text-sm text-blue-100">Plate {{ vehicle.plate_number || 'Pending assignment' }}</p>
                        </div>
                        <Link href="/portal" class="focus-ring rounded-lg border border-white/30 px-3 py-2 text-sm font-semibold text-white hover:bg-white/10">
                            Back to Dashboard
                        </Link>
                    </div>
                </section>

                <section
                    v-if="vehicle.days_until_expiration !== null && vehicle.days_until_expiration !== undefined && vehicle.days_until_expiration <= 30"
                    class="-mt-3 rounded-xl border border-amber-300 bg-amber-50 p-4"
                >
                    <p class="text-sm font-semibold text-amber-900">Registration expiring soon</p>
                    <p class="mt-1 text-sm text-amber-800">
                        Expires in {{ vehicle.days_until_expiration }} day(s)
                        <span v-if="vehicle.expiration_date">on {{ formatDate(vehicle.expiration_date) }}</span>.
                    </p>
                    <Link href="/portal/service-selector?service=renewal" class="focus-ring mt-3 inline-flex rounded-lg bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700">
                        Renew now
                    </Link>
                </section>

                <div class="grid gap-4 md:grid-cols-2">
                    <section class="surface-card p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Registration</h2>
                            <StatusChip :status="vehicle.registration_status" />
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">Plate</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.plate_number || 'Pending' }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">Registration Date</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.registration_date ? formatDate(vehicle.registration_date) : 'N/A' }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Expiration</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.expiration_date ? formatDate(vehicle.expiration_date) : 'N/A' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="surface-card p-5">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Vehicle Details</h2>
                        <dl class="space-y-2 text-sm">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">VIN</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.vin }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">Type</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.vehicle_type }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">Color</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.color }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500">Mileage</dt>
                                <dd class="font-medium text-gray-900">{{ vehicle.mileage ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </section>
                </div>
            </template>
        </div>
    </div>
</template>
