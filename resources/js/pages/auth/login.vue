<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });
</script>

<template>
    <div class="mx-auto max-w-lg p-8">
        <h1 class="mb-2 text-2xl font-bold">Sign In</h1>
        <p class="text-gray-600">Use the portal login page.</p>
        <Link href="/login" class="mt-4 inline-block text-blue-600 underline">Go to Login</Link>
    </div>
</template>
