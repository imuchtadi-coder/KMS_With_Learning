import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import AutoImport from 'unplugin-auto-import/vite'
import { fileURLToPath } from 'node:url'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({
            styles: {
                configFile: 'resources/styles/variables/_vuetify.scss',
            },
        }),
        AutoImport({
            imports: ['vue', 'pinia', 'vue-router', '@vueuse/core', {
                '@/composables/stubs': ['useI18n', 'useAbility'],
                '@core/composable/useCookie': ['useCookie'],
                'ziggy-js': ['route'],
            }],
            dts: false,
            viteOptimizeDeps: true,
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            '@core': fileURLToPath(new URL('./resources/js/@core', import.meta.url)),
            '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
            '@layouts': fileURLToPath(new URL('./resources/js/@layouts', import.meta.url)),
            '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
            '@images': fileURLToPath(new URL('./resources/images/', import.meta.url)),
            '@themeConfig': fileURLToPath(new URL('./themeConfig.js', import.meta.url)),
            '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                silenceDeprecations: ['color-functions', 'global-builtin', 'import', 'if-function'],
            },
        },
    },
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    vuetify: ['vuetify'],
                    vendor: ['vue', 'pinia', 'vue-router', '@vueuse/core'],
                },
            },
        },
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
})
