<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  assignment: { type: Object, required: true },
  submission: { type: Object, required: true },
})

const form = useForm({
  grade: '',
  grader_notes: '',
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Grade Submission</h4>
    <p class="text-body-1 text-medium-emphasis mb-2">{{ assignment.title }}</p>
    <p class="text-body-2 text-medium-emphasis mb-6">Student: {{ submission.user?.name }} &mdash; Submitted: {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '-' }}</p>

    <VRow>
      <VCol cols="12" lg="6">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-3">Submission Details</h5>
          <div class="d-flex flex-column ga-3 text-body-2">
            <div><span class="text-medium-emphasis">Student:</span> <span class="font-weight-medium">{{ submission.user?.name }}</span></div>
            <div><span class="text-medium-emphasis">Email:</span> {{ submission.user?.email }}</div>
            <div><span class="text-medium-emphasis">Submitted:</span> {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '-' }}</div>
            <div v-if="submission.notes"><span class="text-medium-emphasis">Notes:</span> {{ submission.notes }}</div>
            <VBtn variant="tonal" color="primary" size="small" :href="route('instructor.assignments.submissions.download', [course.id, assignment.id, submission.id])" prepend-icon="mdi-download">
              Download Submission
            </VBtn>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" lg="6">
        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-3">Grade</h5>
          <VForm @submit.prevent="form.post(route('instructor.assignments.submissions.grade.store', [course.id, assignment.id, submission.id]))" class="d-flex flex-column ga-4">
            <VTextField v-model="form.grade" label="Grade ({{ assignment.max_score }} max)" variant="outlined" density="default" type="number" :min="0" :max="assignment.max_score" :rules="[v => v !== '' || 'Grade is required']" hide-details="auto" />
            <VTextarea v-model="form.grader_notes" label="Feedback (Optional)" variant="outlined" density="default" rows="4" hide-details="auto" placeholder="Provide feedback to the student..." />
            <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Submit Grade</VBtn>
          </VForm>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
