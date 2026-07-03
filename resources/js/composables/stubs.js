import { ref } from 'vue'

export function useI18n() {
  const locale = ref('en')
  return { locale }
}

export function useAbility() {
  return {
    update: () => {},
    can: () => true,
    rules: [],
  }
}
