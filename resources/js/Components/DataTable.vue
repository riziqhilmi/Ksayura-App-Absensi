<script setup>
defineProps({
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
    emptyText: { type: String, default: 'Belum ada data' },
});
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th v-for="column in columns" :key="column.key" class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="rows.length === 0">
                        <td :colspan="columns.length" class="px-4 py-8 text-center text-sm text-slate-400">{{ emptyText }}</td>
                    </tr>
                    <tr v-for="(row, index) in rows" v-else :key="row.id ?? index" class="hover:bg-slate-50">
                        <td v-for="column in columns" :key="column.key" class="px-4 py-3 text-sm text-slate-700">
                            <slot :name="column.key" :row="row">{{ row[column.key] }}</slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
