<script setup>
defineProps({
  assignment: { type: Object, required: true },
  submission: { type: Object, default: null },
})
</script>

<template>
  <div>
    <nav class="d-flex align-center text-body-2 text-medium-emphasis mb-6 ga-1">
      <a class="text-link text-decoration-none" :href="route('student.dashboard')">Dashboard</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <a class="text-link text-decoration-none" :href="route('student.enrollments')">My Learning</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <span class="text-high-emphasis font-weight-medium">{{ assignment.title }}</span>
    </nav>

    <VCard flat border class="pa-6 mb-6">
      <h4 class="text-h4 font-weight-bold mb-4">{{ assignment.title }}</h4>
      <div class="d-flex ga-4 flex-wrap text-body-2 text-medium-emphasis mb-4">
        <span>Max Score: {{ assignment.max_score }}</span>
        <span>Passing: {{ assignment.passing_grade }}%</span>
        <span v-if="assignment.deadline" :class="new Date(assignment.deadline) < new Date() ? 'text-error' : ''">
          Deadline: {{ new Date(assignment.deadline).toLocaleString() }}
        </span>
      </div>
      <p v-if="assignment.description" class="text-body-1 mb-4">{{ assignment.description }}</p>
      <div v-if="assignment.instructions" class="pa-4 bg-light-info rounded-lg">
        <div class="text-caption font-weight-bold mb-1">Instructions:</div>
        <p class="text-body-2" style="white-space: pre-wrap;">{{ assignment.instructions }}</p>
      </div>
    </VCard>

    <VCard v-if="submission" flat border class="pa-6">
      <h5 class="text-h5 font-weight-bold mb-4">Your Submission</h5>
      <div class="d-flex flex-column ga-3">
        <div class="d-flex align-center ga-2 text-body-2">
          <span class="text-medium-emphasis">Submitted:</span>
          <span class="font-weight-medium">{{ new Date(submission.submitted_at).toLocaleString() }}</span>
        </div>
        <div v-if="submission.grade !== null" class="d-flex align-center ga-2">
          <span class="text-medium-emphasis text-body-2">Grade:</span>
          <span
            class="text-h6 font-weight-bold"
            :class="submission.grade >= assignment.passing_grade ? 'text-success' : 'text-error'"
          >
            {{ submission.grade }} / {{ assignment.max_score }}
            ({{ assignment.max_score > 0 ? Math.round((submission.grade / assignment.max_score) * 100) : 0 }}%)
          </span>
        </div>
        <div v-else>
          <VChip color="warning" variant="tonal" size="small" label>
            Waiting for Grade
          </VChip>
        </div>
        <div v-if="submission.grader_notes" class="pa-3 bg-light-info rounded-lg">
          <div class="text-caption font-weight-bold mb-1">Instructor Feedback:</div>
          <p class="text-body-2">{{ submission.grader_notes }}</p>
        </div>
      </div>
    </VCard>

    <VCard v-else flat border class="pa-6 text-center">
      <VBtn
        v-if="!assignment.deadline || new Date(assignment.deadline) >= new Date()"
        color="primary"
        variant="elevated"
        :href="route('student.assignments.submit-form', assignment.id)"
      >
        Submit Assignment
      </VBtn>
      <VAlert v-else color="error" variant="tonal" density="compact" class="d-inline-flex">
        The deadline for this assignment has passed.
      </VAlert>
    </VCard>
  </div>
</template>
