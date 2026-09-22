<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },

  liveClasses: {
    type: Object,
    default: () => ({
      data: [],
    }),
  },
})

function destroy(lc) {
  if (!confirm('Delete this live class?')) return

  router.delete(
    route('instructor.live-classes.destroy', [
      props.course.id,
      lc.id,
    ]),
    {
      preserveScroll: true,
    }
  )
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">
          Live Classes
        </h4>

        <p class="text-body-1 text-medium-emphasis">
          {{ course.title }}
        </p>
      </div>
      <div class="d-flex align-center ga-2">
        <VBtn
          color="secondary"
          variant="tonal"
          prepend-icon="mdi-arrow-left"
          :href="route('instructor.courses.update', course.id)"
        >
          Back to Course
        </VBtn>
        <VBtn
          color="primary"
          variant="elevated"
          prepend-icon="mdi-plus"
          :href="route('instructor.live-classes.create', course.id)"
        >
          Create Session
        </VBtn>
      </div>
    </div>

    <VRow>
      <VCol
        v-for="lc in liveClasses.data"
        :key="lc.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard flat border hover>
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-2">
              <VChip
                :color="{
                  scheduled: 'info',
                  ongoing: 'success',
                  ended: 'secondary',
                  cancelled: 'error',
                }[lc.status] || 'secondary'"
                size="x-small"
                variant="tonal"
                label
              >
                {{ lc.status }}
              </VChip>

              <span class="text-caption text-medium-emphasis">
                {{ lc.provider?.replace(/_/g, ' ') }}
              </span>
            </div>

            <h6 class="text-h6 font-weight-bold mb-1">
              {{ lc.title }}
            </h6>

            <p class="text-body-2 text-medium-emphasis text-truncate mb-2">
              {{ lc.description }}
            </p>

            <div class="text-caption text-medium-emphasis mb-3">
              {{ lc.start_time
                ? new Date(lc.start_time).toLocaleString()
                : '-' }}
            </div>

            <div class="d-flex ga-2">
  <VBtn
    variant="tonal"
    color="primary"
    size="small"
    class="flex-grow-1"
    :href="route('instructor.live-classes.edit', [course.id, lc.id])"
  >
    Edit
  </VBtn>

  <VBtn
    variant="tonal"
    color="error"
    size="small"
    class="flex-grow-1"
    @click="destroy(lc)"
  >
    Delete
  </VBtn>
</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard
      v-if="!liveClasses.data?.length"
      flat
      border
      class="pa-12 text-center mt-4"
    >
      <VIcon
        icon="mdi-video-outline"
        size="64"
        color="disabled"
      />

      <h5 class="text-h5 mt-4 mb-2">
        No live classes
      </h5>

      <VBtn
        color="primary"
        variant="elevated"
        :href="route('instructor.live-classes.create', course.id)"
      >
        Create Session
      </VBtn>
    </VCard>
  </div>
</template>