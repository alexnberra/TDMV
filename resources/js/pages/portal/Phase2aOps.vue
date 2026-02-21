<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { benefitsApi, businessApi, complianceApi, insuranceApi, vehicleApi } from '@/lib/api';
import PortalLayout from '@/layouts/PortalLayout.vue';

defineOptions({ layout: PortalLayout });

interface Vehicle {
    id: number;
    year: number;
    make: string;
    model: string;
    plate_number: string | null;
}

interface BusinessAccount {
    id: number;
    business_name: string;
    business_type: string;
    fleet_vehicles_count?: number;
}

interface InsurancePolicy {
    id: number;
    policy_number: string;
    provider_name: string;
    status: string;
}

interface SimpleRecord {
    id: number;
    status?: string;
    result?: string;
}

const loading = ref(true);
const submitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const vehicles = ref<Vehicle[]>([]);
const businessAccounts = ref<BusinessAccount[]>([]);
const insurancePolicies = ref<InsurancePolicy[]>([]);
const emissionsTests = ref<SimpleRecord[]>([]);
const vehicleInspections = ref<SimpleRecord[]>([]);
const memberBenefits = ref<SimpleRecord[]>([]);
const disabilityPlacards = ref<SimpleRecord[]>([]);

const businessForm = ref({
    business_name: '',
    business_type: 'fleet',
});

const fleetForm = ref({
    business_account_id: '',
    vehicle_id: '',
    status: 'active',
});

const insuranceForm = ref({
    vehicle_id: '',
    provider_name: 'Tribal Mutual',
    policy_number: '',
    effective_date: new Date().toISOString().slice(0, 10),
    expiration_date: new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().slice(0, 10),
});

const emissionsForm = ref({
    vehicle_id: '',
    test_date: new Date().toISOString().slice(0, 10),
    result: 'pass',
});

const inspectionForm = ref({
    vehicle_id: '',
    inspection_date: new Date().toISOString().slice(0, 10),
    result: 'pass',
});

const benefitForm = ref({
    benefit_type: 'veteran',
    status: 'pending',
});

const placardForm = ref({
    vehicle_id: '',
    placard_type: 'temporary',
    status: 'pending',
});

const summaryCards = computed(() => [
    { label: 'Business Accounts', value: businessAccounts.value.length },
    { label: 'Insurance Policies', value: insurancePolicies.value.length },
    { label: 'Emissions Tests', value: emissionsTests.value.length },
    { label: 'Vehicle Inspections', value: vehicleInspections.value.length },
    { label: 'Member Benefits', value: memberBenefits.value.length },
    { label: 'Disability Placards', value: disabilityPlacards.value.length },
]);

async function loadPhase2aData() {
    loading.value = true;
    errorMessage.value = '';

    try {
        const [vehiclesResponse, accountsResponse, insuranceResponse, emissionsResponse, inspectionsResponse, benefitsResponse, placardsResponse] =
            await Promise.all([
                vehicleApi.all(),
                businessApi.all(),
                insuranceApi.all(),
                complianceApi.emissions(),
                complianceApi.inspections(),
                benefitsApi.memberBenefits(),
                benefitsApi.disabilityPlacards(),
            ]);

        vehicles.value = vehiclesResponse.data.vehicles ?? [];
        businessAccounts.value = accountsResponse.data.data ?? [];
        insurancePolicies.value = insuranceResponse.data.data ?? [];
        emissionsTests.value = emissionsResponse.data.data ?? [];
        vehicleInspections.value = inspectionsResponse.data.data ?? [];
        memberBenefits.value = benefitsResponse.data.data ?? [];
        disabilityPlacards.value = placardsResponse.data.data ?? [];
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load business, fleet, and compliance data.';
    } finally {
        loading.value = false;
    }
}

async function createBusinessAccount() {
    if (!businessForm.value.business_name.trim()) {
        errorMessage.value = 'Business name is required.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await businessApi.create({
            business_name: businessForm.value.business_name.trim(),
            business_type: businessForm.value.business_type,
        });
        businessForm.value.business_name = '';
        successMessage.value = 'Business account created.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create business account.';
    } finally {
        submitting.value = false;
    }
}

async function assignFleetVehicle() {
    if (!fleetForm.value.business_account_id || !fleetForm.value.vehicle_id) {
        errorMessage.value = 'Select a business account and vehicle.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await businessApi.assignFleetVehicle(fleetForm.value.business_account_id, {
            vehicle_id: Number(fleetForm.value.vehicle_id),
            status: fleetForm.value.status,
        });
        successMessage.value = 'Vehicle assigned to fleet.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to assign fleet vehicle.';
    } finally {
        submitting.value = false;
    }
}

async function createInsurancePolicy() {
    if (!insuranceForm.value.vehicle_id || !insuranceForm.value.policy_number.trim()) {
        errorMessage.value = 'Vehicle and policy number are required.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await insuranceApi.create({
            vehicle_id: Number(insuranceForm.value.vehicle_id),
            provider_name: insuranceForm.value.provider_name.trim(),
            policy_number: insuranceForm.value.policy_number.trim().toUpperCase(),
            effective_date: insuranceForm.value.effective_date,
            expiration_date: insuranceForm.value.expiration_date,
            status: 'pending',
        });
        insuranceForm.value.policy_number = '';
        successMessage.value = 'Insurance policy submitted.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create insurance policy.';
    } finally {
        submitting.value = false;
    }
}

async function createEmissionsTest() {
    if (!emissionsForm.value.vehicle_id) {
        errorMessage.value = 'Vehicle is required for emissions tests.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await complianceApi.createEmissions({
            vehicle_id: Number(emissionsForm.value.vehicle_id),
            test_date: emissionsForm.value.test_date,
            result: emissionsForm.value.result,
        });
        successMessage.value = 'Emissions test submitted.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create emissions test.';
    } finally {
        submitting.value = false;
    }
}

async function createInspection() {
    if (!inspectionForm.value.vehicle_id) {
        errorMessage.value = 'Vehicle is required for inspections.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await complianceApi.createInspection({
            vehicle_id: Number(inspectionForm.value.vehicle_id),
            inspection_date: inspectionForm.value.inspection_date,
            result: inspectionForm.value.result,
        });
        successMessage.value = 'Vehicle inspection submitted.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create vehicle inspection.';
    } finally {
        submitting.value = false;
    }
}

async function createBenefit() {
    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await benefitsApi.createMemberBenefit(benefitForm.value);
        successMessage.value = 'Benefit request submitted.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create member benefit.';
    } finally {
        submitting.value = false;
    }
}

async function createPlacard() {
    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await benefitsApi.createDisabilityPlacard({
            vehicle_id: placardForm.value.vehicle_id ? Number(placardForm.value.vehicle_id) : null,
            placard_type: placardForm.value.placard_type,
            status: placardForm.value.status,
        });
        successMessage.value = 'Disability placard request submitted.';
        await loadPhase2aData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create disability placard.';
    } finally {
        submitting.value = false;
    }
}

onMounted(loadPhase2aData);
</script>

<template>
    <div class="p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Business, Fleet & Compliance</h1>
                <p class="text-gray-600">Fleet, insurance, compliance, and member benefit operations.</p>
            </div>
            <Link href="/portal" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Back to Dashboard
            </Link>
        </div>

        <p v-if="loading" class="text-gray-600">Loading business, fleet, and compliance data...</p>

        <template v-else>
            <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-6">
                <div v-for="card in summaryCards" :key="card.label" class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ card.label }}</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ card.value }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Business & Fleet</h2>
                    <div class="space-y-3">
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Business Name</span>
                            <input
                                v-model="businessForm.business_name"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                                placeholder="Doe Family Logistics"
                            />
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Business Type</span>
                            <select
                                v-model="businessForm.business_type"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                            >
                                <option value="fleet">Fleet</option>
                                <option value="tribal_business">Tribal Business</option>
                                <option value="commercial">Commercial</option>
                                <option value="non_profit">Non Profit</option>
                            </select>
                        </label>
                        <button
                            type="button"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-blue-300"
                            :disabled="submitting"
                            @click="createBusinessAccount"
                        >
                            Create Business Account
                        </button>
                    </div>

                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <h3 class="mb-2 text-sm font-semibold text-gray-700">Assign Fleet Vehicle</h3>
                        <div class="space-y-3">
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">Business Account</span>
                                <select
                                    v-model="fleetForm.business_account_id"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                                >
                                    <option value="">Select business account</option>
                                    <option v-for="account in businessAccounts" :key="account.id" :value="String(account.id)">
                                        {{ account.business_name }}
                                    </option>
                                </select>
                            </label>
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">Vehicle</span>
                                <select
                                    v-model="fleetForm.vehicle_id"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                                >
                                    <option value="">Select vehicle</option>
                                    <option v-for="vehicle in vehicles" :key="vehicle.id" :value="String(vehicle.id)">
                                        {{ vehicle.year }} {{ vehicle.make }} {{ vehicle.model }} ({{ vehicle.plate_number ?? 'No plate' }})
                                    </option>
                                </select>
                            </label>
                            <button
                                type="button"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
                                :disabled="submitting"
                                @click="assignFleetVehicle"
                            >
                                Assign to Fleet
                            </button>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Insurance & Compliance</h2>
                    <div class="space-y-3">
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Insurance Vehicle</span>
                            <select
                                v-model="insuranceForm.vehicle_id"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                            >
                                <option value="">Select vehicle</option>
                                <option v-for="vehicle in vehicles" :key="vehicle.id" :value="String(vehicle.id)">
                                    {{ vehicle.year }} {{ vehicle.make }} {{ vehicle.model }}
                                </option>
                            </select>
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Policy Number</span>
                            <input
                                v-model="insuranceForm.policy_number"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                                placeholder="POL-FTN-0001"
                            />
                        </label>
                        <button
                            type="button"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-blue-300"
                            :disabled="submitting"
                            @click="createInsurancePolicy"
                        >
                            Submit Insurance
                        </button>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-700">Emissions Test</p>
                            <select v-model="emissionsForm.vehicle_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Vehicle</option>
                                <option v-for="vehicle in vehicles" :key="`e-${vehicle.id}`" :value="String(vehicle.id)">
                                    {{ vehicle.make }} {{ vehicle.model }}
                                </option>
                            </select>
                            <button
                                type="button"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-100"
                                :disabled="submitting"
                                @click="createEmissionsTest"
                            >
                                Submit Emissions
                            </button>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-700">Vehicle Inspection</p>
                            <select v-model="inspectionForm.vehicle_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Vehicle</option>
                                <option v-for="vehicle in vehicles" :key="`i-${vehicle.id}`" :value="String(vehicle.id)">
                                    {{ vehicle.make }} {{ vehicle.model }}
                                </option>
                            </select>
                            <button
                                type="button"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm hover:bg-gray-100"
                                :disabled="submitting"
                                @click="createInspection"
                            >
                                Submit Inspection
                            </button>
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 lg:col-span-2">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Benefits & Placards</h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-3">
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">Benefit Type</span>
                                <select v-model="benefitForm.benefit_type" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                    <option value="elder">Elder</option>
                                    <option value="veteran">Veteran</option>
                                    <option value="disabled">Disabled</option>
                                    <option value="military_active">Military Active</option>
                                </select>
                            </label>
                            <button
                                type="button"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
                                :disabled="submitting"
                                @click="createBenefit"
                            >
                                Request Benefit
                            </button>
                        </div>

                        <div class="space-y-3">
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">Placard Vehicle (optional)</span>
                                <select v-model="placardForm.vehicle_id" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                    <option value="">No linked vehicle</option>
                                    <option v-for="vehicle in vehicles" :key="`p-${vehicle.id}`" :value="String(vehicle.id)">
                                        {{ vehicle.year }} {{ vehicle.make }} {{ vehicle.model }}
                                    </option>
                                </select>
                            </label>
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">Placard Type</span>
                                <select v-model="placardForm.placard_type" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                    <option value="temporary">Temporary</option>
                                    <option value="permanent">Permanent</option>
                                    <option value="veteran_disabled">Veteran Disabled</option>
                                </select>
                            </label>
                            <button
                                type="button"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
                                :disabled="submitting"
                                @click="createPlacard"
                            >
                                Request Placard
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <div v-if="errorMessage" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
                {{ successMessage }}
            </div>
        </template>
    </div>
</template>
