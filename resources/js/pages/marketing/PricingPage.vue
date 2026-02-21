<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });

const lineItems = [
    { label: 'Vehicle Registration', amount: '$45.00' },
    { label: 'Plate Fee', amount: '$15.00' },
    { label: 'Processing Fee', amount: '$5.00' },
];

const included = [
    'Secure payment processing',
    'Case number and status tracking',
    'Document storage in portal',
    'Email payment confirmation',
];
</script>

<template>
    <section class="py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl font-bold text-gray-900 md:text-5xl">Simple, transparent pricing</h1>
                <p class="mt-4 text-base text-gray-600 md:text-lg">No subscriptions, no hidden charges, clear fee line items.</p>
            </div>

            <div class="mx-auto mt-10 max-w-2xl">
                <article class="rounded-3xl border-2 border-blue-600 bg-white p-7 shadow-lg md:p-9">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">Pay-as-you-go</p>
                            <h2 class="mt-1 text-2xl font-bold text-gray-900">Typical renewal</h2>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">Most common</span>
                    </div>

                    <div class="space-y-3">
                        <div v-for="item in lineItems" :key="item.label" class="flex items-center justify-between border-b border-gray-200 pb-3 text-sm text-gray-700">
                            <span>{{ item.label }}</span>
                            <span class="font-semibold text-gray-900">{{ item.amount }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 text-lg font-bold text-gray-900">
                            <span>Total</span>
                            <span class="text-blue-700">$65.00</span>
                        </div>
                    </div>

                    <ul class="mt-6 space-y-2 text-sm text-gray-700">
                        <li v-for="item in included" :key="item" class="flex items-start gap-2">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-[11px] font-bold text-emerald-700">✓</span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>

                    <Link href="/register" class="focus-ring mt-7 inline-flex w-full touch-target items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Create account and start
                    </Link>
                </article>
            </div>
        </div>
    </section>
</template>
