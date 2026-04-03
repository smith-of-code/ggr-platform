import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useToast } from '@/composables/useToast';

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

const toast = useToast()

router.on('error', () => {
    toast.error('Произошла ошибка при обработке запроса')
})

router.on('invalid', (event) => {
    event.preventDefault()
    toast.error('Сервер вернул некорректный ответ')
})

router.on('finish', (event) => {
    const page = router.page
    if (page?.props?.flash?.error) {
        toast.error(page.props.flash.error)
    }
    if (page?.props?.errors && Object.keys(page.props.errors).length > 0) {
        toast.error('Пожалуйста, исправьте ошибки в форме')
    }
})

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
