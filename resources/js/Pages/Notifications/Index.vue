<script setup>
import { router } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  notifications: { type: Object, required: true },
  unreadCount: { type: Number, default: 0 },
})

function markAsRead(id) {
  router.post(route('notifications.mark-as-read', id), {}, { preserveScroll: true })
}

function markAllAsRead() {
  router.post(route('notifications.mark-all-as-read'), {}, { preserveScroll: true })
}

function deleteNotification(id) {
  router.delete(route('notifications.destroy', id), { preserveScroll: true })
}

function timeAgo(dateStr) {
  const now = new Date()
  const date = new Date(dateStr)
  const diff = Math.floor((now - date) / 1000)
  if (diff < 60) return 'just now'
  if (diff < 3600) return Math.floor(diff / 60) + 'm ago'
  if (diff < 86400) return Math.floor(diff / 3600) + 'h ago'
  if (diff < 2592000) return Math.floor(diff / 86400) + 'd ago'
  return date.toLocaleDateString()
}
</script>

<template>
  <div>
    <nav class="text-caption text-medium-emphasis mb-4">
      <a :href="route('dashboard')" class="text-primary">Dashboard</a>
      <span class="mx-1">/</span>
      <span class="font-weight-bold">Notifications</span>
    </nav>

    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Notifications</h4>
        <p class="text-body-2 text-medium-emphasis">{{ unreadCount }} unread</p>
      </div>
      <VBtn v-if="unreadCount > 0" variant="tonal" color="secondary" size="small" @click="markAllAsRead">Mark All as Read</VBtn>
    </div>

    <div class="d-flex flex-column ga-2">
      <VCard v-for="n in notifications.data" :key="n.id" flat border :class="n.read_at ? '' : 'border-l-thick border-primary'" hover @click="markAsRead(n.id)">
        <VCardText>
          <div class="d-flex align-start justify-space-between ga-4">
            <div class="flex-1 min-w-0">
              <div v-if="n.data?.message" class="text-body-2" v-html="n.data.message"></div>
              <p class="text-caption text-medium-emphasis mt-1">{{ timeAgo(n.created_at) }}</p>
            </div>
            <div class="d-flex align-center ga-2 flex-shrink-0">
              <VBtn v-if="n.data?.action_url" variant="text" color="primary" size="small" :href="n.data.action_url">View</VBtn>
              <VBtn v-if="!n.read_at" variant="text" color="secondary" size="small" @click.stop="markAsRead(n.id)">Mark read</VBtn>
              <VBtn icon="mdi-delete-outline" variant="text" color="error" size="small" aria-label="Delete notification" @click.stop="deleteNotification(n.id)" />
            </div>
          </div>
        </VCardText>
      </VCard>

      <VCard v-if="!notifications.data?.length" flat border class="pa-12 text-center">
        <VIcon icon="mdi-bell-off-outline" size="48" color="disabled" />
        <h5 class="text-h5 mt-4 mb-2">No notifications</h5>
        <p class="text-body-2 text-medium-emphasis">You're all caught up!</p>
      </VCard>
    </div>

    <div v-if="notifications.links" class="mt-6 d-flex justify-center">
      <VPagination v-model="notifications.current_page" :length="notifications.last_page" @update:model-value="router.get(route('notifications.index', { page: $event }), {}, { preserveScroll: true })" />
    </div>
  </div>
</template>

<style scoped>
.border-l-thick { border-left-width: 4px !important; }
.min-w-0 { min-width: 0; }
</style>
