<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { notificationApi } from '@/lib/api';
import PortalLayout from '@/layouts/PortalLayout.vue';
import AppButton from '@/components/ui/AppButton.vue';

defineOptions({ layout: PortalLayout });

interface NotificationItem {
    id: string;
    type: string;
    data: Record<string, string>;
    read_at: string | null;
    created_at: string;
}

interface NotificationPreferences {
    expiration_reminders: boolean;
    status_updates: boolean;
    document_requests: boolean;
    payment_confirmations: boolean;
    office_announcements: boolean;
    email_enabled: boolean;
    sms_enabled: boolean;
    push_enabled: boolean;
}

const notifications = ref<NotificationItem[]>([]);
const preferences = ref<NotificationPreferences | null>(null);
const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const filter = ref<'all' | 'unread'>('all');

const filteredNotifications = computed(() => {
    if (filter.value === 'unread') {
        return notifications.value.filter((item) => !item.read_at);
    }

    return notifications.value;
});

async function loadNotifications() {
    try {
        const [notificationResponse, preferenceResponse] = await Promise.all([
            notificationApi.all(),
            notificationApi.preferences(),
        ]);

        notifications.value = notificationResponse.data.data as NotificationItem[];
        preferences.value = preferenceResponse.data.preferences as NotificationPreferences;
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to load notifications.';
    } finally {
        loading.value = false;
    }
}

async function markRead(id: string) {
    await notificationApi.markRead(id);
    await loadNotifications();
}

async function markAllRead() {
    await notificationApi.markAllRead();
    await loadNotifications();
}

async function savePreferences() {
    if (!preferences.value) {
        return;
    }

    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        await notificationApi.updatePreferences(preferences.value);
        successMessage.value = 'Notification preferences saved.';
    } catch (error: unknown) {
        const message = (error as { response?: { data?: { message?: string } } }).response?.data?.message;
        errorMessage.value = message ?? 'Unable to save preferences.';
    } finally {
        saving.value = false;
    }
}

onMounted(loadNotifications);
</script>

<template>
    <div class="px-4 py-6 md:px-8 md:py-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
                    <p class="mt-2 text-sm text-gray-600">Track alerts and tune communication preferences.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <AppButton variant="secondary" @click="loadNotifications">Refresh</AppButton>
                    <AppButton variant="ghost" @click="markAllRead">Mark all read</AppButton>
                </div>
            </header>

            <p v-if="loading" class="surface-card p-4 text-sm text-gray-600">Loading notifications…</p>

            <template v-else>
                <div class="grid gap-5 lg:grid-cols-3">
                    <section class="surface-card p-5 lg:col-span-2">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="focus-ring rounded-lg px-3 py-2 text-sm font-semibold"
                                :class="filter === 'all' ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
                                @click="filter = 'all'"
                            >
                                All
                            </button>
                            <button
                                type="button"
                                class="focus-ring rounded-lg px-3 py-2 text-sm font-semibold"
                                :class="filter === 'unread' ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
                                @click="filter = 'unread'"
                            >
                                Unread
                            </button>
                        </div>

                        <p v-if="filteredNotifications.length === 0" class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
                            No notifications in this filter.
                        </p>

                        <div v-else class="space-y-3">
                            <article
                                v-for="item in filteredNotifications"
                                :key="item.id"
                                class="rounded-xl border p-4"
                                :class="item.read_at ? 'border-gray-200 bg-white' : 'border-blue-200 bg-blue-50'"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ item.data.title ?? item.type }}</p>
                                        <p class="mt-1 text-sm text-gray-700">{{ item.data.message ?? 'No message provided.' }}</p>
                                        <p class="mt-2 text-xs text-gray-500">{{ item.created_at }}</p>
                                    </div>
                                    <button
                                        v-if="!item.read_at"
                                        type="button"
                                        class="focus-ring rounded-md border border-blue-300 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                        @click="markRead(item.id)"
                                    >
                                        Mark read
                                    </button>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="surface-card p-5">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Preferences</h2>
                        <div v-if="preferences" class="space-y-3 text-sm">
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                <span class="text-gray-700">Expiration reminders</span>
                                <input v-model="preferences.expiration_reminders" type="checkbox" class="h-4 w-4 accent-blue-600" />
                            </label>
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                <span class="text-gray-700">Status updates</span>
                                <input v-model="preferences.status_updates" type="checkbox" class="h-4 w-4 accent-blue-600" />
                            </label>
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                <span class="text-gray-700">Document requests</span>
                                <input v-model="preferences.document_requests" type="checkbox" class="h-4 w-4 accent-blue-600" />
                            </label>
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                <span class="text-gray-700">Payment confirmations</span>
                                <input v-model="preferences.payment_confirmations" type="checkbox" class="h-4 w-4 accent-blue-600" />
                            </label>
                            <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                <span class="text-gray-700">Office announcements</span>
                                <input v-model="preferences.office_announcements" type="checkbox" class="h-4 w-4 accent-blue-600" />
                            </label>

                            <div class="border-t border-gray-200 pt-2">
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Channels</p>
                                <label class="mb-2 flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                    <span class="text-gray-700">Email</span>
                                    <input v-model="preferences.email_enabled" type="checkbox" class="h-4 w-4 accent-blue-600" />
                                </label>
                                <label class="mb-2 flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                    <span class="text-gray-700">SMS</span>
                                    <input v-model="preferences.sms_enabled" type="checkbox" class="h-4 w-4 accent-blue-600" />
                                </label>
                                <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2">
                                    <span class="text-gray-700">Push</span>
                                    <input v-model="preferences.push_enabled" type="checkbox" class="h-4 w-4 accent-blue-600" />
                                </label>
                            </div>

                            <AppButton :loading="saving" class="mt-2 w-full" @click="savePreferences">
                                {{ saving ? 'Saving...' : 'Save Preferences' }}
                            </AppButton>
                        </div>
                    </section>
                </div>

                <p v-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ errorMessage }}</p>
                <p v-if="successMessage" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ successMessage }}</p>
            </template>
        </div>
    </div>
</template>
