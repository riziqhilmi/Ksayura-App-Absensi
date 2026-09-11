<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center justify-end gap-2">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-300"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="rounded-lg border px-3 py-2 text-sm font-semibold transition"
                :class="link.active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
