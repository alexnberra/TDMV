<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/ui';

type Status =
    | 'active'
    | 'expiring-soon'
    | 'expired'
    | 'pending'
    | 'submitted'
    | 'under_review'
    | 'info_requested'
    | 'approved'
    | 'rejected'
    | 'completed'
    | 'cancelled';

interface Props {
    status: Status | string;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'sm',
});

const variants: Record<string, { label: string; className: string }> = {
    active: { label: 'Active', className: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
    'expiring-soon': { label: 'Expiring Soon', className: 'border-amber-200 bg-amber-50 text-amber-700' },
    expired: { label: 'Expired', className: 'border-red-200 bg-red-50 text-red-700' },
    pending: { label: 'Pending', className: 'border-slate-200 bg-slate-100 text-slate-700' },
    submitted: { label: 'Submitted', className: 'border-blue-200 bg-blue-50 text-blue-700' },
    under_review: { label: 'Under Review', className: 'border-indigo-200 bg-indigo-50 text-indigo-700' },
    info_requested: { label: 'Info Requested', className: 'border-amber-200 bg-amber-50 text-amber-700' },
    approved: { label: 'Approved', className: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
    rejected: { label: 'Rejected', className: 'border-red-200 bg-red-50 text-red-700' },
    completed: { label: 'Completed', className: 'border-emerald-200 bg-emerald-50 text-emerald-700' },
    cancelled: { label: 'Cancelled', className: 'border-gray-200 bg-gray-100 text-gray-700' },
};

const config = computed(() => {
    return variants[props.status] ?? {
        label: props.status.replace('_', ' '),
        className: 'border-gray-200 bg-gray-100 text-gray-700',
    };
});

const sizeClasses = computed(() => {
    return props.size === 'md' ? 'px-3 py-1.5 text-xs md:text-sm' : 'px-2.5 py-1 text-[11px]';
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center rounded-full border font-semibold uppercase tracking-wide',
                sizeClasses,
                config.className,
            )
        "
    >
        {{ config.label }}
    </span>
</template>
