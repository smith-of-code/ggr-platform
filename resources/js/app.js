import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import {
    RButton, RInput, RCheckbox, RAvatar, RBadge, RCard,
    RModal, RTabs, RProgress, RSidebar,
    CourseCard, ProfileCard, Leaderboard, AssignmentCard,
} from '@rosatom-ggr/ui-kit';

const appName = import.meta.env.VITE_APP_NAME || 'Гостеприимные города Росатома';

const uiKitComponents = {
    RButton, RInput, RCheckbox, RAvatar, RBadge, RCard,
    RModal, RTabs, RProgress, RSidebar,
    CourseCard, ProfileCard, Leaderboard, AssignmentCard,
};

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

        Object.entries(uiKitComponents).forEach(([name, component]) => {
            app.component(name, component);
        });

        return app.mount(el);
    },
    progress: {
        color: '#025EA1',
    },
});
