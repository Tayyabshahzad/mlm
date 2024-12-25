import '../css/app.css';
import './bootstrap';
import 'core-js/features/promise/all-settled';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { initializeMetronic, debugMetronic  } from './metronic-init';
import '@babel/polyfill';
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
            
        // Mount the app first
        app.mount(el);
        
        // Initialize Metronic after mounting
        debugMetronic();
        initializeMetronic();
        
        // Re-initialize after Inertia page visits
        document.addEventListener('inertia:navigate', () => {
            setTimeout(() => {
                initializeMetronic();
            }, 0);
        });

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});