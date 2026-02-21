<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { appointmentApi, householdApi, referenceApi } from '@/lib/api';
import PortalLayout from '@/layouts/PortalLayout.vue';

defineOptions({ layout: PortalLayout });

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
}

interface HouseholdMember {
    id: number;
    relationship_type: string;
    is_minor: boolean;
    user?: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
    };
}

interface Household {
    id: number;
    household_name: string;
    city: string | null;
    state: string | null;
    appointments_count?: number;
    members?: HouseholdMember[];
}

interface Appointment {
    id: number;
    appointment_type: string;
    status: string;
    scheduled_for: string;
    confirmation_code: string;
    office_location?: {
        id: number;
        name: string;
    };
}

const loading = ref(true);
const submitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const households = ref<Household[]>([]);
const appointments = ref<Appointment[]>([]);
const officeLocations = ref<OfficeLocation[]>([]);

const householdForm = ref({
    household_name: '',
    city: '',
    state: '',
});

const memberForm = ref({
    household_id: '',
    user_id: '',
    relationship_type: 'spouse',
    is_minor: false,
});

const appointmentForm = ref({
    household_id: '',
    office_location_id: '',
    appointment_type: 'dmv_office_visit',
    scheduled_for: '',
    notes: '',
});

const cards = computed(() => [
    { label: 'Households', value: households.value.length },
    { label: 'Appointments', value: appointments.value.length },
    {
        label: 'Upcoming',
        value: appointments.value.filter((appointment) => new Date(appointment.scheduled_for).getTime() > Date.now()).length,
    },
    {
        label: 'Needs Action',
        value: appointments.value.filter((appointment) => ['requested', 'rescheduled'].includes(appointment.status)).length,
    },
]);

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString();
}

async function loadData() {
    loading.value = true;
    errorMessage.value = '';

    try {
        const [householdsResponse, appointmentsResponse, officeLocationsResponse] = await Promise.all([
            householdApi.all(),
            appointmentApi.all(),
            referenceApi.officeLocations(),
        ]);

        households.value = householdsResponse.data.data ?? [];
        appointments.value = appointmentsResponse.data.data ?? [];
        officeLocations.value = officeLocationsResponse.data.locations ?? [];
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load household and appointment data.';
    } finally {
        loading.value = false;
    }
}

async function createHousehold() {
    if (!householdForm.value.household_name.trim()) {
        errorMessage.value = 'Household name is required.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await householdApi.create({
            household_name: householdForm.value.household_name.trim(),
            city: householdForm.value.city.trim() || null,
            state: householdForm.value.state.trim() || null,
        });

        householdForm.value.household_name = '';
        householdForm.value.city = '';
        householdForm.value.state = '';

        successMessage.value = 'Household created successfully.';
        await loadData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create household.';
    } finally {
        submitting.value = false;
    }
}

async function addMember() {
    if (!memberForm.value.household_id || !memberForm.value.user_id) {
        errorMessage.value = 'Select household and member user ID.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await householdApi.addMember(memberForm.value.household_id, {
            user_id: Number(memberForm.value.user_id),
            relationship_type: memberForm.value.relationship_type,
            is_minor: memberForm.value.is_minor,
            can_manage_minor_vehicles: memberForm.value.relationship_type === 'guardian',
        });

        memberForm.value.user_id = '';
        successMessage.value = 'Household member added.';
        await loadData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to add household member.';
    } finally {
        submitting.value = false;
    }
}

async function createAppointment() {
    if (!appointmentForm.value.scheduled_for) {
        errorMessage.value = 'Appointment date/time is required.';
        return;
    }

    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await appointmentApi.create({
            household_id: appointmentForm.value.household_id ? Number(appointmentForm.value.household_id) : null,
            office_location_id: appointmentForm.value.office_location_id ? Number(appointmentForm.value.office_location_id) : null,
            appointment_type: appointmentForm.value.appointment_type,
            scheduled_for: new Date(appointmentForm.value.scheduled_for).toISOString(),
            notes: appointmentForm.value.notes.trim() || null,
        });

        appointmentForm.value.scheduled_for = '';
        appointmentForm.value.notes = '';
        successMessage.value = 'Appointment created.';
        await loadData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to create appointment.';
    } finally {
        submitting.value = false;
    }
}

async function cancelAppointment(appointmentId: number) {
    submitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await appointmentApi.cancel(appointmentId, 'Cancelled from member portal.');
        successMessage.value = `Appointment ${appointmentId} cancelled.`;
        await loadData();
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to cancel appointment.';
    } finally {
        submitting.value = false;
    }
}

onMounted(loadData);
</script>

<template>
    <div class="p-4 md:p-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Households & Appointments</h1>
                <p class="text-gray-600">Household management and appointment scheduling workflows.</p>
            </div>
            <Link href="/portal" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                Back to Dashboard
            </Link>
        </div>

        <p v-if="loading" class="text-gray-600">Loading household and appointment data...</p>

        <template v-else>
            <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div v-for="card in cards" :key="card.label" class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ card.label }}</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ card.value }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Create Household</h2>
                    <div class="space-y-3">
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Household Name</span>
                            <input
                                v-model="householdForm.household_name"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none"
                                placeholder="Doe Household"
                            />
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">City</span>
                                <input v-model="householdForm.city" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                            </label>
                            <label class="text-sm">
                                <span class="mb-1 block text-gray-700">State</span>
                                <input v-model="householdForm.state" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                            </label>
                        </div>
                        <button
                            type="button"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-blue-300"
                            :disabled="submitting"
                            @click="createHousehold"
                        >
                            Create Household
                        </button>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Add Household Member</h2>
                    <div class="space-y-3">
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Household</span>
                            <select v-model="memberForm.household_id" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                <option value="">Select household</option>
                                <option v-for="household in households" :key="household.id" :value="String(household.id)">
                                    {{ household.household_name }}
                                </option>
                            </select>
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Member User ID</span>
                            <input v-model="memberForm.user_id" class="w-full rounded-md border border-gray-300 px-3 py-2" placeholder="2" />
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Relationship</span>
                            <select v-model="memberForm.relationship_type" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                <option value="spouse">Spouse</option>
                                <option value="child">Child</option>
                                <option value="guardian">Guardian</option>
                                <option value="parent">Parent</option>
                                <option value="sibling">Sibling</option>
                                <option value="other">Other</option>
                            </select>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input v-model="memberForm.is_minor" type="checkbox" />
                            Member is a minor
                        </label>
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50"
                            :disabled="submitting"
                            @click="addMember"
                        >
                            Add Member
                        </button>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-5 lg:col-span-2">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Schedule Appointment</h2>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Household (optional)</span>
                            <select v-model="appointmentForm.household_id" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                <option value="">None</option>
                                <option v-for="household in households" :key="`household-${household.id}`" :value="String(household.id)">
                                    {{ household.household_name }}
                                </option>
                            </select>
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Office Location</span>
                            <select v-model="appointmentForm.office_location_id" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                <option value="">None</option>
                                <option v-for="office in officeLocations" :key="office.id" :value="String(office.id)">
                                    {{ office.name }}
                                </option>
                            </select>
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Type</span>
                            <select v-model="appointmentForm.appointment_type" class="w-full rounded-md border border-gray-300 px-3 py-2">
                                <option value="dmv_office_visit">DMV Office Visit</option>
                                <option value="road_test">Road Test</option>
                                <option value="vehicle_inspection">Vehicle Inspection</option>
                                <option value="photo_signature_update">Photo/Signature Update</option>
                                <option value="document_review">Document Review</option>
                                <option value="title_signing">Title Signing</option>
                                <option value="plate_pickup">Plate Pickup</option>
                                <option value="virtual_consultation">Virtual Consultation</option>
                            </select>
                        </label>
                        <label class="text-sm">
                            <span class="mb-1 block text-gray-700">Scheduled For</span>
                            <input v-model="appointmentForm.scheduled_for" type="datetime-local" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                        </label>
                    </div>
                    <label class="mt-3 block text-sm">
                        <span class="mb-1 block text-gray-700">Notes</span>
                        <textarea v-model="appointmentForm.notes" rows="2" class="w-full rounded-md border border-gray-300 px-3 py-2" />
                    </label>
                    <button
                        type="button"
                        class="mt-3 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:bg-blue-300"
                        :disabled="submitting"
                        @click="createAppointment"
                    >
                        Schedule Appointment
                    </button>
                </section>
            </div>

            <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Appointments</h2>
                <div class="space-y-3">
                    <article
                        v-for="appointment in appointments.slice(0, 12)"
                        :key="appointment.id"
                        class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 p-4"
                    >
                        <div>
                            <p class="font-semibold text-gray-900">{{ appointment.appointment_type }}</p>
                            <p class="text-sm text-gray-600">
                                {{ formatDateTime(appointment.scheduled_for) }}
                                <span v-if="appointment.office_location"> • {{ appointment.office_location.name }}</span>
                            </p>
                            <p class="text-xs text-gray-500">Confirmation: {{ appointment.confirmation_code }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-700">
                                {{ appointment.status }}
                            </span>
                            <button
                                v-if="!['cancelled', 'completed', 'no_show'].includes(appointment.status)"
                                type="button"
                                class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-50"
                                :disabled="submitting"
                                @click="cancelAppointment(appointment.id)"
                            >
                                Cancel
                            </button>
                        </div>
                    </article>
                    <p v-if="appointments.length === 0" class="text-sm text-gray-600">No appointments found.</p>
                </div>
            </section>

            <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Households</h2>
                <div class="space-y-3">
                    <article v-for="household in households" :key="household.id" class="rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">{{ household.household_name }}</p>
                                <p class="text-sm text-gray-600">{{ household.city ?? 'N/A' }} {{ household.state ?? '' }}</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                {{ household.members?.length ?? 0 }} members
                            </span>
                        </div>
                    </article>
                    <p v-if="households.length === 0" class="text-sm text-gray-600">No households found.</p>
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
