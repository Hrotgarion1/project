import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { i18nVue } from 'laravel-vue-i18n';
import Footer from './Components/Footer.vue';
import axios from 'axios';
import draggable from 'vuedraggable';

// 🔔 Toastify
import Vue3Toastify from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

import VueEasyLightbox from 'vue-easy-lightbox';

// Axios
axios.defaults.withCredentials = true;

// Chart.js plugin
Chart.register(ChartDataLabels);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .use(VueEasyLightbox)
            .use(Vue3Toastify, {
                autoClose: 3000,
                position: 'top-right',
                theme: 'auto',
                pauseOnHover: true,
                draggable: true,
            })
            .use(i18nVue, {
                lang: props.initialPage.props.locale || 'en',
                fallback: 'en',
                resolve: (lang) => {
                    const langs = import.meta.glob('../../lang/*.json', { eager: true });
                    return langs[`../../lang/${lang}.json`]?.default || {};
                },
            })
            .component('Footer', Footer)
            .component('draggable', draggable)
            .component('v-select', vSelect);

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Registro del Service Worker para PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('✅ Service Worker registrado con éxito:', registration);
            })
            .catch((error) => {
                console.error('❌ Error al registrar el Service Worker:', error);
            });
    });
}