import './bootstrap';

import Alpine from 'alpinejs';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { createApp, h, ref } from 'vue';

window.Alpine = Alpine;

Alpine.start();

const inertiaRoot = document.getElementById('app');

if (inertiaRoot?.dataset.page) {
    const isLoading = ref(false);

    router.on('start', () => {
        isLoading.value = true;
    });

    router.on('finish', () => {
        isLoading.value = false;
    });

    createInertiaApp({
        title: (title) => (title ? `${title} - Kantor Sayur` : 'Kantor Sayur'),
        resolve: (name) => {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
            return pages[`./Pages/${name}.vue`];
        },
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .provide('isLoading', isLoading)
                .mount(el);
        },
        progress: {
            color: '#059669',
            includeCSS: true,
        },
    });
}
