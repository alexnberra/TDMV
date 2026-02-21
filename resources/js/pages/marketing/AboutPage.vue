<script setup lang="ts">
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });
</script>

<template>
    <section class="py-12 md:py-16">
        <div class="mx-auto max-w-4xl px-4 md:px-6">
            <header class="mb-10">
                <h1 class="text-4xl font-bold text-gray-900 md:text-5xl">About Tribal Vehicle Services</h1>
                <p class="mt-4 text-base text-gray-600 md:text-lg">
                    A digital-first platform created to modernize DMV workflows while honoring tribal sovereignty and community needs.
                </p>
            </header>

            <div class="space-y-6 text-base leading-relaxed text-gray-700">
                <article class="surface-card p-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Our mission</h2>
                    <p class="mt-3">Deliver reliable, secure, and accessible vehicle services for tribal members with transparent workflows and faster outcomes.</p>
                </article>

                <article class="surface-card p-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Community-centered design</h2>
                    <p class="mt-3">Every screen is built mobile-first, supports guided completion, and reduces friction for members and staff.</p>
                </article>

                <article class="surface-card p-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Security and trust</h2>
                    <p class="mt-3">Sensitive data is handled with encryption, role-based access, and auditable workflows aligned with government-grade expectations.</p>
                </article>
            </div>
        </div>
    </section>
</template>
