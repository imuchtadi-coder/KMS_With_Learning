<script setup>
import Shortcuts from '@core/components/Shortcuts.vue'

const shortcuts = [
  {
    icon: 'mdi-view-dashboard-outline',
    title: 'Dashboard',
    subtitle: 'Overview',
    href: route('dashboard'),
  },
  {
    icon: 'mdi-bell-outline',
    title: 'Notifications',
    subtitle: 'View All',
    href: route('notifications.index'),
  },
  {
    icon: 'mdi-account-cog-outline',
    title: 'Settings',
    subtitle: 'Profile Settings',
    href: route('profile.edit'),
  },
]
</script>

<template>
  <Shortcuts :shortcuts="shortcuts" />
</template>
