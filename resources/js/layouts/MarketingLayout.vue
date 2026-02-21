<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const mobileMenuOpen = ref(false);
const page = usePage();
const currentPath = computed(() => page.url.split('?')[0]);
const tdmv = computed(() => (page.props as Record<string, { is_demo?: boolean }>).tdmv ?? {});

const navItems = [
    { path: '/features', label: 'Features' },
    { path: '/pricing', label: 'Pricing' },
    { path: '/about', label: 'About' },
    { path: '/contact', label: 'Contact' },
];

function closeMenu() {
    mobileMenuOpen.value = false;
}

function isActive(path: string): boolean {
    return currentPath.value === path;
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <a href="#main-content" class="focus-ring sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-3 focus:z-[1070] focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:text-sm">
            Skip to content
        </a>

        <header class="fixed left-0 right-0 top-0 z-[1020] border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 md:px-6">
                <Link href="/" class="focus-ring flex items-center gap-3 rounded-md p-1">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 text-lg font-bold text-white">
                        T
                    </div>
                    <div class="leading-tight">
                        <p class="text-base font-bold text-gray-900">Tribal Vehicle Services</p>
                        <p class="text-xs text-gray-500">Digital member portal</p>
                    </div>
                    <span
                        v-if="tdmv.is_demo"
                        class="rounded-full border border-amber-300 bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800"
                    >
                        Demo
                    </span>
                </Link>

                <nav class="hidden items-center gap-6 md:flex" aria-label="Primary">
                    <Link
                        v-for="item in navItems"
                        :key="item.path"
                        :href="item.path"
                        class="focus-ring rounded-md px-2 py-1 text-sm font-medium transition"
                        :class="isActive(item.path) ? 'text-blue-700' : 'text-gray-600 hover:text-gray-900'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    <Link href="/login" class="focus-ring rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Log In</Link>
                    <Link href="/register" class="focus-ring rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Get Started</Link>
                </div>

                <button
                    type="button"
                    aria-label="Toggle menu"
                    class="focus-ring touch-target rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    {{ mobileMenuOpen ? 'Close' : 'Menu' }}
                </button>
            </div>

            <div v-if="mobileMenuOpen" class="border-t border-gray-200 bg-white md:hidden">
                <div class="mx-auto max-w-7xl space-y-2 px-4 py-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.path"
                        :href="item.path"
                        class="focus-ring block rounded-md px-3 py-2 text-sm font-medium"
                        :class="isActive(item.path) ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </Link>
                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <Link href="/login" class="focus-ring rounded-lg border border-gray-300 px-3 py-2 text-center text-sm font-semibold text-gray-700" @click="closeMenu">
                            Log In
                        </Link>
                        <Link href="/register" class="focus-ring rounded-lg bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white" @click="closeMenu">
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <main id="main-content" class="pt-16">
            <slot />
        </main>

        <footer class="mt-20 bg-gray-900 text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <div>
                        <div class="mb-3 flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 font-bold">T</div>
                            <p class="font-semibold">Tribal Vehicle Services</p>
                        </div>
                        <p class="text-sm text-gray-400">Secure and modern DMV services for tribal communities.</p>
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-300">Product</p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link href="/features" class="hover:text-white">Features</Link></li>
                            <li><Link href="/pricing" class="hover:text-white">Pricing</Link></li>
                        </ul>
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-300">Company</p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link href="/about" class="hover:text-white">About</Link></li>
                            <li><Link href="/contact" class="hover:text-white">Contact</Link></li>
                        </ul>
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-300">Portal</p>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><Link href="/login" class="hover:text-white">Member Login</Link></li>
                            <li><Link href="/register" class="hover:text-white">Create Account</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 border-t border-gray-800 pt-6 text-center text-xs text-gray-400">
                    © 2026 Tribal Vehicle Services. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
