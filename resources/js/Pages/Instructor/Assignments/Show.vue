<script setup>
defineProps({
  course: { type: Object, required: true },
  assignment: { type: Object, required: true },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ assignment.title }}</h4>
        <p class="text-body-1 text-medium-emphasis">{{ course.title }}</p>
      </div>
      <div class="d-flex ga-2">
        <VBtn variant="outlined" size="small" :href="route('instructor.assignments.edit', [course.id, assignment.id])">Edit</VBtn>
        <VBtn variant="tonal" color="primary" size="small" :href="route('instructor.assignments.index', course.id)">Back</VBtn>
      </div>
    </div>

    <VRow>
      <VCol cols="12" lg="8">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-3">Assignment Info</h5>
          <div class="d-flex flex-column ga-2 text-body-2">
            <div><span class="text-medium-emphasis">Description:</span> {{ assignment.description || '-' }}</div>
            <div><span class="text-medium-emphasis">Instructions:</span></div>
            <div class="bg-white pa-3 rounded" style="white-space: pre-wrap;">{{ assignment.instructions || 'No instructions' }}</div>
            <div class="d-flex justify-space-between mt-2">
              <span><span class="text-medium-emphasis">Max Score:</span> {{ assignment.max_score }}</span>
              <span><span class="text-medium-emphasis">Passing Grade:</span> {{ assignment.passing_grade }}%</span>
              <span><span class="text-medium-emphasis">Deadline:</span> {{ assignment.deadline ? new Date(assignment.deadline).toLocaleString() : '-' }}</span>
            </div>
            <div><span class="text-medium-emphasis">Allowed Files:</span> {{ assignment.file_types || 'Any' }}</div>
            <div><span class="text-medium-emphasis">Max File Size:</span> {{ assignment.max_file_size || 10 }} MB</div>
          </div>
        </VCard>

        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-3">Submissions ({{ assignment.submissions?.length || 0 }})</h5>
          <VDataTable v-if="assignment.submissions?.length" :items="assignment.submissions" :headers="[
            { title: 'Student', key: 'user.name' },
            { title: 'Submitted', key: 'submitted_at' },
            { title: 'Grade', key: 'grade' },
            { title: 'Actions', key: 'actions' },
          ]" disable-sort hide-default-footer class="text-no-wrap">
            <template #item.submitted_at="{ value }">{{ value ? new Date(value).toLocaleString() : '-' }}</template>
            <template #item.grade="{ item }">
              <span v-if="item.grade !== null" :class="item.grade >= assignment.passing_grade ? 'text-success' : 'text-error'" class="font-weight-semibold">
                {{ item.grade }} / {{ assignment.max_score }}
              </span>
              <VChip v-else color="warning" size="x-small" variant="tonal" label>Pending</VChip>
            </template>
            <template #item.actions="{ item }">
              <div class="d-flex ga-1">
                <VBtn variant="text" color="primary" size="x-small" :href="route('instructor.assignments.submissions.grade', [course.id, assignment.id, item.id])">Grade</VBtn>
                <VBtn variant="text" color="secondary" size="x-small" :href="route('instructor.assignments.submissions.download', [course.id, assignment.id, item.id])">Download</VBtn>
              </div>
            </template>
          </VDataTable>
          <p v-else class="text-body-2 text-medium-emphasis text-center">No submissions yet.</p>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
