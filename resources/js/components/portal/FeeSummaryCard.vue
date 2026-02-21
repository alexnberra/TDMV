<script setup lang="ts">
import { computed } from 'vue';
import { formatCurrency } from '@/lib/ui';

interface Props {
    fees: Record<string, number>;
}

const props = defineProps<Props>();

const total = computed(() => {
    return Object.values(props.fees).reduce((sum, amount) => sum + amount, 0);
});

function formatLabel(key: string): string {
    return key
        .split('_')
        .map((part) => `${part.charAt(0).toUpperCase()}${part.slice(1)}`)
        .join(' ');
}
</script>

<template>
    <section class="rounded-2xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-blue-900">Fee Breakdown</h2>

        <div class="space-y-2">
            <div v-for="(value, key) in fees" :key="key" class="flex items-center justify-between text-sm text-gray-700">
                <span>{{ formatLabel(key) }}</span>
                <span class="font-medium text-gray-900">{{ formatCurrency(value) }}</span>
            </div>
        </div>

        <div class="mt-4 border-t border-blue-200 pt-4">
            <div class="flex items-center justify-between">
                <span class="text-base font-semibold text-gray-900">Total</span>
                <span class="text-lg font-bold text-blue-800">{{ formatCurrency(total) }}</span>
            </div>
        </div>

        <slot name="actions" />
    </section>
</template>
