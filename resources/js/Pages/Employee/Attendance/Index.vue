<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import AppShell from '../../../Components/AppShell.vue';
import Card from '../../../Components/Card.vue';
import ConfirmDialog from '../../../Components/ConfirmDialog.vue';
import Pagination from '../../../Components/Pagination.vue';

const props = defineProps({
    attendances: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        required: true,
    },

    todayAttendance: {
        type: Object,
        default: null,
    },

    todayShift: {
        type: Object,
        default: null,
    },

    officeLocation: {
        type: Object,
        default: () => ({}),
    },

    filters: {
        type: Object,
        required: true,
    },

    options: {
        type: Object,
        required: true,
    },

    links: {
        type: Object,
        required: true,
    },
});


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const now = ref(new Date());

const todayStatus = ref({
    status: props.todayAttendance?.status || 'loading',

    checked_in: Boolean(
        props.todayAttendance?.check_in_time
    ),

    checked_out: Boolean(
        props.todayAttendance?.check_out_time
    ),

    check_in_time:
        props.todayAttendance?.check_in_time,

    check_out_time:
        props.todayAttendance?.check_out_time,

    shift: props.todayShift,
});


const locationState = reactive({
    supported: true,

    permission: 'checking',

    message: 'Mengecek lokasi...',

    detail: 'Mohon tunggu sebentar',

    last: null,

    proximity: null,
});


const loadingAction = ref('');


const notice = reactive({
    show: false,

    type: 'info',

    message: '',
});


const confirmState = reactive({
    show: false,

    title: '',

    message: '',

    action: null,

    confirmText: 'Lanjutkan',
});


const selectedMonth = ref(
    props.filters.month
);

const selectedYear = ref(
    props.filters.year
);


let statusTimer = null;

let clockTimer = null;

let deviceFingerprintPromise = null;


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

const csrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';


/*
|--------------------------------------------------------------------------
| Status Labels
|--------------------------------------------------------------------------
*/

const statusLabels = {
    loading: 'Memuat',

    no_shift: 'Tidak Ada Shift',

    holiday: 'Libur',

    absent: 'Tidak Hadir',

    past_check_in: 'Tidak Hadir',

    not_started: 'Belum Absen',

    present: 'Hadir',

    late: 'Terlambat',

    half_day: 'Setengah Hari',

    leave: 'Cuti',

    auto_checkout: 'Auto Check Out',
};


/*
|--------------------------------------------------------------------------
| Status Tone
|--------------------------------------------------------------------------
*/

const statusTone = computed(() => {
    const status = todayStatus.value.status;

    if (
        status === 'present'
        && todayStatus.value.checked_in
        && !todayStatus.value.checked_out
    ) {
        return 'bg-emerald-400 text-emerald-950';
    }

    if (status === 'late') {
        return 'bg-amber-400 text-amber-950';
    }

    if (
        status === 'holiday'
        || status === 'leave'
    ) {
        return 'bg-violet-400 text-violet-950';
    }

    if (
        status === 'absent'
        || status === 'past_check_in'
        || status === 'no_shift'
    ) {
        return 'bg-red-500 text-white';
    }

    if (
        status === 'half_day'
        || status === 'auto_checkout'
    ) {
        return 'bg-blue-400 text-blue-950';
    }

    return 'bg-white text-slate-700';
});


/*
|--------------------------------------------------------------------------
| Location Tone
|--------------------------------------------------------------------------
*/

const locationTone = computed(() => {
    if (
        locationState.permission === 'granted'
        && locationState.proximity?.is_within_radius !== false
    ) {
        return 'bg-emerald-50 text-emerald-700 ring-emerald-100';
    }

    if (
        locationState.permission === 'checking'
    ) {
        return 'bg-amber-50 text-amber-700 ring-amber-100';
    }

    return 'bg-red-50 text-red-700 ring-red-100';
});


/*
|--------------------------------------------------------------------------
| Status Text
|--------------------------------------------------------------------------
*/

const statusText = computed(() => {
    if (
        todayStatus.value.checked_in
        && !todayStatus.value.checked_out
    ) {
        return todayStatus.value.status === 'late'
            ? 'Sedang bekerja • Terlambat'
            : 'Sedang bekerja';
    }

    if (
        todayStatus.value.checked_out
    ) {
        return 'Selesai hari ini';
    }

    return (
        statusLabels[
            todayStatus.value.status
        ]
        || todayStatus.value.status
    );
});


/*
|--------------------------------------------------------------------------
| Current Shift
|--------------------------------------------------------------------------
*/

const currentShift = computed(() =>
    todayStatus.value.shift
    || props.todayShift
);


/*
|--------------------------------------------------------------------------
| Attendance Availability
|--------------------------------------------------------------------------
*/

const canCheckIn = computed(() => {
    const status =
        todayStatus.value.status;

    return (
        locationState.permission === 'granted'
        && status === 'not_started'
        && todayStatus.value.can_check_in !== false
    );
});


const canCheckOut = computed(() =>
    locationState.permission === 'granted'
    && todayStatus.value.checked_in
    && !todayStatus.value.checked_out
);


/*
|--------------------------------------------------------------------------
| Primary Action
|--------------------------------------------------------------------------
*/

const primaryAction = computed(() => {
    if (canCheckOut.value) {
        return {
            label: 'Check Out',
            type: 'out',
            tone: 'from-orange-500 via-orange-500 to-amber-500',
        };
    }

    return {
        label: 'Check In',
        type: 'in',
        tone: 'from-blue-700 via-blue-600 to-sky-500',
    };
});


/*
|--------------------------------------------------------------------------
| Disabled Reason
|--------------------------------------------------------------------------
*/

const actionDisabledReason = computed(() => {
    if (loadingAction.value) {
        return 'Sedang memproses absensi...';
    }

    if (
        primaryAction.value.type === 'out'
    ) {
        if (
            todayStatus.value.checked_out
        ) {
            return 'Absensi hari ini sudah selesai.';
        }

        if (
            !todayStatus.value.checked_in
        ) {
            return 'Check out tersedia setelah check in.';
        }

        if (
            locationState.permission !== 'granted'
        ) {
            return 'Aktifkan lokasi untuk check out.';
        }

        return '';
    }

    if (
        locationState.permission !== 'granted'
    ) {
        return 'Aktifkan lokasi untuk check in.';
    }

    if (
        todayStatus.value.status === 'no_shift'
    ) {
        return 'Anda belum memiliki shift hari ini.';
    }

    if (
        todayStatus.value.status === 'holiday'
    ) {
        return 'Hari ini adalah jadwal libur.';
    }

    if (
        ['absent', 'past_check_in'].includes(
            todayStatus.value.status
        )
    ) {
        return 'Anda sudah tercatat tidak hadir hari ini.';
    }

    if (
        todayStatus.value.can_check_in === false
    ) {
        return `Check in mulai pukul ${todayStatus.value.available_from}.`;
    }

    return '';
});


/*
|--------------------------------------------------------------------------
| Attendance Info
|--------------------------------------------------------------------------
*/

const workDurationText = computed(() => {
    if (
        todayStatus.value.work_duration_text
    ) {
        return todayStatus.value.work_duration_text;
    }

    if (
        todayStatus.value.checked_in
        && !todayStatus.value.checked_out
    ) {
        return 'Sedang berlangsung';
    }

    return '-';
});


const lateText = computed(() => {
    if (
        todayStatus.value.status === 'late'
        && todayStatus.value.late_minutes > 0
    ) {
        return `${todayStatus.value.late_minutes} menit`;
    }

    if (
        todayStatus.value.checked_in
        && todayStatus.value.status === 'present'
    ) {
        return 'Tepat waktu';
    }

    return '-';
});


/*
|--------------------------------------------------------------------------
| Notice
|--------------------------------------------------------------------------
*/

const showNotice = (
    message,
    type = 'info'
) => {
    notice.show = true;

    notice.type = type;

    notice.message = message;

    window.setTimeout(() => {
        notice.show = false;
    }, 4200);
};


/*
|--------------------------------------------------------------------------
| Date & Clock
|--------------------------------------------------------------------------
*/

const formatClock = computed(() =>
    now.value.toLocaleTimeString(
        'id-ID',
        {
            hour: '2-digit',

            minute: '2-digit',

            second: '2-digit',
        }
    )
);


const formatDate = computed(() =>
    now.value.toLocaleDateString(
        'id-ID',
        {
            weekday: 'long',

            day: '2-digit',

            month: 'long',

            year: 'numeric',
        }
    )
);


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const formatCurrencylessPercent = (
    value
) => {
    if (!props.stats.total) {
        return 0;
    }

    return Math.round(
        (
            (value || 0)
            / props.stats.total
        ) * 100
    );
};


/*
|--------------------------------------------------------------------------
| Open Location
|--------------------------------------------------------------------------
*/

const openLocation = (
    lat,
    lng
) => {
    window.open(
        `https://www.google.com/maps?q=${lat},${lng}`,
        '_blank'
    );
};


/*
|--------------------------------------------------------------------------
| Device Fingerprint
|--------------------------------------------------------------------------
*/

const getStableDeviceId = () => {
    const key =
        'ksayura_attendance_device_id';

    let deviceId =
        localStorage.getItem(key);

    if (!deviceId) {
        deviceId =
            (
                crypto.randomUUID
                && crypto.randomUUID()
            )
            || `${Date.now()}-${Math.random()
                .toString(16)
                .slice(2)}`;

        localStorage.setItem(
            key,
            deviceId
        );
    }

    return deviceId;
};


const getDeviceFingerprint =
    async () => {
        if (
            deviceFingerprintPromise
        ) {
            return deviceFingerprintPromise;
        }

        deviceFingerprintPromise =
            (async () => {
                const rawFingerprint = [
                    getStableDeviceId(),

                    navigator.userAgent || '',

                    navigator.platform || '',

                    navigator.language || '',

                    screen.width,

                    screen.height,

                    screen.colorDepth,

                    new Date()
                        .getTimezoneOffset(),
                ].join('|');


                if (!crypto.subtle) {
                    return rawFingerprint
                        .padEnd(64, '0')
                        .slice(0, 64);
                }


                const data =
                    new TextEncoder()
                        .encode(
                            rawFingerprint
                        );


                const hashBuffer =
                    await crypto.subtle.digest(
                        'SHA-256',
                        data
                    );


                return Array
                    .from(
                        new Uint8Array(
                            hashBuffer
                        )
                    )
                    .map(
                        (byte) =>
                            byte
                                .toString(16)
                                .padStart(2, '0')
                    )
                    .join('');
            })();


        return deviceFingerprintPromise;
    };


/*
|--------------------------------------------------------------------------
| Attendance Payload
|--------------------------------------------------------------------------
*/

const buildAttendancePayload =
    async (position) => ({
        latitude:
            position.coords.latitude,

        longitude:
            position.coords.longitude,

        accuracy: Math.round(
            position.coords.accuracy
            || 999
        ),

        location_recorded_at:
            new Date(
                position.timestamp
            ).toISOString(),

        client_recorded_at:
            new Date().toISOString(),

        timezone:
            Intl.DateTimeFormat()
                .resolvedOptions()
                .timeZone
            || 'unknown',

        timezone_offset_minutes:
            new Date()
                .getTimezoneOffset(),

        device_fingerprint:
            await getDeviceFingerprint(),

        is_mock_location: false,
    });


/*
|--------------------------------------------------------------------------
| Check Location Proximity
|--------------------------------------------------------------------------
*/

const checkLocationProximity =
    async (
        lat,
        lng
    ) => {
        try {
            const response =
                await fetch(
                    props.links
                        .validateLocation,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken(),
                        },

                        body:
                            JSON.stringify({
                                latitude:
                                    lat,

                                longitude:
                                    lng,
                            }),
                    }
                );


            const data =
                await response.json();


            locationState.proximity =
                data;


            if (
                data.is_within_radius
            ) {
                locationState.message =
                    'Lokasi valid';

                locationState.detail =
                    `Dalam radius kantor (${data.distance} meter)`;

                return;
            }


            locationState.message =
                'Di luar radius';

            locationState.detail =
                `Jarak ${data.distance} meter dari kantor, maksimal ${data.radius} meter`;
        } catch (error) {
            locationState.detail =
                'Gagal mengecek jarak ke kantor';
        }
    };


/*
|--------------------------------------------------------------------------
| Request Location
|--------------------------------------------------------------------------
*/

const requestLocationPermission =
    async () => {
        locationState.supported =
            'geolocation'
            in navigator;


        if (
            !locationState.supported
        ) {
            locationState.permission =
                'denied';

            locationState.message =
                'Geolokasi tidak tersedia';

            locationState.detail =
                'Gunakan browser modern dengan dukungan GPS';

            return;
        }


        locationState.permission =
            'checking';

        locationState.message =
            'Meminta akses lokasi...';

        locationState.detail =
            'Izinkan akses lokasi di browser';


        navigator.geolocation
            .getCurrentPosition(
                (position) => {
                    locationState.permission =
                        'granted';


                    locationState.last = {
                        latitude:
                            position.coords
                                .latitude,

                        longitude:
                            position.coords
                                .longitude,

                        accuracy:
                            Math.round(
                                position
                                    .coords
                                    .accuracy
                                || 999
                            ),

                        recorded_at:
                            new Date(
                                position
                                    .timestamp
                            ).toISOString(),
                    };


                    locationState.message =
                        'Lokasi aktif';

                    locationState.detail =
                        `Akurasi ${locationState.last.accuracy}m`;


                    checkLocationProximity(
                        position.coords
                            .latitude,

                        position.coords
                            .longitude
                    );
                },


                (error) => {
                    locationState.permission =
                        'denied';

                    locationState.message =
                        'Lokasi belum aktif';

                    locationState.detail =
                        error.message
                        || 'Aktifkan izin lokasi untuk absensi';
                },


                {
                    enableHighAccuracy:
                        true,

                    timeout: 10000,

                    maximumAge: 0,
                }
            );
    };


/*
|--------------------------------------------------------------------------
| Get Current Location
|--------------------------------------------------------------------------
*/

const getLocation =
    () =>
        new Promise(
            (
                resolve,
                reject
            ) => {
                if (
                    !(
                        'geolocation'
                        in navigator
                    )
                ) {
                    reject(
                        new Error(
                            'Browser tidak mendukung geolokasi'
                        )
                    );

                    return;
                }


                if (
                    locationState.permission
                    !== 'granted'
                ) {
                    reject(
                        new Error(
                            'Izin lokasi belum diberikan. Aktifkan lokasi terlebih dahulu.'
                        )
                    );

                    return;
                }


                navigator.geolocation
                    .getCurrentPosition(
                        (position) =>
                            buildAttendancePayload(
                                position
                            )
                                .then(
                                    resolve
                                )
                                .catch(
                                    reject
                                ),

                        (error) =>
                            reject(
                                new Error(
                                    `Gagal mendapatkan lokasi: ${error.message}`
                                )
                            ),

                        {
                            enableHighAccuracy:
                                true,

                            timeout:
                                10000,

                            maximumAge:
                                0,
                        }
                    );
            }
        );


/*
|--------------------------------------------------------------------------
| Refresh Today Status
|--------------------------------------------------------------------------
*/

const refreshTodayStatus =
    async () => {
        try {
            const response =
                await fetch(
                    props.links
                        .todayStatus,
                    {
                        headers: {
                            Accept:
                                'application/json',
                        },
                    }
                );


            todayStatus.value =
                await response.json();
        } catch (error) {
            todayStatus.value = {
                ...todayStatus.value,

                status: 'error',
            };
        }
    };


/*
|--------------------------------------------------------------------------
| Error Message
|--------------------------------------------------------------------------
*/

const errorText = (
    data,
    fallback
) => {
    let message =
        data.error
        || fallback;


    if (
        data.is_holiday
    ) {
        message +=
            ' Hari ini adalah jadwal libur Anda.';
    }


    if (
        data.available_from
    ) {
        message +=
            ` Check in dapat dilakukan mulai pukul ${data.available_from}.`;
    }


    if (
        data.distance !== undefined
    ) {
        message +=
            ` Jarak Anda ${data.distance} meter dari kantor. Maksimal ${data.max_distance} meter.`;
    }


    return message;
};


/*
|--------------------------------------------------------------------------
| Submit Attendance
|--------------------------------------------------------------------------
*/

const submitAttendance =
    async (type) => {
        loadingAction.value =
            type;


        try {
            const payload =
                await getLocation();


            const response =
                await fetch(
                    type === 'in'
                        ? props.links
                            .checkIn
                        : props.links
                            .checkOut,

                    {
                        method:
                            'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken(),

                            Accept:
                                'application/json',
                        },

                        body:
                            JSON.stringify(
                                payload
                            ),
                    }
                );


            const data =
                await response.json();


            if (
                !response.ok
                || !data.success
            ) {
                showNotice(
                    errorText(
                        data,

                        type === 'in'
                            ? 'Gagal check in'
                            : 'Gagal check out'
                    ),

                    'error'
                );

                return;
            }


            showNotice(
                type === 'in'
                    ? `Check in berhasil. Status: ${
                        statusLabels[
                            data.status
                        ]
                        || data.status
                    }${
                        data.late_text
                            ? `, terlambat ${data.late_text}`
                            : ''
                    }.`
                    : `Check out berhasil. Durasi kerja: ${data.work_duration_text}.`,

                'success'
            );


            await refreshTodayStatus();


            router.reload({
                only: [
                    'attendances',
                    'stats',
                    'todayAttendance',
                ],

                preserveScroll:
                    true,
            });
        } catch (error) {
            showNotice(
                error.message
                || 'Gagal memproses absensi',

                'error'
            );
        } finally {
            loadingAction.value =
                '';
        }
    };


/*
|--------------------------------------------------------------------------
| Primary Action Handler
|--------------------------------------------------------------------------
*/

const handlePrimaryAction =
    () => {
        const type =
            primaryAction.value.type;


        if (
            type === 'in'
        ) {
            if (
                todayStatus.value.status
                === 'no_shift'
            ) {
                return showNotice(
                    'Anda belum diberi shift. Silakan hubungi owner/admin.',

                    'error'
                );
            }


            if (
                todayStatus.value.status
                === 'holiday'
            ) {
                return showNotice(
                    'Hari ini adalah jadwal libur Anda. Check in tidak dapat dilakukan.',

                    'error'
                );
            }


            if (
                [
                    'absent',
                    'past_check_in',
                ].includes(
                    todayStatus.value.status
                )
            ) {
                return showNotice(
                    'Anda sudah melewati jam shift dan tercatat tidak hadir.',

                    'error'
                );
            }


            if (
                todayStatus.value.can_check_in
                === false
            ) {
                return showNotice(
                    `Anda masih belum bisa absen. Check in mulai pukul ${todayStatus.value.available_from}.`,

                    'warning'
                );
            }
        }


        if (
            locationState.permission
            !== 'granted'
        ) {
            requestLocationPermission();


            return showNotice(
                'Aktifkan lokasi terlebih dahulu untuk absensi.',

                'warning'
            );
        }


        confirmState.show =
            true;


        confirmState.title =
            type === 'in'
                ? 'Check In'
                : 'Check Out';


        confirmState.message =
            type === 'in'
                ? 'Pastikan Anda berada di lokasi kantor sebelum melakukan check in.'
                : 'Pastikan Anda berada di lokasi kantor sebelum melakukan check out.';


        confirmState.confirmText =
            type === 'in'
                ? 'Check In Sekarang'
                : 'Check Out Sekarang';


        confirmState.action =
            () =>
                submitAttendance(
                    type
                );
    };


/*
|--------------------------------------------------------------------------
| Confirmation
|--------------------------------------------------------------------------
*/

const runConfirmed =
    () => {
        const action =
            confirmState.action;


        confirmState.show =
            false;


        if (action) {
            action();
        }
    };


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const applyFilter =
    () => {
        router.get(
            props.links.index,

            {
                month:
                    selectedMonth.value,

                year:
                    selectedYear.value,
            },

            {
                preserveScroll:
                    true,

                preserveState:
                    true,

                replace:
                    true,
            }
        );
    };


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    requestLocationPermission();

    refreshTodayStatus();


    clockTimer =
        window.setInterval(
            () => {
                now.value =
                    new Date();
            },

            1000
        );


    statusTimer =
        window.setInterval(
            refreshTodayStatus,

            60000
        );
});


onUnmounted(() => {
    if (clockTimer) {
        window.clearInterval(
            clockTimer
        );
    }


    if (statusTimer) {
        window.clearInterval(
            statusTimer
        );
    }
});
</script>


<template>
    <Head title="Absensi Saya" />

    <AppShell>
        <div
            class="mx-auto max-w-6xl space-y-5 pb-8"

        >

            <!-- ======================================================= -->
            <!-- NOTIFICATION -->
            <!-- ======================================================= -->

            <transition
                enter-active-class="transition duration-200"
                enter-from-class="translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="notice.show"
                    class="
                        fixed
                        inset-x-4
                        top-4
                        z-50
                        rounded-2xl
                        border
                        px-4
                        py-3
                        text-sm
                        font-semibold
                        shadow-xl
                        sm:left-auto
                        sm:right-6
                        sm:w-96
                    "
                    :class="
                        notice.type === 'error'
                            ? 'border-red-200 bg-red-50 text-red-700'
                            : notice.type === 'warning'
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    "
                >
                    {{ notice.message }}
                </div>
            </transition>



            <!-- ======================================================= -->
            <!-- HERO -->
            <!-- ======================================================= -->

            <section
                class="
                    overflow-hidden
                    rounded-[30px]
                    bg-gradient-to-br
                    from-emerald-700
                    via-green-700
                    to-teal-700
                    text-white
                    shadow-xl
                    shadow-emerald-950/10
                "
            >

                <div
                    class="
                        relative
                        overflow-hidden
                        px-5
                        py-6
                        sm:px-7
                        sm:py-7
                    "
                >

                    <!-- decorative circle -->
                    <div
                        class="
                            pointer-events-none
                            absolute
                            -right-20
                            -top-20
                            h-64
                            w-64
                            rounded-full
                            bg-emerald-400/10
                        "
                    />

                    <div
                        class="
                            pointer-events-none
                            absolute
                            -bottom-20
                            -left-16
                            h-48
                            w-48
                            rounded-full
                            bg-teal-300/10
                        "
                    />


                    <div
                        class="
                            relative
                            z-10
                        "
                    >

                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-4
                            "
                        >

                            <div>

                                <p
                                    class="
                                        text-sm
                                        font-semibold
                                    text-emerald-50
                                    "
                                >
                                    {{ formatDate }}
                                </p>


                                <h1
                                    class="
                                        mt-3
                                        font-mono
                                        text-4xl
                                        font-black
                                        tracking-tight
                                        text-white
                                        sm:text-6xl
                                    "
                                >
                                    {{ formatClock }}
                                </h1>


                                <p
                                    class="
                                        mt-2
                                        text-sm
                                        font-medium
                                        text-white
                                    "
                                >
                                    {{ statusText }}
                                </p>

                            </div>


                            <span
                                class="
                                    shrink-0
                                    rounded-full
                                    px-3
                                    py-1.5
                                    text-xs
                                    font-black
                                    shadow-sm
                                "
                                :class="statusTone"
                            >
                                {{
                                    statusLabels[
                                        todayStatus.status
                                    ]
                                    || todayStatus.status
                                }}
                            </span>

                        </div>



                        <!-- SHIFT CARD -->

                        <div
                            class="
                                mt-6
                                rounded-2xl
                                border
                                border-white/20
                                bg-emerald-600
                                p-4
                                shadow-inner
                                sm:p-5
                            "
                        >

                            <div
                                class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-3
                                "
                            >

                                <p
                                    class="
                                        text-xs
                                        font-bold
                                        uppercase
                                        tracking-[0.14em]
                                        text-emerald-50
                                    "
                                >
                                    Shift Hari Ini
                                </p>


                                <div
                                    class="
                                        flex
                                        h-9
                                        w-9
                                        items-center
                                        justify-center
                                        rounded-xl
                                        bg-white
                                        text-emerald-700
                                    "
                                >

                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3"
                                        />

                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            stroke-width="2"
                                        />
                                    </svg>

                                </div>

                            </div>


                            <div
                                class="
                                    mt-3
                                    flex
                                    flex-col
                                    gap-1
                                    sm:flex-row
                                    sm:items-end
                                    sm:justify-between
                                "
                            >

                                <div>

                                    <p
                                        class="
                                            text-xl
                                            font-black
                                            text-white
                                        "
                                    >
                                        {{
                                            currentShift?.name
                                            || 'Anda belum diberi shift'
                                        }}
                                    </p>


                                    <p
                                        class="
                                            mt-1
                                            text-sm
                                            font-medium
                                            text-white
                                        "
                                    >

                                        <template
                                            v-if="currentShift"
                                        >
                                            {{
                                                currentShift.start_time
                                            }}

                                            -

                                            {{
                                                currentShift.end_time
                                            }}

                                            • Toleransi

                                            {{
                                                currentShift.grace_period
                                            }}

                                            menit
                                        </template>


                                        <template
                                            v-else
                                        >
                                            Silakan hubungi owner/admin
                                        </template>

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </section>



            <!-- ======================================================= -->
            <!-- MAIN GRID -->
            <!-- ======================================================= -->

            <section
                class="
                    grid
                    grid-cols-1
                    gap-4
                    lg:grid-cols-[1fr_360px]
                "
            >

                <div class="space-y-4">


                    <!-- =================================================== -->
                    <!-- LOCATION CARD -->
                    <!-- =================================================== -->

                    <Card>

                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-4
                            "
                        >

                            <div>

                                <p
                                    class="
                                        text-xs
                                        font-bold
                                        uppercase
                                        tracking-wider
                                        text-slate-400
                                    "
                                >
                                    Status Lokasi
                                </p>


                                <h2
                                    class="
                                        mt-1
                                        text-xl
                                        font-black
                                        text-slate-950
                                    "
                                >
                                    {{ locationState.message }}
                                </h2>


                                <p
                                    class="
                                        mt-1
                                        text-sm
                                        leading-relaxed
                                        text-slate-500
                                    "
                                >
                                    {{ locationState.detail }}
                                </p>

                            </div>


                            <div
                                class="
                                    flex
                                    h-12
                                    w-12
                                    shrink-0
                                    items-center
                                    justify-center
                                    rounded-2xl
                                "
                                :class="
                                    locationState.permission === 'granted'
                                    && locationState.proximity?.is_within_radius !== false

                                        ? 'bg-emerald-100 text-emerald-700'

                                        : locationState.permission === 'checking'

                                            ? 'bg-amber-100 text-amber-700'

                                            : 'bg-red-100 text-red-700'
                                "
                            >

                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"
                                    />

                                    <circle
                                        cx="12"
                                        cy="10"
                                        r="2"
                                        stroke-width="2"
                                    />

                                </svg>

                            </div>

                        </div>



                        <!-- OUTSIDE RADIUS WARNING -->

                        <div
                            v-if="
                                locationState.proximity
                                    ?.is_within_radius
                                === false
                            "
                            class="
                                mt-4
                                rounded-2xl
                                border
                                border-amber-200
                                bg-amber-50
                                px-4
                                py-3
                                text-sm
                                leading-relaxed
                                text-amber-800
                            "
                        >

                            <div
                                class="
                                    flex
                                    gap-2
                                "
                            >

                                <svg
                                    class="
                                        mt-0.5
                                        h-5
                                        w-5
                                        shrink-0
                                    "
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v4m0 4h.01M10.3 3.4L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.4a2 2 0 00-3.4 0z"
                                    />
                                </svg>


                                <p>
                                    Pastikan Anda berada di lokasi kantor.

                                    <span
                                        class="font-semibold"
                                    >
                                        Lokasi kantor:
                                    </span>

                                    {{
                                        officeLocation.address
                                        || 'Belum diatur'
                                    }}
                                </p>

                            </div>

                        </div>



                        <!-- REFRESH LOCATION -->

                        <button
                            type="button"
                            class="
                                mt-4
                                flex
                                w-full
                                items-center
                                justify-center
                                gap-2
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white
                                px-4
                                py-3
                                text-sm
                                font-bold
                                text-slate-700
                                transition
                                duration-200
                                hover:border-emerald-200
                                hover:bg-emerald-50
                                hover:text-emerald-700
                                active:scale-[0.99]
                            "
                            @click="
                                requestLocationPermission
                            "
                        >

                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v6h6M20 20v-6h-6M5.6 18.4A8 8 0 0118.4 5.6M18.4 5.6H14M5.6 18.4H10"
                                />
                            </svg>

                            Perbarui Lokasi

                        </button>

                    </Card>



                    <!-- =================================================== -->
                    <!-- PRIMARY CHECK IN / CHECK OUT -->
                    <!-- =================================================== -->

                    <div>

                        <button
    type="button"
    class="
        flex
        min-h-[88px]
        w-full
        items-center
        justify-center
        rounded-[26px]
        px-6
        py-6
        text-lg
        font-black
        text-white
        shadow-lg
        transition
        duration-200
        hover:-translate-y-0.5
        hover:shadow-xl
        active:scale-[0.98]
        disabled:cursor-wait
        disabled:opacity-70
    "
    :class="
        primaryAction.type === 'in'
            ? 'bg-blue-600 hover:bg-blue-700'
            : 'bg-orange-500 hover:bg-orange-600'
    "
    :disabled="loadingAction !== ''"
    @click="handlePrimaryAction"
>

                            <span
                                v-if="loadingAction"
                                class="
                                    inline-flex
                                    items-center
                                    gap-3
                                "
                            >

                                <span
                                    class="
                                        h-5
                                        w-5
                                        animate-spin
                                        rounded-full
                                        border-2
                                        border-slate-400
                                        border-t-slate-700
                                    "
                                />

                                Memproses...

                            </span>


                            <span
                                v-else
                                class="
                                    flex
                                    items-center
                                    gap-3
                                "
                            >

                                <span
                                    class="
                                        flex
                                        h-10
                                        w-10
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-white/20
                                    "
                                >

                                    <svg
                                        class="
                                            h-5
                                            w-5
                                        "
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 12h14M12 5l7 7-7 7"
                                        />
                                    </svg>

                                </span>


                                {{ primaryAction.label }}

                            </span>

                        </button>



                        <!-- DISABLED REASON -->

                        <p
                            v-if="
                                actionDisabledReason
                            "
                            class="
                                mt-2
                                px-2
                                text-center
                                text-xs
                                font-medium
                                text-slate-500
                            "
                        >
                            {{ actionDisabledReason }}
                        </p>

                    </div>



                    <!-- =================================================== -->
                    <!-- TODAY STATS -->
                    <!-- =================================================== -->

                    <div
                        class="
                            grid
                            grid-cols-2
                            gap-3
                            sm:grid-cols-4
                        "
                    >

                        <!-- CHECK IN -->

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white
                                p-4
                                shadow-sm
                            "
                        >

                            <div
                                class="
                                    flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-emerald-100
                                    text-emerald-700
                                "
                            >

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 12h14M13 6l6 6-6 6"
                                    />
                                </svg>

                            </div>


                            <p
                                class="
                                    mt-3
                                    text-xs
                                    font-semibold
                                    text-slate-400
                                "
                            >
                                Jam Masuk
                            </p>


                            <p
                                class="
                                    mt-1
                                    text-xl
                                    font-black
                                    text-slate-900
                                "
                            >
                                {{
                                    todayStatus.check_in_time
                                    || '-'
                                }}
                            </p>

                        </div>



                        <!-- CHECK OUT -->

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white
                                p-4
                                shadow-sm
                            "
                        >

                            <div
                                class="
                                    flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-blue-100
                                    text-blue-700
                                "
                            >

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 12H5M11 18l-6-6 6-6"
                                    />
                                </svg>

                            </div>


                            <p
                                class="
                                    mt-3
                                    text-xs
                                    font-semibold
                                    text-slate-400
                                "
                            >
                                Jam Pulang
                            </p>


                            <p
                                class="
                                    mt-1
                                    text-xl
                                    font-black
                                    text-slate-900
                                "
                            >
                                {{
                                    todayStatus.check_out_time
                                    || '-'
                                }}
                            </p>

                        </div>



                        <!-- DURATION -->

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white
                                p-4
                                shadow-sm
                            "
                        >

                            <div
                                class="
                                    flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-violet-100
                                    text-violet-700
                                "
                            >

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke-width="2"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 7v5l3 2"
                                    />
                                </svg>

                            </div>


                            <p
                                class="
                                    mt-3
                                    text-xs
                                    font-semibold
                                    text-slate-400
                                "
                            >
                                Durasi
                            </p>


                            <p
                                class="
                                    mt-1
                                    text-base
                                    font-black
                                    text-slate-900
                                    sm:text-lg
                                "
                            >
                                {{ workDurationText }}
                            </p>

                        </div>



                        <!-- LATE -->

                        <div
                            class="
                                rounded-2xl
                                border
                                border-slate-200
                                bg-white
                                p-4
                                shadow-sm
                            "
                        >

                            <div
                                class="
                                    flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-amber-100
                                    text-amber-700
                                "
                            >

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v4m0 4h.01M10.3 3.4L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.4a2 2 0 00-3.4 0z"
                                    />
                                </svg>

                            </div>


                            <p
                                class="
                                    mt-3
                                    text-xs
                                    font-semibold
                                    text-slate-400
                                "
                            >
                                Terlambat
                            </p>


                            <p
                                class="
                                    mt-1
                                    text-base
                                    font-black
                                    sm:text-lg
                                "
                                :class="
                                    lateText === 'Tepat waktu'

                                        ? 'text-emerald-600'

                                        : todayStatus.status === 'late'

                                            ? 'text-amber-600'

                                            : 'text-slate-900'
                                "
                            >
                                {{ lateText }}
                            </p>

                        </div>

                    </div>

                </div>



                <!-- =================================================== -->
                <!-- MONTH SUMMARY -->
                <!-- =================================================== -->

                <Card>

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-3
                        "
                    >

                        <div>

                            <p
                                class="
                                    text-xs
                                    font-bold
                                    uppercase
                                    tracking-wider
                                    text-slate-400
                                "
                            >
                                Statistik
                            </p>


                            <h2
                                class="
                                    mt-1
                                    text-lg
                                    font-black
                                    text-slate-900
                                "
                            >
                                Ringkasan Bulan Ini
                            </h2>

                        </div>


                        <div
                            class="
                                flex
                                h-11
                                w-11
                                items-center
                                justify-center
                                rounded-2xl
                                bg-emerald-100
                                text-emerald-700
                            "
                        >

                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 19V9m5 10V5m5 14v-7m5 7V3"
                                />
                            </svg>

                        </div>

                    </div>



                    <div
                        class="
                            mt-5
                            grid
                            grid-cols-2
                            gap-3
                        "
                    >

                        <div
                            v-for="
                                item in [
                                    [
                                        'Total',
                                        stats.total,
                                        'bg-slate-500'
                                    ],

                                    [
                                        'Hadir',
                                        stats.present,
                                        'bg-emerald-500'
                                    ],

                                    [
                                        'Terlambat',
                                        stats.late,
                                        'bg-amber-500'
                                    ],

                                    [
                                        'Tidak Hadir',
                                        stats.absent,
                                        'bg-red-500'
                                    ],

                                    [
                                        'Cuti / 1/2 Hari',
                                        (
                                            stats.leave
                                            || 0
                                        )
                                        +
                                        (
                                            stats.half_day
                                            || 0
                                        ),

                                        'bg-violet-500'
                                    ],

                                    [
                                        'Auto CO',
                                        stats.auto_checkout
                                        || 0,

                                        'bg-orange-500'
                                    ]
                                ]
                            "
                            :key="item[0]"
                            class="
                                rounded-2xl
                                bg-slate-50
                                p-3
                            "
                        >

                            <p
                                class="
                                    text-xs
                                    font-semibold
                                    text-slate-500
                                "
                            >
                                {{ item[0] }}
                            </p>


                            <p
                                class="
                                    mt-1
                                    text-2xl
                                    font-black
                                    text-slate-900
                                "
                            >
                                {{ item[1] }}
                            </p>


                            <div
                                class="
                                    mt-3
                                    h-1.5
                                    overflow-hidden
                                    rounded-full
                                    bg-slate-200
                                "
                            >

                                <div
                                    class="
                                        h-full
                                        rounded-full
                                    "
                                    :class="
                                        item[2]
                                    "
                                    :style="{
                                        width:
                                            `${
                                                formatCurrencylessPercent(
                                                    item[1]
                                                )
                                            }%`
                                    }"
                                />

                            </div>

                        </div>

                    </div>

                </Card>

            </section>



            <!-- ======================================================= -->
            <!-- ATTENDANCE HISTORY -->
            <!-- ======================================================= -->

            <Card>

                <div
                    class="
                        flex
                        flex-col
                        gap-4
                        sm:flex-row
                        sm:items-center
                        sm:justify-between
                    "
                >

                    <div>

                        <p
                            class="
                                text-xs
                                font-bold
                                uppercase
                                tracking-wider
                                text-slate-400
                            "
                        >
                            Riwayat
                        </p>


                        <h2
                            class="
                                mt-1
                                text-lg
                                font-black
                                text-slate-900
                            "
                        >
                            Riwayat Absensi
                        </h2>


                        <p
                            class="
                                mt-1
                                text-sm
                                text-slate-500
                            "
                        >
                            Catatan absensi sesuai bulan dan tahun pilihan.
                        </p>

                    </div>



                    <div
                        class="
                            grid
                            grid-cols-2
                            gap-2
                            sm:flex
                        "
                    >

                        <select
                            v-model="
                                selectedMonth
                            "
                            class="
                                rounded-xl
                                border-slate-200
                                bg-white
                                px-3
                                py-2.5
                                text-sm
                                font-semibold
                                text-slate-700
                                focus:border-emerald-500
                                focus:ring-emerald-100
                            "
                            @change="
                                applyFilter
                            "
                        >

                            <option
                                v-for="
                                    month
                                    in options.months
                                "
                                :key="
                                    month.value
                                "
                                :value="
                                    month.value
                                "
                            >
                                {{ month.label }}
                            </option>

                        </select>



                        <select
                            v-model="
                                selectedYear
                            "
                            class="
                                rounded-xl
                                border-slate-200
                                bg-white
                                px-3
                                py-2.5
                                text-sm
                                font-semibold
                                text-slate-700
                                focus:border-emerald-500
                                focus:ring-emerald-100
                            "
                            @change="
                                applyFilter
                            "
                        >

                            <option
                                v-for="
                                    year
                                    in options.years
                                "
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>

                        </select>

                    </div>

                </div>



                <!-- =================================================== -->
                <!-- DESKTOP TABLE -->
                <!-- =================================================== -->

                <div
                    class="
                        mt-5
                        hidden
                        overflow-hidden
                        rounded-2xl
                        border
                        border-slate-200
                        md:block
                    "
                >

                    <table
                        class="
                            min-w-full
                            divide-y
                            divide-slate-200
                        "
                    >

                        <thead
                            class="
                                bg-slate-50
                            "
                        >

                            <tr>

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-bold
                                        uppercase
                                        text-slate-500
                                    "
                                >
                                    Tanggal
                                </th>


                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-bold
                                        uppercase
                                        text-slate-500
                                    "
                                >
                                    Shift
                                </th>


                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-bold
                                        uppercase
                                        text-slate-500
                                    "
                                >
                                    Check In
                                </th>


                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-bold
                                        uppercase
                                        text-slate-500
                                    "
                                >
                                    Check Out
                                </th>


                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-bold
                                        uppercase
                                        text-slate-500
                                    "
                                >
                                    Durasi
                                </th>


                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-bold
                                        uppercase
                                        text-slate-500
                                    "
                                >
                                    Status
                                </th>

                            </tr>

                        </thead>



                        <tbody
                            class="
                                divide-y
                                divide-slate-100
                                bg-white
                            "
                        >

                            <tr
                                v-for="
                                    attendance
                                    in attendances.data
                                "
                                :key="
                                    attendance.id
                                "
                                class="
                                    transition
                                    hover:bg-slate-50
                                "
                            >

                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-sm
                                        font-semibold
                                        text-slate-700
                                    "
                                >
                                    {{
                                        attendance.date_label
                                    }}
                                </td>



                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-sm
                                        text-slate-600
                                    "
                                >
                                    {{
                                        attendance.shift?.name
                                        || '-'
                                    }}
                                </td>



                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-sm
                                        text-slate-600
                                    "
                                >

                                    <span>
                                        {{
                                            attendance.check_in_time
                                            || '-'
                                        }}
                                    </span>


                                    <button
                                        v-if="
                                            attendance.latitude_in
                                            && attendance.longitude_in
                                        "
                                        type="button"
                                        class="
                                            ml-2
                                            text-xs
                                            font-bold
                                            text-emerald-700
                                            hover:text-emerald-800
                                        "
                                        @click="
                                            openLocation(
                                                attendance.latitude_in,

                                                attendance.longitude_in
                                            )
                                        "
                                    >
                                        Lihat
                                    </button>

                                </td>



                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-sm
                                        text-slate-600
                                    "
                                >

                                    <span>
                                        {{
                                            attendance.check_out_time
                                            || '-'
                                        }}
                                    </span>


                                    <button
                                        v-if="
                                            attendance.latitude_out
                                            && attendance.longitude_out
                                        "
                                        type="button"
                                        class="
                                            ml-2
                                            text-xs
                                            font-bold
                                            text-emerald-700
                                            hover:text-emerald-800
                                        "
                                        @click="
                                            openLocation(
                                                attendance.latitude_out,

                                                attendance.longitude_out
                                            )
                                        "
                                    >
                                        Lihat
                                    </button>

                                </td>



                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-sm
                                        text-slate-600
                                    "
                                >
                                    {{
                                        attendance.work_duration_text
                                        || '-'
                                    }}
                                </td>



                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            rounded-full
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-bold
                                            ring-1
                                        "
                                        :class="
                                            attendance.status === 'present'

                                                ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'

                                                : attendance.status === 'late'

                                                    ? 'bg-amber-50 text-amber-700 ring-amber-200'

                                                    : attendance.status === 'absent'

                                                        ? 'bg-red-50 text-red-700 ring-red-200'

                                                        : 'bg-blue-50 text-blue-700 ring-blue-200'
                                        "
                                    >
                                        {{
                                            statusLabels[
                                                attendance.status
                                            ]
                                            || attendance.status
                                        }}
                                    </span>


                                    <span
                                        v-if="
                                            attendance.late_minutes
                                            > 0
                                        "
                                        class="
                                            ml-1
                                            text-xs
                                            font-semibold
                                            text-amber-600
                                        "
                                    >
                                        +{{
                                            attendance.late_minutes
                                        }}
                                        menit
                                    </span>

                                </td>

                            </tr>



                            <tr
                                v-if="
                                    attendances.data.length
                                    === 0
                                "
                            >

                                <td
                                    colspan="6"
                                    class="
                                        px-4
                                        py-12
                                        text-center
                                        text-sm
                                        text-slate-400
                                    "
                                >
                                    Belum ada riwayat absensi
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>



                <!-- =================================================== -->
                <!-- MOBILE ATTENDANCE LIST -->
                <!-- =================================================== -->

                <div
                    class="
                        mt-5
                        space-y-3
                        md:hidden
                    "
                >

                    <article
                        v-for="
                            attendance
                            in attendances.data
                        "
                        :key="
                            attendance.id
                        "
                        class="
                            rounded-2xl
                            border
                            border-slate-200
                            bg-white
                            p-4
                            shadow-sm
                        "
                    >

                        <div
                            class="
                                flex
                                items-start
                                justify-between
                                gap-3
                            "
                        >

                            <div>

                                <p
                                    class="
                                        font-black
                                        text-slate-900
                                    "
                                >
                                    {{
                                        attendance.date_label
                                    }}
                                </p>


                                <p
                                    class="
                                        mt-0.5
                                        text-sm
                                        text-slate-500
                                    "
                                >
                                    {{
                                        attendance.shift?.name
                                        || 'Tanpa shift'
                                    }}
                                </p>

                            </div>



                            <span
                                class="
                                    rounded-full
                                    px-2.5
                                    py-1
                                    text-xs
                                    font-bold
                                    ring-1
                                "
                                :class="
                                    attendance.status === 'present'

                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'

                                        : attendance.status === 'late'

                                            ? 'bg-amber-50 text-amber-700 ring-amber-200'

                                            : attendance.status === 'absent'

                                                ? 'bg-red-50 text-red-700 ring-red-200'

                                                : 'bg-blue-50 text-blue-700 ring-blue-200'
                                "
                            >
                                {{
                                    statusLabels[
                                        attendance.status
                                    ]
                                    || attendance.status
                                }}
                            </span>

                        </div>



                        <div
                            class="
                                mt-4
                                grid
                                grid-cols-2
                                gap-3
                                border-t
                                border-slate-100
                                pt-4
                            "
                        >

                            <div>

                                <span
                                    class="
                                        block
                                        text-xs
                                        font-semibold
                                        text-slate-400
                                    "
                                >
                                    Masuk
                                </span>

                                <b
                                    class="
                                        mt-1
                                        block
                                        text-sm
                                        text-slate-800
                                    "
                                >
                                    {{
                                        attendance.check_in_time
                                        || '-'
                                    }}
                                </b>

                            </div>



                            <div>

                                <span
                                    class="
                                        block
                                        text-xs
                                        font-semibold
                                        text-slate-400
                                    "
                                >
                                    Pulang
                                </span>

                                <b
                                    class="
                                        mt-1
                                        block
                                        text-sm
                                        text-slate-800
                                    "
                                >
                                    {{
                                        attendance.check_out_time
                                        || '-'
                                    }}
                                </b>

                            </div>



                            <div>

                                <span
                                    class="
                                        block
                                        text-xs
                                        font-semibold
                                        text-slate-400
                                    "
                                >
                                    Durasi
                                </span>

                                <b
                                    class="
                                        mt-1
                                        block
                                        text-sm
                                        text-slate-800
                                    "
                                >
                                    {{
                                        attendance.work_duration_text
                                        || '-'
                                    }}
                                </b>

                            </div>



                            <div>

                                <span
                                    class="
                                        block
                                        text-xs
                                        font-semibold
                                        text-slate-400
                                    "
                                >
                                    Terlambat
                                </span>

                                <b
                                    class="
                                        mt-1
                                        block
                                        text-sm
                                        text-slate-800
                                    "
                                >
                                    {{
                                        attendance.late_text
                                        || '-'
                                    }}
                                </b>

                            </div>

                        </div>



                        <!-- LOCATION LINKS MOBILE -->

                        <div
                            v-if="
                                attendance.latitude_in
                                || attendance.latitude_out
                            "
                            class="
                                mt-4
                                flex
                                gap-2
                                border-t
                                border-slate-100
                                pt-3
                            "
                        >

                            <button
                                v-if="
                                    attendance.latitude_in
                                    && attendance.longitude_in
                                "
                                type="button"
                                class="
                                    flex-1
                                    rounded-xl
                                    bg-emerald-50
                                    px-3
                                    py-2
                                    text-xs
                                    font-bold
                                    text-emerald-700
                                "
                                @click="
                                    openLocation(
                                        attendance.latitude_in,

                                        attendance.longitude_in
                                    )
                                "
                            >
                                Lokasi Masuk
                            </button>



                            <button
                                v-if="
                                    attendance.latitude_out
                                    && attendance.longitude_out
                                "
                                type="button"
                                class="
                                    flex-1
                                    rounded-xl
                                    bg-blue-50
                                    px-3
                                    py-2
                                    text-xs
                                    font-bold
                                    text-blue-700
                                "
                                @click="
                                    openLocation(
                                        attendance.latitude_out,

                                        attendance.longitude_out
                                    )
                                "
                            >
                                Lokasi Pulang
                            </button>

                        </div>

                    </article>



                    <div
                        v-if="
                            attendances.data.length
                            === 0
                        "
                        class="
                            rounded-2xl
                            border
                            border-dashed
                            border-slate-200
                            bg-white
                            p-10
                            text-center
                        "
                    >

                        <div
                            class="
                                mx-auto
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-2xl
                                bg-slate-100
                                text-slate-400
                            "
                        >

                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14H4V6a1 1 0 011-1z"
                                />
                            </svg>

                        </div>


                        <p
                            class="
                                mt-3
                                text-sm
                                font-semibold
                                text-slate-500
                            "
                        >
                            Belum ada riwayat absensi
                        </p>

                    </div>

                </div>



                <!-- =================================================== -->
                <!-- PAGINATION -->
                <!-- =================================================== -->

                <div class="mt-5">

                    <Pagination
                        :links="
                            attendances.links
                        "
                    />

                </div>

            </Card>

        </div>



        <!-- =========================================================== -->
        <!-- CONFIRM DIALOG -->
        <!-- =========================================================== -->

        <ConfirmDialog
            :show="
                confirmState.show
            "
            :title="
                confirmState.title
            "
            :message="
                confirmState.message
            "
            :confirm-text="
                confirmState.confirmText
            "
            @cancel="
                confirmState.show = false
            "
            @confirm="
                runConfirmed
            "
        />

    </AppShell>
</template>
