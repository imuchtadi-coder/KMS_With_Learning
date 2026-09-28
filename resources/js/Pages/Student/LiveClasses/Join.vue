<script setup>
defineProps({
  liveClass: { type: Object, required: true },
})

const statusColor = (status) => ({
  scheduled: 'info',
  ongoing: 'success',
  ended: 'secondary',
  cancelled: 'error',
}[status] || 'secondary')
</script>

<template>
  <div>
    <VCard flat border class="pa-6 mb-6">
      <div class="d-flex justify-space-between align-start">
        <div>
          <h4 class="text-h4 font-weight-bold mb-2">{{ liveClass.title }}</h4>
          <p class="text-body-1 text-medium-emphasis mb-2">{{ liveClass.course?.title }}</p>
          <div class="d-flex align-center ga-3 text-body-2 text-medium-emphasis">
            <span>{{ new Date(liveClass.start_time).toLocaleString() }}</span>
            <span>&bull;</span>
            <span>{{ liveClass.provider?.replace(/_/g, ' ') }}</span>
            <VChip :color="statusColor(liveClass.status)" size="small" variant="tonal" label>
              {{ liveClass.status }}
            </VChip>
          </div>
        </div>
      </div>
    </VCard>

    <VCard v-if="liveClass.description" flat border class="pa-6 mb-6">
      <h5 class="text-h5 font-weight-bold mb-3">Description</h5>
      <p class="text-body-1">{{ liveClass.description }}</p>
    </VCard>

    <VCard flat border class="pa-6">
      <h5 class="text-h5 font-weight-bold mb-4">Join Meeting</h5>

      <div v-if="liveClass.status === 'ongoing' || liveClass.status === 'scheduled'" class="d-flex flex-column ga-3">
        <VBtn
          v-if="liveClass.meeting_url"
          color="success"
          variant="elevated"
          :href="liveClass.meeting_url"
          target="_blank"
          prepend-icon="mdi-video"
        >
          Join Meeting
        </VBtn>
        <VAlert v-else color="warning" variant="tonal" density="compact">
          No meeting URL provided. Contact your instructor for meeting details.
        </VAlert>
        <div v-if="liveClass.meeting_id" class="text-body-2 text-medium-emphasis">
          <span class="font-weight-semibold">Meeting ID:</span> {{ liveClass.meeting_id }}
        </div>
        <div v-if="liveClass.password" class="text-body-2 text-medium-emphasis">
          <span class="font-weight-semibold">Password:</span> {{ liveClass.password }}
        </div>
      </div>
      <div v-else-if="liveClass.status === 'ended' && liveClass.recording_url">
        <p class="text-body-2 text-medium-emphasis mb-3">This session has ended.</p>
        <VBtn color="primary" variant="elevated" :href="liveClass.recording_url" target="_blank" prepend-icon="mdi-play-circle-outline">
          View Recording
        </VBtn>
      </div>
      <VAlert v-else-if="liveClass.status === 'cancelled'" color="error" variant="tonal" density="compact">
        This session was cancelled.
      </VAlert>
      <p v-else class="text-body-2 text-medium-emphasis">This session has ended. No recording available.</p>
    </VCard>
    <div class ="mt-6">
      <VBtn
        color="secondary"
        variant="tonal"
        prepend-icon="mdi-arrow-left"
        :href="route('student.live-classes.index')"
      >
        Back to Live Classes
      </VBtn>
    </div>
  </div>
</template>
