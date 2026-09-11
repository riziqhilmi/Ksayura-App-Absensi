<script setup>
defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: 'Apakah Anda yakin?' },
    confirmText: { type: String, default: 'Ya, lanjutkan' },
    cancelText: { type: String, default: 'Batal' },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h2 class="text-lg font-bold text-slate-900">{{ title }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ message }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50" @click="emit('cancel')">
                    {{ cancelText }}
                </button>
                <button type="button" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="emit('confirm')">
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>
