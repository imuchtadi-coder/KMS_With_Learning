<script setup>
import { usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Notifications from '@core/components/Notifications.vue'

const notificationsData = computed(() => usePage().props.notifications)
const notifications = computed(() => notificationsData.value?.items ?? [])
const unreadCount = computed(() => notificationsData.value?.unreadCount ?? 0)

const removeNotification = () => {
  // No remove endpoint — no-op
}

const markRead = notificationId => {
  notificationId.forEach(id => {
    router.post(route('notifications.mark-as-read', id))
  })
}

const markUnRead = () => {
  // No unread endpoint — no-op
}

const handleNotificationClick = notification => {
  if (notification.action_url && notification.action_url !== '#') {
    router.get(notification.action_url)
  }
}

const viewAll = () => {
  router.get(route('notifications.index'))
}
</script>

<template>
  <Notifications
    :notifications="notifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
    @view-all="viewAll"
  />
</template>
