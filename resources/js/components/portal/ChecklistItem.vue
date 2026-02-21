<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/ui';

interface Props {
    title: string;
    description: string;
    helperText?: string;
    status: 'complete' | 'incomplete' | 'needs-review';
}

const props = defineProps<Props>();

const cardClass = computed(() => {
    if (props.status === 'complete') {
        return 'border-emerald-200 bg-emerald-50';
    }

    if (props.status === 'needs-review') {
        return 'border-amber-200 bg-amber-50';
    }

    return 'border-gray-200 bg-white';
});

const icon = computed(() => {
    if (props.status === 'complete') {
        return '✓';
    }

    if (props.status === 'needs-review') {
        return '!';
    }

    return '○';
});

const iconClass = computed(() => {
    if (props.status === 'complete') {
        return 'bg-emerald-600 text-white';
    }

    if (props.status === 'needs-review') {
        return 'bg-amber-500 text-white';
    }

    return 'border border-gray-300 bg-white text-gray-500';
});
</script>

<template>
    <article :class="cn('rounded-xl border p-4', cardClass)">
        <div class="flex gap-3">
            <div :class="cn('flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold', iconClass)">
                {{ icon }}
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900 md:text-base">{{ title }}</h3>
                <p class="mt-1 text-sm text-gray-700">{{ description }}</p>
                <p v-if="helperText" class="mt-3 rounded-lg bg-white/80 p-2.5 text-xs text-gray-700">{{ helperText }}</p>
            </div>
            <slot name="action" />
        </div>
    </article>
</template>
