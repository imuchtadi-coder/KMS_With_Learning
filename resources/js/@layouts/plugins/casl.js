import { usePage } from '@inertiajs/vue3'

export const can = (action, subject) => {
  if (!action) return true

  try {
    const page = usePage()
    const roles = page?.props?.auth?.user?.roles || []

    if (subject === 'role')
      return roles.includes(action)

    return true
  } catch {
    return true
  }
}

export const canViewNavMenuGroup = item => {
  const hasAnyVisibleChild = item.children.some(i => can(i.action, i.subject))

  if (!(item.action && item.subject))
    return hasAnyVisibleChild

  return can(item.action, item.subject) && hasAnyVisibleChild
}

export const canNavigate = to => true
