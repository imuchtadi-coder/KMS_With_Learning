<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  quiz: { type: Object, required: true },
  sessions: { type: Array, default: () => [] },
})

function forceSubmit(session) {
  if (!confirm(`Force submit attempt #${session.attempt_number}?`)) return
  router.post(route('instructor.exams.force-submit', [props.course.id, props.quiz.id, session.id]), {}, { preserveScroll: true })
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Exam Monitor</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ quiz.title }} &mdash; {{ course.title }}</p>

    <VCard flat border class="pa-6 mb-6">
      <h5 class="text-h5 font-weight-bold mb-3">Exam Summary</h5>
      <div class="d-flex ga-6 text-body-2">
        <div><span class="text-medium-emphasis">Total Sessions:</span> <span class="font-weight-bold">{{ sessions.length }}</span></div>
        <div><span class="text-medium-emphasis">Active:</span> <span class="font-weight-bold text-success">{{ sessions.filter(s => s.status === 'in_progress').length }}</span></div>
        <div><span class="text-medium-emphasis">Completed:</span> <span class="font-weight-bold">{{ sessions.filter(s => s.status === 'completed').length }}</span></div>
        <div><span class="text-medium-emphasis">Timed Out:</span> <span class="font-weight-bold text-error">{{ sessions.filter(s => s.status === 'timeout').length }}</span></div>
      </div>
    </VCard>

    <VCard flat border>
      <VDataTable :items="sessions" :headers="[
        { title: 'Student', key: 'user.name' },
        { title: 'Started', key: 'started_at' },
        { title: 'Status', key: 'status' },
        { title: 'Score', key: 'score' },
        { title: 'Actions', key: 'actions' },
      ]" disable-sort hide-default-footer class="text-no-wrap">
        <template #item.user.name="{ item }">
          <div class="d-flex align-center ga-2">
            <VAvatar size="28" color="primary" variant="tonal">{{ item.user?.name?.[0] }}</VAvatar>
            <span class="font-weight-medium">{{ item.user?.name }}</span>
          </div>
        </template>
        <template #item.started_at="{ value }">{{ value ? new Date(value).toLocaleString() : '-' }}</template>
        <template #item.status="{ value }">
          <VChip :color="{ in_progress: 'success', completed: 'primary', timeout: 'error' }[value] || 'secondary'" size="x-small" variant="tonal" label>{{ value?.replace(/_/g, ' ') }}</VChip>
        </template>
        <template #item.score="{ item }">{{ item.score ?? '-' }} / {{ item.max_score ?? '-' }}</template>
        <template #item.actions="{ item }">
          <VBtn v-if="item.status === 'in_progress'" variant="tonal" color="error" size="x-small" @click="forceSubmit(item)">Force Submit</VBtn>
          <span v-else class="text-disabled text-caption">-</span>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
