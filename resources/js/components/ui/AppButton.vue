<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/ui';

interface Props {
    variant?: 'primary' | 'secondary' | 'ghost' | 'destructive';
    size?: 'sm' | 'md' | 'lg';
    loading?: boolean;
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    loading: false,
    type: 'button',
    disabled: false,
});

const classes = computed(() => {
    const base = 'focus-ring touch-target inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition disabled:cursor-not-allowed disabled:opacity-60';

    const variants: Record<string, string> = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700',
        secondary: 'border-2 border-blue-600 text-blue-700 hover:bg-blue-50',
        ghost: 'text-gray-700 hover:bg-gray-100',
        destructive: 'bg-red-600 text-white hover:bg-red-700',
    };

    const sizes: Record<string, string> = {
        sm: 'px-3 py-2 text-sm',
        md: 'px-4 py-2.5 text-sm md:text-base',
        lg: 'px-6 py-3 text-base',
    };

    return cn(base, variants[props.variant], sizes[props.size]);
});
</script>

<template>
    <button :type="type" :disabled="disabled || loading" :class="classes">
        <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white" />
        <slot />
    </button>
</template>
