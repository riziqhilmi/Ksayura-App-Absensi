<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);

const message = computed(() => {
    const flash = page.props.flash ?? {};
    return flash.success || flash.error || flash.info || flash.status || '';
});

const tone = computed(() => {
    const flash = page.props.flash ?? {};
    if (flash.error) return 'border-red-200 bg-red-50 text-red-700';
    if (flash.info) return 'border-blue-200 bg-blue-50 text-blue-700';
    return 'border-emerald-200 bg-emerald-50 text-emerald-700';
});

watch(message, (value) => {
    if (!value) return;
    visible.value = true;
    window.setTimeout(() => {
        visible.value = false;
    }, 3500);
}, { immediate: true });
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div v-if="visible && message" class="fixed right-4 top-4 z-[60] max-w-sm rounded-lg border px-4 py-3 text-sm font-semibold shadow-lg" :class="tone">
            {{ message }}
        </div>
    </transition>
</template>
