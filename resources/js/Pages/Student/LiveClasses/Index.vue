<script setup>
import { computed } from 'vue'

const props = defineProps({
  liveClasses: { type: Object, required: true },
  upcoming: { type: Array, default: () => [] },
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
    <h4 class="text-h4 font-weight-bold mb-6">Live Classes</h4>

    <VCard v-if="upcoming.length" color="info" variant="tonal" class="pa-4 mb-6">
      <h5 class="text-h5 text-info font-weight-bold mb-3">Upcoming Sessions</h5>
      <div class="d-flex flex-column ga-3">
        <VCard v-for="lc in upcoming" :key="lc.id" variant="outlined" class="pa-3">
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="font-weight-semibold">{{ lc.title }}</div>
              <div class="text-body-2 text-medium-emphasis">{{ lc.course?.title }} &bull; {{ new Date(lc.start_time).toLocaleString() }}</div>
            </div>
            <VChip size="small" color="info" variant="tonal" label>
              {{ new Date(lc.start_time).toLocaleDateString() }}
            </VChip>
          </div>
        </VCard>
      </div>
    </VCard>

    <VCard flat border>
      <VDataTable
        :items="liveClasses.data"
        :headers="[
          { title: 'Title', key: 'title', sortable: false },
          { title: 'Course', key: 'course.title', sortable: false },
          { title: 'Provider', key: 'provider', sortable: false },
          { title: 'Start', key: 'start_time', sortable: true },
          { title: 'Status', key: 'status', sortable: false },
          { title: 'Recording', key: 'recording_url', sortable: false },
          { title: 'Action', key: 'actions', sortable: false },
        ]"
        disable-sort
        hide-default-footer
        class="text-no-wrap"
      >
        <template #item.provider="{ value }">
          <VChip size="small" variant="tonal" label>{{ value?.replace(/_/g, ' ') }}</VChip>
        </template>
        <template #item.start_time="{ value }">
          <span class="text-body-2">{{ value ? new Date(value).toLocaleString() : '-' }}</span>
        </template>
        <template #item.status="{ value }">
          <VChip :color="statusColor(value)" size="small" variant="tonal" label>{{ value }}</VChip>
        </template>
        <template #item.recording_url="{ value }">
          <a v-if="value" :href="value" target="_blank" class="text-link text-decoration-none text-caption">View</a>
          <span v-else class="text-disabled">-</span>
        </template>
        <template #item.actions="{ item }">
          <VBtn
            v-if="item.status === 'ongoing' || item.status === 'scheduled'"
            size="small"
            color="primary"
            variant="tonal"
            :href="route('student.live-classes.join', item.id)"
          >
            Join
          </VBtn>
          <span v-else class="text-disabled text-caption">-</span>
        </template>
        <template #bottom>
          <div class="d-flex align-center justify-end pa-4">
            <VPagination
              v-if="liveClasses.last_page > 1"
              v-model="liveClasses.current_page"
              :length="liveClasses.last_page"
              :total-visible="5"
              @update:model-value="(p) => $inertia.get(route('student.live-classes.index'), { page: p })"
            />
          </div>
        </template>
      </VDataTable>
      <VCardText v-if="!liveClasses.data?.length" class="text-center py-8 text-medium-emphasis">
        No live classes available.
      </VCardText>
    </VCard>
  </div>
</template>
