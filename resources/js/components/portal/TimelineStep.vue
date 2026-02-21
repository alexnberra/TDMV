<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/ui';

interface Props {
    title: string;
    description?: string;
    timestamp?: string;
    status: 'complete' | 'current' | 'upcoming';
    isLast?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    timestamp: '',
    isLast: false,
});

const dotClass = computed(() => {
    if (props.status === 'complete') {
        return 'border-emerald-500 bg-emerald-500 text-white';
    }

    if (props.status === 'current') {
        return 'border-blue-600 bg-blue-600 text-white';
    }

    return 'border-gray-300 bg-white text-gray-400';
});

const lineClass = computed(() => {
    return props.status === 'complete' ? 'bg-emerald-400' : 'bg-gray-300';
});

const dotLabel = computed(() => {
    if (props.status === 'complete') {
        return '✓';
    }

    if (props.status === 'current') {
        return '•';
    }

    return '';
});
</script>

<template>
    <div class="flex gap-4">
        <div class="flex flex-col items-center">
            <div :class="cn('flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-bold', dotClass)">
                {{ dotLabel }}
            </div>
            <div v-if="!isLast" :class="cn('mt-2 w-0.5 flex-1 min-h-8', lineClass)" />
        </div>

        <div :class="cn('flex-1', isLast ? 'pb-0' : 'pb-6')">
            <p class="text-sm font-semibold text-gray-900">{{ title }}</p>
            <p v-if="description" class="mt-1 text-sm text-gray-700">{{ description }}</p>
            <p v-if="timestamp" class="mt-1 text-xs text-gray-500">{{ timestamp }}</p>
        </div>
    </div>
</template>
