import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import Vue3Toastify, {toast} from 'vue3-toastify'
import { createPinia } from 'pinia';
import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

const appName = import.meta.env.VITE_APP_NAME || 'Multi Schools System';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .use(Vue3Toastify, {
                autoClose: 3000,
                position: "top-right",
                theme: "light",
            })
            .component('EasyDataTable', Vue3EasyDataTable)
            .mount(el);
    },
    progress: {
        color: 'rgb(174, 29, 188)',
    },
});

// This will set light / dark mode on page load...
initializeTheme();


