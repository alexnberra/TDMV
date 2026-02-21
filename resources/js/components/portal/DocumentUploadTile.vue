<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/ui';
import StatusChip from './StatusChip.vue';

interface Props {
    name: string;
    type: string;
    status: string;
}

const props = defineProps<Props>();

const mappedStatus = computed(() => {
    if (props.status === 'accepted') return 'approved';
    if (props.status === 'rejected') return 'rejected';
    if (props.status === 'processing') return 'under_review';
    return 'pending';
});
</script>

<template>
    <article class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ name }}</p>
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ type.replace('_', ' ') }}</p>
            </div>
            <StatusChip :status="mappedStatus" />
        </div>
        <div class="mt-3 flex items-center gap-2">
            <slot name="actions" />
        </div>
    </article>
</template>
