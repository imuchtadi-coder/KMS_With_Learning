<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  assignments: { type: Array, default: () => [] },
})

function destroy(assignment) {
  if (!confirm('Delete this assignment?')) return
  router.delete(route('instructor.assignments.destroy', [props.course.id, assignment.id]), { preserveScroll: true })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Assignments</h4>
        <p class="text-body-1 text-medium-emphasis">{{ course.title }}</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('instructor.assignments.create', course.id)">New Assignment</VBtn>
    </div>

    <VCard v-if="assignments.length" flat border>
      <VDataTable :items="assignments" :headers="[
        { title: 'Title', key: 'title' },
        { title: 'Section', key: 'section?.title' },
        { title: 'Max Score', key: 'max_score' },
        { title: 'Deadline', key: 'deadline' },
        { title: 'Submissions', key: 'id' },
        { title: 'Actions', key: 'actions' },
      ]" disable-sort hide-default-footer class="text-no-wrap">
        <template #item.deadline="{ value }">{{ value ? new Date(value).toLocaleDateString() : '-' }}</template>
        <template #item.id="{ item }">{{ item.submissions?.length || 0 }}</template>
        <template #item.actions="{ item }">
          <div class="d-flex ga-1">
            <VBtn variant="text" color="primary" size="small" :href="route('instructor.assignments.show', [course.id, item.id])">Manage</VBtn>
            <VBtn variant="text" color="primary" size="small" :href="route('instructor.assignments.edit', [course.id, item.id])">Edit</VBtn>
            <VBtn variant="text" color="error" size="small" @click="destroy(item)">Delete</VBtn>
          </div>
        </template>
      </VDataTable>
    </VCard>
    <VCard v-else flat border class="pa-12 text-center">
      <VIcon icon="mdi-file-document-outline" size="64" color="disabled" />
      <h5 class="text-h5 mt-4 mb-2">No assignments yet</h5>
      <VBtn color="primary" variant="elevated" :href="route('instructor.assignments.create', course.id)">Create Assignment</VBtn>
    </VCard>
  </div>
</template>
