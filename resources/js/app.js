import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { ref} from 'vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

export const globalOnlineUsers = ref([])

if (typeof window !== 'undefined' && window.Echo) {
    window.Echo.join('online-users')
        .here((users) => {
            globalOnlineUsers.value = users.map(u => u.id)
        })
        .joining((user) => {
            if (!globalOnlineUsers.value.includes(user.id)) {
                globalOnlineUsers.value.push(user.id)
            }
        })
        .leaving((user) => {
            globalOnlineUsers.value = globalOnlineUsers.value.filter(id => id !== user.id)
        })
}
