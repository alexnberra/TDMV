<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useApiAuth } from '@/composables/useApiAuth';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';

defineOptions({ layout: MarketingLayout });

const { register, isAuthenticated, ensureUser } = useApiAuth();

const form = reactive({
    tribe_id: '1',
    tribal_enrollment_id: '',
    first_name: '',
    last_name: '',
    date_of_birth: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    address_line1: '',
    city: '',
    state: '',
    zip_code: '',
});

const loading = ref(false);
const errorMessage = ref('');

const passwordMismatch = computed(() => {
    return !!form.password_confirmation && form.password !== form.password_confirmation;
});

onMounted(async () => {
    await ensureUser();

    if (isAuthenticated.value) {
        window.location.href = '/portal';
    }
});

async function handleSubmit(event: Event) {
    event.preventDefault();

    if (passwordMismatch.value) {
        errorMessage.value = 'Passwords do not match.';
        return;
    }

    loading.value = true;
    errorMessage.value = '';

    try {
        await register({ ...form, tribe_id: Number(form.tribe_id) });
        window.location.href = '/portal';
    } catch (error: any) {
        errorMessage.value = error.response?.data?.message || 'Unable to create account.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="px-4 py-10 md:py-14">
        <div class="mx-auto max-w-4xl rounded-2xl border border-gray-200 bg-white p-6 shadow-md md:p-8">
            <header class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Create your account</h1>
                <p class="mt-2 text-sm text-gray-600">Complete the form below to start using Tribal Vehicle Services.</p>
            </header>

            <form class="space-y-6" @submit="handleSubmit" novalidate>
                <section>
                    <h2 class="mb-3 border-b border-gray-200 pb-2 text-base font-semibold text-gray-900">Personal information</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <AppInput id="first_name" v-model="form.first_name" label="First name" required autocomplete="given-name" />
                        <AppInput id="last_name" v-model="form.last_name" label="Last name" required autocomplete="family-name" />
                        <AppInput id="tribal_enrollment_id" v-model="form.tribal_enrollment_id" label="Tribal enrollment ID" required />
                        <AppInput id="date_of_birth" v-model="form.date_of_birth" type="date" label="Date of birth" required />
                    </div>
                </section>

                <section>
                    <h2 class="mb-3 border-b border-gray-200 pb-2 text-base font-semibold text-gray-900">Contact details</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <AppInput id="email" v-model="form.email" type="email" label="Email" required autocomplete="email" />
                        <AppInput id="phone" v-model="form.phone" type="tel" label="Phone" required autocomplete="tel" />
                    </div>
                </section>

                <section>
                    <h2 class="mb-3 border-b border-gray-200 pb-2 text-base font-semibold text-gray-900">Address</h2>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-3">
                            <AppInput id="address_line1" v-model="form.address_line1" label="Street address" required autocomplete="address-line1" />
                        </div>
                        <AppInput id="city" v-model="form.city" label="City" required autocomplete="address-level2" />
                        <AppInput id="state" v-model="form.state" label="State" required autocomplete="address-level1" />
                        <AppInput id="zip_code" v-model="form.zip_code" label="ZIP code" required autocomplete="postal-code" />
                    </div>
                </section>

                <section>
                    <h2 class="mb-3 border-b border-gray-200 pb-2 text-base font-semibold text-gray-900">Security</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <AppInput id="password" v-model="form.password" type="password" label="Password" required autocomplete="new-password" />
                        <AppInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            label="Confirm password"
                            required
                            autocomplete="new-password"
                            :error="passwordMismatch ? 'Passwords do not match.' : ''"
                        />
                    </div>
                </section>

                <p v-if="errorMessage" role="alert" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ errorMessage }}
                </p>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-600">
                        Already registered?
                        <Link href="/login" class="font-semibold text-blue-700 hover:text-blue-800">Log in</Link>
                    </p>
                    <AppButton type="submit" :loading="loading" size="lg">
                        {{ loading ? 'Creating account...' : 'Create account' }}
                    </AppButton>
                </div>
            </form>
        </div>
    </section>
</template>
