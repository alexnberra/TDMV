<script setup lang="ts">
interface Props {
    title: string;
    description: string;
    icon: string;
    iconClass?: string;
}

withDefaults(defineProps<Props>(), {
    iconClass: 'bg-blue-100 text-blue-700',
});
</script>

<template>
    <article class="surface-card touch-target p-4 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
        <div class="flex items-start gap-3">
            <div :class="`flex h-11 w-11 items-center justify-center rounded-xl text-xl ${iconClass}`">
                <span aria-hidden="true">{{ icon }}</span>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900">{{ title }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ description }}</p>
            </div>
        </div>
    </article>
</template>
