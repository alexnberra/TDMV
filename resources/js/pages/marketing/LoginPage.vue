<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useApiAuth } from '@/composables/useApiAuth';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';

defineOptions({ layout: MarketingLayout });

const { login, isAuthenticated, ensureUser } = useApiAuth();

const form = reactive({
    email: '',
    password: '',
});

const loading = ref(false);
const errorMessage = ref('');

onMounted(async () => {
    await ensureUser();

    if (isAuthenticated.value) {
        window.location.href = '/portal';
    }
});

async function handleSubmit(event: Event) {
    event.preventDefault();
    loading.value = true;
    errorMessage.value = '';

    try {
        await login(form.email.trim(), form.password);
        window.location.href = '/portal';
    } catch (error: any) {
        const message = typeof error?.response?.data?.message === 'string' ? error.response.data.message : '';
        const status = error?.response?.status;

        errorMessage.value = message || (status ? `Login failed (${status}). Please try again.` : 'Unable to reach the server. Please try again.');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-7 shadow-lg md:p-8">
            <header class="mb-6 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Welcome back</h1>
                <p class="mt-2 text-sm text-gray-600">Log in to continue your vehicle services.</p>
            </header>

            <form class="space-y-4" @submit="handleSubmit" novalidate>
                <AppInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    label="Email"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                />

                <AppInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />

                <p v-if="errorMessage" role="alert" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ errorMessage }}
                </p>

                <AppButton type="submit" :loading="loading" class="w-full">
                    {{ loading ? 'Logging in...' : 'Log in' }}
                </AppButton>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Need an account?
                <Link href="/register" class="font-semibold text-blue-700 hover:text-blue-800">Create one</Link>
            </p>
        </div>
    </section>
</template>
