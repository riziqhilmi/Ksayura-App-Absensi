<script setup>
defineProps({
    label: { type: String, required: true },
    error: { type: String, default: '' },
});
</script>

<template>
    <label class="block">
        <span class="text-sm font-semibold text-slate-700">{{ label }}</span>
        <span class="mt-1 block">
            <slot />
        </span>
        <span v-if="error" class="mt-1 block text-sm text-red-600">{{ error }}</span>
    </label>
</template>
