import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import AOS from 'aos'
import 'aos/dist/aos.css'

AOS.init({
  duration: 900,
  easing: 'ease-out',
  once: true,
  offset: 30
})

// Nama aplikasi
const appName = 'Bangka Tengah Super App';

createInertiaApp({
    title: (title) =>
        title
            ? `${title} | ${appName}`
            : appName,

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        return createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },

    progress: {
        color: '#2563EB', 
    },
});

// Auto-hide scrollbar when idle
if (typeof window !== 'undefined') {
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        document.documentElement.classList.add('scrolling');
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            document.documentElement.classList.remove('scrolling');
        }, 1000);
    }, { capture: true, passive: true });
}