import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useRoute() {
  const page = usePage()

  return {
    name: computed(() => {
      const comp = page.component || ''
      return comp.replace(/\//g, '-').toLowerCase()
    }),
    meta: {},
    params: {},
    path: page.url || '/',
    query: {},
    hash: '',
    fullPath: page.url || '/',
    matched: [],
    redirectedFrom: undefined,
  }
}

export function useRouter() {
  return {
    resolve(to) {
      if (typeof to === 'string') {
        return {
          href: to,
          location: { path: to, query: {} },
          route: { name: to },
          normalizedPath: to,
        }
      }
      return {
        href: to?.path || '/',
        location: { path: to?.path || '/', query: {} },
        route: { name: to?.name },
        normalizedPath: to?.path || '/',
      }
    },
    push() {},
    replace() {},
    currentRoute: computed(() => ({ name: '' })),
    options: { history: { state: {} } },
    getRoutes: () => [],
    addRoute: () => {},
    removeRoute: () => {},
    hasRoute: () => false,
    isReady: () => Promise.resolve(),
    beforeEach: () => () => {},
    afterEach: () => () => {},
    onError: () => () => {},
    back: () => {},
    forward: () => {},
    go: () => {},
  }
}
