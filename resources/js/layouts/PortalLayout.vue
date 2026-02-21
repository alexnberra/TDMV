<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useApiAuth } from '@/composables/useApiAuth';

const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);
const { user, ensureUser, logout, isLoading } = useApiAuth();
const tdmv = computed(() => (page.props as Record<string, { is_demo?: boolean }>).tdmv ?? {});

const navItems = computed(() => {
    const items = [
        { path: '/portal', label: 'Home', icon: '🏠' },
        { path: '/portal/service-selector', label: 'Services', icon: '🧭' },
        { path: '/portal/phase-2a', label: 'Fleet & Compliance', icon: '🚛' },
        { path: '/portal/phase-2b', label: 'Households & Appointments', icon: '👨‍👩‍👧‍👦' },
        { path: '/portal/phase-3', label: 'AI Assistant & Automation', icon: '🤖' },
        { path: '/portal/requirements', label: 'Checklist', icon: '✅' },
        { path: '/portal/upload', label: 'Uploads', icon: '📎' },
        { path: '/portal/vehicle/2', label: 'Vehicle 2', icon: '🚗' },
        { path: '/portal/notifications', label: 'Alerts', icon: '🔔' },
        { path: '/portal/support', label: 'Help', icon: '❓' },
    ];

    if (user.value?.role === 'admin' || user.value?.role === 'staff') {
        items.push({ path: '/portal/admin', label: 'Admin', icon: '🛠️' });
    }

    return items;
});

const mobileItems = computed(() => {
    return [
        { path: '/portal', label: 'Home', icon: '🏠' },
        { path: '/portal/service-selector', label: 'Services', icon: '🧭' },
        { path: '/portal/notifications', label: 'Alerts', icon: '🔔' },
        { path: '/portal/support', label: 'Help', icon: '❓' },
    ];
});

function isActive(path: string): boolean {
    if (path === '/portal') {
        return currentPath.value === '/portal';
    }

    return currentPath.value.startsWith(path);
}

async function handleLogout() {
    await logout();
    window.location.href = '/login';
}

onMounted(async () => {
    const authUser = await ensureUser();

    if (!authUser) {
        window.location.href = '/login';
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-20 md:pb-0">
        <header class="sticky top-0 z-[1010] border-b border-gray-200 bg-white/95 backdrop-blur md:hidden">
            <div class="flex items-center justify-between px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Tribal Vehicle Services</p>
                    <p class="text-xs text-gray-500">Member Portal</p>
                </div>
                <span
                    v-if="tdmv.is_demo"
                    class="rounded-full border border-amber-300 bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800"
                >
                    Demo
                </span>
            </div>
        </header>

        <aside class="fixed bottom-0 left-0 top-0 hidden w-72 border-r border-gray-200 bg-white md:block">
            <div class="flex h-full flex-col p-5">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-lg font-bold text-white">
                        T
                    </div>
                    <div>
                        <p class="text-base font-semibold text-gray-900">Tribal Vehicle Services</p>
                        <p class="text-xs text-gray-500">Member Portal</p>
                    </div>
                </div>

                <nav class="space-y-1" aria-label="Portal">
                    <Link
                        v-for="item in navItems"
                        :key="item.path"
                        :href="item.path"
                        class="focus-ring flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="isActive(item.path) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'"
                    >
                        <span aria-hidden="true">{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="mt-auto border-t border-gray-200 pt-4">
                    <p class="text-sm font-semibold text-gray-900">{{ user?.first_name }} {{ user?.last_name }}</p>
                    <p class="text-xs text-gray-500">{{ user?.email }}</p>
                    <button
                        type="button"
                        class="focus-ring mt-3 w-full rounded-lg border border-gray-300 px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-100"
                        @click="handleLogout"
                    >
                        Log Out
                    </button>
                </div>
            </div>
        </aside>

        <main class="mx-auto max-w-7xl md:ml-72 md:px-2">
            <div v-if="isLoading && !user" class="p-6 text-sm text-gray-600">Loading account…</div>
            <slot v-else />
        </main>

        <nav class="fixed bottom-0 left-0 right-0 z-[1020] border-t border-gray-200 bg-white md:hidden" aria-label="Bottom navigation">
            <div class="grid grid-cols-4 gap-1 px-2 py-1.5">
                <Link
                    v-for="item in mobileItems"
                    :key="item.path"
                    :href="item.path"
                    class="focus-ring touch-target flex flex-col items-center justify-center rounded-md px-1 py-1 text-[11px] font-medium"
                    :class="isActive(item.path) ? 'text-blue-700' : 'text-gray-500'"
                >
                    <span aria-hidden="true" class="text-sm">{{ item.icon }}</span>
                    <span class="mt-0.5">{{ item.label }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
