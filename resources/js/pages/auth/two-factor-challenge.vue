<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });
</script>

<template>
    <div class="mx-auto max-w-lg p-8">
        <h1 class="mb-2 text-2xl font-bold">Two-Factor Challenge</h1>
        <p class="text-gray-600">Enter your two-factor authentication code.</p>
    </div>
</template>
