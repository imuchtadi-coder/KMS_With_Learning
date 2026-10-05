import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createInertiaApp, router as inertiaRouter } from '@inertiajs/vue3'
import { registerPlugins } from '@core/utils/plugins'
import { ZiggyVue } from 'ziggy-js'
import { Ziggy } from './ziggy'
import { installPageTranslations, setLanguageScope } from './composables/language'

// Use current origin so routes work on any port/domain
Ziggy.url = window.location.origin

// Make Ziggy globally available so auto-imported route() can find it
window.Ziggy = Ziggy

// Layouts
import AppLayout from './layouts/default.vue'
import AuthLayout from './layouts/blank.vue'

// Styles
import '@core-scss/template/index.scss'
import '@styles/styles.scss'

// Minimal vue-router for $router/$route/RouterLink support
// Navigation is handled by Inertia, not vue-router
const stubComponent = { render: () => null }
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/:pathMatch(.*)*',
            name: 'catch-all',
            component: stubComponent,
        },
    ],
})

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        const page = pages[`./Pages/${name}.vue`]

        if (!page)
            throw new Error(`Page not found: ${name}`)

        if (name.startsWith('Auth/'))
            page.default.layout = AuthLayout
        else
            page.default.layout = AppLayout

        return page
    },
    setup({ el, App, props, plugin }) {
        setLanguageScope(props.initialPage?.props?.languageScope)
        inertiaRouter.on('navigate', event => {
            const languageScope = event.detail.page.props?.languageScope

            if (languageScope)
                setLanguageScope(languageScope)
        })

        const app = createApp(App, props)
        app.use(plugin)
        app.use(router)
        app.use(ZiggyVue, Ziggy)
        registerPlugins(app)
        app.mount(el)
        installPageTranslations()
    },
})
