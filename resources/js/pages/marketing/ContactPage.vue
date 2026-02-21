<script setup lang="ts">
import { reactive, ref } from 'vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

defineOptions({ layout: MarketingLayout });

const form = reactive({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submitted = ref(false);

function submitForm(event: Event) {
    event.preventDefault();
    submitted.value = true;
}
</script>

<template>
    <section class="py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl font-bold text-gray-900 md:text-5xl">Contact support</h1>
                <p class="mt-4 text-base text-gray-600 md:text-lg">Questions about registrations, payments, or account access? We can help.</p>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <article class="surface-card p-6 md:p-8">
                    <h2 class="text-2xl font-semibold text-gray-900">Send a message</h2>

                    <form class="mt-6 space-y-4" @submit="submitForm">
                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-gray-700">Name</span>
                            <input v-model="form.name" required class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-base" placeholder="Your full name" />
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-gray-700">Email</span>
                            <input v-model="form.email" required type="email" class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-base" placeholder="you@example.com" />
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-gray-700">Subject</span>
                            <input v-model="form.subject" required class="focus-ring h-11 w-full rounded-lg border border-gray-300 px-3 text-base" placeholder="How can we help?" />
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-gray-700">Message</span>
                            <textarea v-model="form.message" required rows="5" class="focus-ring w-full rounded-lg border border-gray-300 px-3 py-2 text-base" placeholder="Share details about your question" />
                        </label>

                        <button type="submit" class="focus-ring touch-target rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                            Send Message
                        </button>

                        <p v-if="submitted" role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                            Message received. We will reply as soon as possible.
                        </p>
                    </form>
                </article>

                <div class="space-y-4">
                    <article class="surface-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Email</h3>
                        <p class="mt-2 text-sm text-gray-600">support@tribalservices.gov</p>
                    </article>
                    <article class="surface-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Phone</h3>
                        <p class="mt-2 text-sm text-gray-600">(555) 123-4567</p>
                        <p class="text-xs text-gray-500">Mon-Fri • 8:00 AM - 5:00 PM</p>
                    </article>
                    <article class="surface-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900">Office</h3>
                        <p class="mt-2 text-sm text-gray-600">123 Tribal Office Road<br />Headquarters, ST 12345</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>
