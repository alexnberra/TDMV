<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });
</script>

<template>
    <div class="mx-auto max-w-3xl p-8 text-center">
        <h1 class="mb-4 text-3xl font-bold text-gray-900">Welcome to TDMV</h1>
        <p class="mb-6 text-gray-600">Use the links below to explore the platform.</p>
        <div class="flex justify-center gap-4">
            <Link href="/" class="rounded-md bg-blue-600 px-4 py-2 text-white">Marketing Site</Link>
            <Link href="/portal" class="rounded-md border border-gray-300 px-4 py-2">Portal</Link>
        </div>
    </div>
</template>
