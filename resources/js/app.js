import './bootstrap';

import Alpine from 'alpinejs';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import vSelect from 'vue3-select';
import Swal from 'sweetalert2';
import 'vue3-select/dist/vue3-select.css';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
Alpine.start();
window.Swal = Swal;

const LIVE_REFRESH_EVENT = 'app:live-refresh';

window.emitLiveRefresh = (detail = {}) => {
    const payload = {
        at: Date.now(),
        ...detail,
    };

    window.dispatchEvent(new CustomEvent(LIVE_REFRESH_EVENT, { detail: payload }));

    try {
        window.localStorage.setItem('app.live-refresh', JSON.stringify(payload));
    } catch (_) {}
};

function getSwalOptions(extra = {}) {
    const isSmallScreen = window.matchMedia('(max-width: 640px)').matches;

    return {
        width: isSmallScreen ? 'calc(100vw - 1.25rem)' : '24rem',
        padding: isSmallScreen ? '0.9rem' : '1rem',
        ...extra,
    };
}

function showFlashSwal(page) {
    const flash = page?.props?.flash || {};
    const success = flash.success || null;
    const error = flash.error || null;
    const status = flash.status || null;

    if (success) {
        Swal.fire(
            getSwalOptions({
                icon: 'success',
                title: 'Success',
                text: success,
                confirmButtonColor: '#16a34a',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
            })
        );
        return;
    }

    if (error) {
        Swal.fire(
            getSwalOptions({
                icon: 'error',
                title: 'Error',
                text: error,
                confirmButtonColor: '#dc2626',
            })
        );
        return;
    }

    if (status) {
        Swal.fire(
            getSwalOptions({
                icon: 'info',
                title: 'Notice',
                text: status,
                confirmButtonColor: '#16a34a',
            })
        );
    }
}

if (document.getElementById('app')) {
    router.on('success', (event) => {
        showFlashSwal(event?.detail?.page);

        const method = String(event?.detail?.visit?.method || 'get').toLowerCase();
        if (method !== 'get') {
            window.emitLiveRefresh({ source: 'inertia', method });
        }
    });

    createInertiaApp({
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue')
            ),
        setup({ el, App, props, plugin }) {
            showFlashSwal(props?.initialPage);

            createApp({ render: () => h(App, props) })
                .use(plugin)
                .component('v-select', vSelect)
                .mount(el);
        },
        progress: {
            color: '#16a34a',
        },
    });
}
