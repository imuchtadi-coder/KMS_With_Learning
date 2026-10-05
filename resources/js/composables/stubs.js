import { locale } from './language'

export function useI18n() {
  return { locale }
}

export function useAbility() {
  return {
    update: () => {},
    can: () => true,
    rules: [],
  }
}
