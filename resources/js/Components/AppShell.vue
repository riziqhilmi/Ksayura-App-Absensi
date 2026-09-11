<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Toast from './Toast.vue';

const page = usePage();
const sidebarOpen = ref(false);
const userMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user);
const navigation = computed(() => page.props.navigation ?? { items: [] });

const initials = computed(() => user.value?.avatar_initial || user.value?.name?.charAt(0) || 'U');

const logout = () => {
    router.post(navigation.value.logout_url, {}, {
        preserveScroll: false,
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <Toast />

        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
            @click="sidebarOpen = false"
        />

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 flex w-72 transform flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 items-center gap-3 border-b border-slate-100 px-5">
                <img src="/images/logo.png" alt="Kantor Sayur" class="h-10 w-10 rounded-xl object-contain shadow-sm">
                <div>
                    <p class="text-sm font-bold leading-4 text-slate-900">Kantor Sayur</p>
                    <p class="text-xs font-medium text-slate-500">Workforce App</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <component
                    :is="item.inertia ? Link : 'a'"
                    v-for="item in navigation.items"
                    :key="item.href"
                    :href="item.href"
                    class="group flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-semibold transition"
                    :class="item.active ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'"
                    @click="sidebarOpen = false"
                >
                    <span>{{ item.label }}</span>
                    <span
                        v-if="item.badge"
                        class="ml-3 inline-flex min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 py-0.5 text-xs font-bold text-white"
                    >
                        {{ item.badge }}
                    </span>
                </component>
            </nav>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <span class="sr-only">Buka menu</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Workspace</p>
                            <p class="text-sm font-bold text-slate-900">{{ user?.role === 'owner' ? 'Owner' : 'Karyawan' }}</p>
                        </div>
                    </div>

                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-3 rounded-lg px-2 py-1.5 hover:bg-slate-100"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">
                                {{ initials }}
                            </span>
                            <span class="hidden text-left sm:block">
                                <span class="block text-sm font-semibold text-slate-800">{{ user?.name }}</span>
                                <span class="block text-xs text-slate-500">{{ user?.email }}</span>
                            </span>
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 mt-2 w-52 rounded-lg border border-slate-200 bg-white p-1 shadow-lg"
                        >
                            <a :href="navigation.profile_url" class="block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100">Profil</a>
                            <a
                                v-if="navigation.settings_url"
                                :href="navigation.settings_url"
                                class="block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100"
                            >
                                Pengaturan
                            </a>
                            <button
                                type="button"
                                class="block w-full rounded-md px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                @click="logout"
                            >
                                Keluar
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:pb-6">
                <slot />
            </main>
        </div>

        <nav v-if="user?.role === 'employee'" class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-2 py-2 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur lg:hidden">
            <div class="grid grid-cols-5 gap-1">
                <component
                    :is="item.inertia ? Link : 'a'"
                    v-for="item in navigation.items"
                    :key="item.href"
                    :href="item.href"
                    class="flex min-w-0 flex-col items-center rounded-lg px-1 py-2 text-[11px] font-semibold transition"
                    :class="item.active ? 'bg-emerald-50 text-emerald-700' : 'text-slate-500 hover:bg-slate-50'"
                >
                    <span class="mb-1 h-1.5 w-1.5 rounded-full" :class="item.active ? 'bg-emerald-600' : 'bg-slate-300'" />
                    <span class="w-full truncate text-center">{{ item.label.replace(' Saya', '').replace(' Kerja', '') }}</span>
                </component>
            </div>
        </nav>
    </div>
</template>
