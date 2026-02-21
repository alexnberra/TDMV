<script setup lang="ts">
import { computed } from 'vue';
import StatusChip from './StatusChip.vue';
import { formatDate } from '@/lib/ui';

interface Props {
    id: number;
    year: number;
    make: string;
    model: string;
    plateNumber?: string | null;
    status: string;
    expirationDate?: string | null;
    daysUntilExpiration?: number | null;
}

const props = defineProps<Props>();

const expirationLabel = computed(() => {
    if (!props.expirationDate) {
        return 'Pending';
    }

    return formatDate(props.expirationDate);
});

const expirationHint = computed(() => {
    if (props.daysUntilExpiration === null || props.daysUntilExpiration === undefined) {
        return '';
    }

    if (props.daysUntilExpiration < 0) {
        return `Expired ${Math.abs(props.daysUntilExpiration)} day(s) ago`;
    }

    if (props.daysUntilExpiration <= 30) {
        return `${props.daysUntilExpiration} day(s) left`;
    }

    return '';
});

const semanticStatus = computed(() => {
    if (props.status === 'active' && props.daysUntilExpiration !== null && props.daysUntilExpiration !== undefined && props.daysUntilExpiration <= 30) {
        return 'expiring-soon';
    }

    return props.status;
});
</script>

<template>
    <article class="surface-card touch-target p-4 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ year }} {{ make }} {{ model }}</h3>
                <p class="mt-1 text-sm text-gray-500">Plate {{ plateNumber || 'Pending assignment' }}</p>
            </div>
            <StatusChip :status="semanticStatus" />
        </div>

        <div class="mt-4 flex items-end justify-between gap-2">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Expires</p>
                <p class="text-sm font-medium text-gray-900">{{ expirationLabel }}</p>
                <p v-if="expirationHint" class="mt-1 text-xs" :class="daysUntilExpiration && daysUntilExpiration <= 30 ? 'text-amber-700' : 'text-gray-500'">
                    {{ expirationHint }}
                </p>
            </div>
            <span class="text-sm font-medium text-blue-700">View</span>
        </div>
    </article>
</template>
