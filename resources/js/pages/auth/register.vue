<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });
</script>

<template>
    <div class="mx-auto max-w-lg p-8">
        <h1 class="mb-2 text-2xl font-bold">Register</h1>
        <p class="text-gray-600">Use the custom registration flow for tribal vehicle services.</p>
        <Link href="/register" class="mt-4 inline-block text-blue-600 underline">Go to Register</Link>
    </div>
</template>
