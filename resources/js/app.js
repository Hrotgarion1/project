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

// Configurar axios
axios.defaults.withCredentials = true;

// Registrar ChartDataLabels
Chart.register(ChartDataLabels);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .use(i18nVue, {
                lang: props.initialPage.props.locale || 'en',
                fallback: 'en',
                resolve: lang => {
                    const langs = import.meta.glob('../../lang/*.json', { eager: true });
                    return langs[`../../lang/${lang}.json`]?.default || {};
                },
            })
            .component('Footer', Footer)
            .component('draggable', draggable);

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
