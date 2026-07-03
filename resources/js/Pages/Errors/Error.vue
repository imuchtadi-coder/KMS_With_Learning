<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: Number, default: 404 },
})

const messages = {
  403: { title: 'Forbidden', subtitle: 'You do not have permission to access this page.' },
  404: { title: 'Page Not Found', subtitle: 'The page you are looking for does not exist.' },
  419: { title: 'Session Expired', subtitle: 'Your session has expired. Please log in again.' },
  429: { title: 'Too Many Requests', subtitle: 'You have made too many requests. Please try again later.' },
  500: { title: 'Server Error', subtitle: 'An unexpected error occurred. Please try again later.' },
}

const msg = computed(() => messages[props.status] || messages[500])
const icon = computed(() => {
  if (props.status === 403) return 'mdi-shield-off-outline'
  if (props.status === 404) return 'mdi-file-find-outline'
  if (props.status === 419) return 'mdi-timer-off-outline'
  if (props.status === 429) return 'mdi-speedometer-slow'
  return 'mdi-alert-outline'
})
</script>

<template>
  <div class="d-flex align-center justify-center" style="min-height: 60vh;">
    <div class="text-center">
      <VIcon :icon="icon" size="96" color="disabled" class="mb-4" />
      <h1 class="text-h1 font-weight-bold mb-2">{{ status }}</h1>
      <h4 class="text-h4 text-medium-emphasis mb-2">{{ msg.title }}</h4>
      <p class="text-body-1 text-medium-emphasis mb-6">{{ msg.subtitle }}</p>
      <VBtn color="primary" variant="elevated" :href="route('dashboard')">Back to Dashboard</VBtn>
    </div>
  </div>
</template>
