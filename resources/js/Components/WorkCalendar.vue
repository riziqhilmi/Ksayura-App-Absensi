<script setup>
defineProps({
    days: { type: Array, default: () => [] },
    firstDayOfMonth: { type: Number, default: 0 },
});

const weekdays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

const statusClass = (day) => {
    if (!day) return 'bg-slate-50';
    if (day.is_absent) return 'bg-red-50 ring-1 ring-red-200';
    if (day.is_late) return 'bg-amber-50 ring-1 ring-amber-200';
    if (day.is_present) return 'bg-emerald-50 ring-1 ring-emerald-200';
    if (day.is_holiday) return 'bg-violet-50 ring-1 ring-violet-200';
    if (day.is_today) return 'bg-blue-50 ring-1 ring-blue-200';
    if (day.shift) return 'bg-white';
    if (day.is_weekend) return 'bg-slate-50';
    return 'bg-white';
};

const label = (day) => {
    if (day.is_absent) return 'Tidak Hadir';
    if (day.is_late) return 'Terlambat';
    if (day.is_present) return 'Hadir';
    if (day.is_holiday) return day.holiday?.type_label || 'Libur';
    if (day.shift) return day.shift.name;
    if (day.is_weekend) return 'Akhir Pekan';
    return 'Kosong';
};

const labelClass = (day) => {
    if (day.is_absent) return 'bg-red-100 text-red-700';
    if (day.is_late) return 'bg-amber-100 text-amber-700';
    if (day.is_present) return 'bg-emerald-100 text-emerald-700';
    if (day.is_holiday) return 'bg-violet-100 text-violet-700';
    if (day.shift) return 'bg-emerald-100 text-emerald-700';
    return 'bg-slate-100 text-slate-600';
};
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
            <div v-for="weekday in weekdays" :key="weekday" class="py-3 text-center text-xs font-bold uppercase text-slate-500">
                {{ weekday }}
            </div>
        </div>

        <div class="grid grid-cols-7">
            <div v-for="blank in firstDayOfMonth" :key="`blank-${blank}`" class="min-h-[92px] border-b border-r border-slate-100 bg-slate-50" />

            <div
                v-for="day in days"
                :key="day.date"
                class="min-h-[92px] border-b border-r border-slate-100 p-2 transition hover:bg-slate-50 sm:min-h-[120px]"
                :class="statusClass(day)"
            >
                <div class="flex items-start justify-between gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold" :class="day.is_today ? 'bg-blue-600 text-white' : 'text-slate-700'">
                        {{ day.day }}
                    </span>
                    <span v-if="day.is_weekend" class="hidden rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500 sm:inline-flex">
                        Weekend
                    </span>
                </div>

                <div class="mt-3 space-y-1">
                    <span class="inline-flex max-w-full rounded-md px-2 py-1 text-[11px] font-bold" :class="labelClass(day)">
                        <span class="truncate">{{ label(day) }}</span>
                    </span>
                    <p v-if="day.shift" class="truncate text-[11px] font-medium text-slate-500">
                        {{ day.shift.start_time }} - {{ day.shift.end_time }}
                    </p>
                    <p v-if="day.attendance?.check_in_time" class="truncate text-[11px] text-slate-500">
                        Masuk {{ day.attendance.check_in_time }}
                    </p>
                    <p v-if="day.holiday?.reason" class="truncate text-[11px] text-slate-500">
                        {{ day.holiday.reason }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
