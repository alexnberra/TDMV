<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { applicationApi, vehicleApi } from '@/lib/api';
import { setActiveApplicationId } from '@/composables/useActiveApplication';
import PortalLayout from '@/layouts/PortalLayout.vue';
import AppButton from '@/components/ui/AppButton.vue';

defineOptions({ layout: PortalLayout });

interface Vehicle {
    id: number;
    year: number;
    make: string;
    model: string;
    vin: string;
}

const services = [
    { value: 'renewal', label: 'Registration Renewal', description: 'Renew an existing registration.' },
    { value: 'new_registration', label: 'New Registration', description: 'Register a newly acquired vehicle.' },
    { value: 'title_transfer', label: 'Title Transfer', description: 'Transfer ownership to a new owner.' },
    { value: 'plate_replacement', label: 'Plate Replacement', description: 'Replace lost, stolen, or damaged plates.' },
    { value: 'duplicate_title', label: 'Duplicate Title', description: 'Request a replacement vehicle title.' },
];

const selectedService = ref<string>('renewal');
const selectedVehicleId = ref<number | null>(null);
const vehicles = ref<Vehicle[]>([]);
const manualVehicle = ref({
    vin: '',
    year: '',
    make: '',
    model: '',
    color: '',
});
const loading = ref(true);
const submitting = ref(false);
const errorMessage = ref('');

onMounted(async () => {
    try {
        const response = await vehicleApi.all();
        vehicles.value = response.data.vehicles ?? [];
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load vehicles.';
    } finally {
        loading.value = false;
    }
});

function selectedVehicle(): Vehicle | undefined {
    return vehicles.value.find((vehicle) => vehicle.id === selectedVehicleId.value);
}

function requiresExistingVehicle(): boolean {
    return selectedService.value !== 'new_registration';
}

async function startApplication() {
    errorMessage.value = '';

    if (requiresExistingVehicle() && !selectedVehicleId.value) {
        errorMessage.value = 'Select a vehicle for this service.';
        return;
    }

    if (!requiresExistingVehicle() && (!manualVehicle.value.year || !manualVehicle.value.make || !manualVehicle.value.model || !manualVehicle.value.vin)) {
        errorMessage.value = 'For new registration, provide VIN, year, make, and model.';
        return;
    }

    submitting.value = true;

    try {
        const vehicle = selectedVehicle();
        const vehicleData = requiresExistingVehicle() && vehicle
            ? {
                  vin: vehicle.vin,
                  year: vehicle.year,
                  make: vehicle.make,
                  model: vehicle.model,
              }
            : {
                  vin: manualVehicle.value.vin.toUpperCase(),
                  year: Number.parseInt(manualVehicle.value.year, 10),
                  make: manualVehicle.value.make,
                  model: manualVehicle.value.model,
                  color: manualVehicle.value.color,
              };

        const response = await applicationApi.create({
            service_type: selectedService.value,
            vehicle_id: selectedVehicleId.value,
            vehicle_data: vehicleData,
            requirements_data: {
                required_documents: ['insurance', 'title', 'tribal_id'],
            },
        });

        const applicationId = response.data.application.id as number;
        setActiveApplicationId(applicationId);
        window.location.href = `/portal/requirements?application_id=${applicationId}`;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to start application.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Step 1 of 4</p>
                    <h1 class="mt-1 text-3xl font-bold text-gray-900">Choose your service</h1>
                    <p class="mt-2 text-sm text-gray-600">We will build the right checklist based on the selected service.</p>
                </div>
                <Link href="/portal" class="focus-ring rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    Back
                </Link>
            </header>

            <div class="h-2 rounded-full bg-gray-200">
                <div class="h-full w-1/4 rounded-full bg-blue-600" />
            </div>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading your vehicles…</p>

            <template v-else>
                <section class="surface-card p-5 md:p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">What would you like to do?</h2>
                    <div class="space-y-3">
                        <label
                            v-for="service in services"
                            :key="service.value"
                            class="focus-within:ring-ring flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition"
                            :class="selectedService === service.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                        >
                            <input v-model="selectedService" :value="service.value" type="radio" class="mt-1 h-4 w-4 accent-blue-600" />
                            <div>
                                <p class="text-sm font-semibold text-gray-900 md:text-base">{{ service.label }}</p>
                                <p class="text-sm text-gray-600">{{ service.description }}</p>
                            </div>
                        </label>
                    </div>
                </section>

                <section class="surface-card p-5 md:p-6">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Vehicle information</h2>

                    <div v-if="requiresExistingVehicle()" class="space-y-2">
                        <label for="vehicle" class="text-sm font-semibold text-gray-700">Select existing vehicle</label>
                        <select
                            id="vehicle"
                            v-model.number="selectedVehicleId"
                            class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-sm"
                        >
                            <option :value="null">Choose a vehicle...</option>
                            <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">
                                {{ vehicle.year }} {{ vehicle.make }} {{ vehicle.model }} (ID {{ vehicle.id }})
                            </option>
                        </select>
                    </div>

                    <div v-else class="grid gap-4 md:grid-cols-2">
                        <label class="block text-sm">
                            <span class="mb-1.5 block font-semibold text-gray-700">VIN</span>
                            <input v-model="manualVehicle.vin" maxlength="17" class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-sm" placeholder="17-character VIN" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1.5 block font-semibold text-gray-700">Year</span>
                            <input v-model="manualVehicle.year" type="number" class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-sm" placeholder="2024" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1.5 block font-semibold text-gray-700">Make</span>
                            <input v-model="manualVehicle.make" class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-sm" placeholder="Toyota" />
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1.5 block font-semibold text-gray-700">Model</span>
                            <input v-model="manualVehicle.model" class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-sm" placeholder="Tacoma" />
                        </label>
                    </div>
                </section>

                <p v-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</p>

                <div class="flex justify-end">
                    <AppButton :loading="submitting" size="lg" @click="startApplication">
                        {{ submitting ? 'Starting...' : 'Continue to Requirements' }}
                    </AppButton>
                </div>
            </template>
        </div>
    </div>
</template>
