<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });

const features = [
    {
        title: 'Vehicle Management',
        description: 'Track each vehicle registration, renewal, and expiration from one member dashboard.',
        bullets: ['Multiple vehicles', 'Expiration reminders', 'Registration status history'],
    },
    {
        title: 'Document Workflow',
        description: 'Upload required records with clear acceptance and review states.',
        bullets: ['Mobile upload', 'Status visibility', 'Secure storage'],
    },
    {
        title: 'Application Timeline',
        description: 'Know exactly where your request sits and what action is required next.',
        bullets: ['Case timeline', 'Staff updates', 'Action-needed alerts'],
    },
    {
        title: 'Payments & Fees',
        description: 'Transparent fee breakdowns and safe online payment processing.',
        bullets: ['Itemized totals', 'Card/ACH support', 'Payment confirmation'],
    },
    {
        title: 'Notifications',
        description: 'Configure reminders and service updates across email, SMS, and portal alerts.',
        bullets: ['Granular preferences', 'Unread tracking', 'Read-all controls'],
    },
    {
        title: 'Support Center',
        description: 'Find office hours, FAQs, and service guidance quickly.',
        bullets: ['Location cards', 'FAQ categories', 'Contact options'],
    },
];
</script>

<template>
    <section class="py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl font-bold text-gray-900 md:text-5xl">Platform features built for real DMV workflows</h1>
                <p class="mt-4 text-base text-gray-600 md:text-lg">
                    Member-facing clarity with staff-ready operations and secure data handling.
                </p>
            </div>

            <div class="mt-10 grid gap-4 md:mt-12 md:grid-cols-2 lg:grid-cols-3 md:gap-6">
                <article v-for="feature in features" :key="feature.title" class="surface-card p-6">
                    <h2 class="text-lg font-semibold text-gray-900">{{ feature.title }}</h2>
                    <p class="mt-2 text-sm text-gray-600 md:text-base">{{ feature.description }}</p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-700">
                        <li v-for="item in feature.bullets" :key="item" class="flex items-center gap-2">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-blue-600" />
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </section>
</template>
