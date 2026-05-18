import '../css/app.css';
import './bootstrap';

/**
 * @routes выводит `const Ziggy = {...}` — это не window.Ziggy, а глобальный Ziggy.
 * APP_URL с сервера может быть чужим хостом → правим объект, который реально читает route().
 */
function normalizeZiggyBaseUrl() {
    if (typeof window === 'undefined') {
        return
    }
    const z =
        typeof Ziggy !== 'undefined'
            ? Ziggy
            : typeof globalThis !== 'undefined' && globalThis.Ziggy
              ? globalThis.Ziggy
              : undefined
    if (!z?.url) {
        return
    }
    try {
        const ziggyOrigin = new URL(z.url).origin
        if (ziggyOrigin !== window.location.origin) {
            z.url = `${window.location.origin}/`
        }
    } catch {
        z.url = `${window.location.origin}/`
    }
}
normalizeZiggyBaseUrl()

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useToast } from '@/composables/useToast';
import { formatInvalidInertiaResponse, formatValidationErrors } from '@/utils/inertiaErrors';

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

/** Сеть / сбой загрузки chunk: detail.exception — Error (Inertia v2). */
router.on('exception', (event) => {
    const ex = event.detail?.exception
    if (ex instanceof Error && ex.message?.trim() && ex.message.length < 400) {
        toast.error(ex.message.trim())
        return
    }
    toast.error('Не удалось выполнить запрос. Проверьте подключение к сети.')
})

/** Ошибки валидации после Inertia-ответа: detail.errors (не дублировать в finish). */
router.on('error', (event) => {
    const errors = event.detail?.errors
    if (errors && typeof errors === 'object' && Object.keys(errors).length > 0) {
        toast.error(formatValidationErrors(errors))
        return
    }
    toast.error('Произошла ошибка при обработке запроса')
})

/** Non-Inertia ответ: detail.response — { data, headers, status }. */
router.on('invalid', (event) => {
    event.preventDefault()
    toast.error(formatInvalidInertiaResponse(event.detail?.response))
})

/**
 * flash.error — отдельный toast.
 * props.errors обрабатываются в inertia:error (один toast на цикл визита).
 */
router.on('finish', () => {
    const page = router.page
    if (page?.props?.flash?.error) {
        toast.error(page.props.flash.error)
    }
})

/**
 * Yandex.Metrika: учёт SPA-переходов Inertia.
 * Первый mount уже посчитан inline-скриптом в layout (`resources/views/app.blade.php`),
 * поэтому первое срабатывание `navigate` пропускаем.
 * Сам скрипт счётчика подключается только в production (@production), поэтому
 * `window.ym` в dev/staging будет undefined — guard ниже это учитывает.
 * ID счётчика должен совпадать с указанным в layout.
 */
const YM_COUNTER_ID = 109286415
let ymIsInitialNavigate = true
let ymPreviousUrl = typeof window !== 'undefined' ? window.location.href : ''

router.on('navigate', (event) => {
    if (ymIsInitialNavigate) {
        ymIsInitialNavigate = false
        ymPreviousUrl = event.detail?.page?.url ?? ymPreviousUrl
        return
    }
    if (typeof window === 'undefined' || typeof window.ym !== 'function') {
        return
    }
    const url = window.location.href
    window.ym(YM_COUNTER_ID, 'hit', url, {
        referer: ymPreviousUrl,
        title: document.title,
    })
    ymPreviousUrl = url
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
