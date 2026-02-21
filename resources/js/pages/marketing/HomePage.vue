<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });

const features = [
    {
        title: 'Simple Registration',
        description: 'Register, renew, and track each vehicle from one guided experience.',
    },
    {
        title: 'Real-Time Status',
        description: 'See each step in your application timeline with clear next actions.',
    },
    {
        title: 'Secure Documents',
        description: 'Upload records safely with encrypted storage and staff-only access.',
    },
];

const trustItems = ['Tribal member-first design', 'Secure payments', 'Mobile document upload'];
const benefits = [
    'Guided service intake that prevents missed requirements',
    'Status updates and reminders before deadlines',
    'One portal for vehicles, documents, and payments',
    'Fast support with office locations and FAQs',
];
</script>

<template>
    <div>
        <section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white">
            <div class="mx-auto max-w-7xl px-4 py-16 md:px-6 md:py-24">
                <div class="max-w-3xl">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-blue-100">Tribal DMV Platform</p>
                    <h1 class="text-balance text-4xl font-bold leading-tight md:text-6xl">Vehicle services that feel modern, secure, and easy to trust.</h1>
                    <p class="mt-5 max-w-2xl text-lg text-blue-100 md:text-xl">
                        Manage registration, title workflows, documents, and application status in one member portal.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link href="/register" class="focus-ring touch-target rounded-lg bg-white px-6 py-3 text-center text-sm font-semibold text-blue-700 hover:bg-blue-50">
                            Create Account
                        </Link>
                        <Link href="/features" class="focus-ring touch-target rounded-lg border border-white/70 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-white/10">
                            Explore Features
                        </Link>
                    </div>
                    <div class="mt-8 flex flex-wrap gap-2">
                        <span v-for="item in trustItems" :key="item" class="rounded-full border border-white/30 bg-white/10 px-3 py-1 text-xs font-medium text-blue-50">
                            {{ item }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <div class="mb-10 max-w-2xl">
                    <h2 class="text-3xl font-bold text-gray-900 md:text-4xl">Everything members need in one portal</h2>
                    <p class="mt-3 text-base text-gray-600 md:text-lg">Clean workflows, clear status, and fewer office trips.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-3 md:gap-6">
                    <article v-for="feature in features" :key="feature.title" class="surface-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ feature.title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 md:text-base">{{ feature.description }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="bg-gray-100 py-14 md:py-20">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 md:grid-cols-2 md:px-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 md:text-4xl">Built for tribal communities</h2>
                    <p class="mt-3 text-gray-600">A respectful, mobile-first experience that supports sovereign operations and modern service delivery.</p>
                    <ul class="mt-6 space-y-3">
                        <li v-for="benefit in benefits" :key="benefit" class="flex items-start gap-3 text-sm text-gray-700 md:text-base">
                            <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">✓</span>
                            <span>{{ benefit }}</span>
                        </li>
                    </ul>
                </div>

                <div class="surface-card overflow-hidden">
                    <div class="h-full bg-gradient-to-br from-blue-50 via-white to-amber-50 p-8">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Platform Snapshot</p>
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="rounded-xl border border-blue-100 bg-white p-4">
                                <p class="text-xs text-gray-500">Service Coverage</p>
                                <p class="mt-2 text-2xl font-bold text-gray-900">10+</p>
                            </div>
                            <div class="rounded-xl border border-blue-100 bg-white p-4">
                                <p class="text-xs text-gray-500">Mobile Ready</p>
                                <p class="mt-2 text-2xl font-bold text-gray-900">100%</p>
                            </div>
                            <div class="rounded-xl border border-blue-100 bg-white p-4">
                                <p class="text-xs text-gray-500">Status Tracking</p>
                                <p class="mt-2 text-2xl font-bold text-gray-900">Real Time</p>
                            </div>
                            <div class="rounded-xl border border-blue-100 bg-white p-4">
                                <p class="text-xs text-gray-500">Document Flows</p>
                                <p class="mt-2 text-2xl font-bold text-gray-900">Guided</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4 md:px-6">
                <div class="rounded-3xl bg-gradient-to-r from-blue-700 to-blue-800 px-6 py-10 text-center text-white md:px-12 md:py-14">
                    <h2 class="text-3xl font-bold md:text-4xl">Ready to get started?</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-base text-blue-100 md:text-lg">
                        Create an account and start your first vehicle service in minutes.
                    </p>
                    <Link href="/register" class="focus-ring mt-7 inline-flex touch-target items-center justify-center rounded-lg bg-white px-6 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                        Start Now
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>
