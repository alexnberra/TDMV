<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { referenceApi } from '@/lib/api';
import PortalLayout from '@/layouts/PortalLayout.vue';

defineOptions({ layout: PortalLayout });

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
    phone: string;
    email: string | null;
    current_wait_time: number | null;
}

interface FaqItem {
    id: number;
    question: string;
    answer: string;
}

type FaqGroups = Record<string, FaqItem[]>;

const locations = ref<OfficeLocation[]>([]);
const faqs = ref<FaqGroups>({});
const loading = ref(true);
const errorMessage = ref('');
const search = ref('');

const filteredFaqs = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return faqs.value;
    }

    const result: FaqGroups = {};

    for (const [category, items] of Object.entries(faqs.value)) {
        const matches = items.filter((faq) => {
            return faq.question.toLowerCase().includes(query) || faq.answer.toLowerCase().includes(query);
        });

        if (matches.length) {
            result[category] = matches;
        }
    }

    return result;
});

onMounted(async () => {
    try {
        const [locationResponse, faqResponse] = await Promise.all([referenceApi.officeLocations(), referenceApi.faqs()]);

        locations.value = locationResponse.data.locations ?? [];
        faqs.value = faqResponse.data.faqs ?? {};
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load support resources.';
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header>
                <h1 class="text-3xl font-bold text-gray-900">Help & Support</h1>
                <p class="mt-2 text-sm text-gray-600">Search FAQs, view office details, and find contact options.</p>
            </header>

            <label class="block">
                <span class="mb-1.5 block text-sm font-semibold text-gray-700">Search help topics</span>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search for registration, documents, payments..."
                    class="focus-ring h-12 w-full rounded-xl border border-gray-300 px-4 text-base"
                />
            </label>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading support resources…</p>
            <p v-else-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ errorMessage }}</p>

            <template v-else>
                <div class="grid gap-5 lg:grid-cols-3">
                    <section class="surface-card p-5 lg:col-span-2">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Frequently asked questions</h2>

                        <p v-if="Object.keys(filteredFaqs).length === 0" class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                            No FAQ results for this search.
                        </p>

                        <div v-else class="space-y-4">
                            <article v-for="(items, category) in filteredFaqs" :key="category" class="rounded-xl border border-gray-200 bg-white p-4">
                                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">{{ category }}</h3>
                                <div class="space-y-2">
                                    <details v-for="faq in items" :key="faq.id" class="group rounded-lg border border-gray-200 p-3">
                                        <summary class="cursor-pointer list-none pr-5 text-sm font-semibold text-gray-900">{{ faq.question }}</summary>
                                        <p class="mt-2 text-sm text-gray-700">{{ faq.answer }}</p>
                                    </details>
                                </div>
                            </article>
                        </div>
                    </section>

                    <aside class="space-y-4">
                        <article class="surface-card p-5">
                            <h2 class="text-lg font-semibold text-gray-900">Contact options</h2>
                            <ul class="mt-3 space-y-2 text-sm text-gray-700">
                                <li><strong>Email:</strong> support@tribalservices.gov</li>
                                <li><strong>Phone:</strong> (555) 123-4567</li>
                                <li><strong>Hours:</strong> Mon-Fri 8:00 AM - 5:00 PM</li>
                            </ul>
                        </article>

                        <article class="surface-card p-5">
                            <h2 class="text-lg font-semibold text-gray-900">Office locations</h2>
                            <p v-if="locations.length === 0" class="mt-2 text-sm text-gray-600">No active offices currently listed.</p>
                            <div v-else class="mt-3 space-y-3">
                                <div v-for="location in locations" :key="location.id" class="rounded-lg border border-gray-200 p-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ location.name }}</p>
                                    <p class="mt-1 text-xs text-gray-600">{{ location.address }}</p>
                                    <p class="mt-2 text-xs text-gray-700">{{ location.phone }}</p>
                                    <p v-if="location.email" class="text-xs text-gray-700">{{ location.email }}</p>
                                    <p v-if="location.current_wait_time !== null" class="mt-1 text-xs text-gray-500">
                                        Estimated wait {{ location.current_wait_time }} minutes
                                    </p>
                                </div>
                            </div>
                        </article>
                    </aside>
                </div>
            </template>
        </div>
    </div>
</template>
