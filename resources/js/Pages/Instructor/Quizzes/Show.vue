<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  quiz: { type: Object, required: true },
})

function publish() {
  router.post(route('instructor.quizzes.publish', [props.quiz.course_id, props.quiz.id]), {}, { preserveScroll: true })
}
function unpublish() {
  router.post(route('instructor.quizzes.unpublish', [props.quiz.course_id, props.quiz.id]), {}, { preserveScroll: true })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ quiz.title }}</h4>
        <p class="text-body-1 text-medium-emphasis">{{ course.title }}</p>
      </div>
      <div class="d-flex ga-2">
        <VBtn v-if="!quiz.published" color="success" variant="tonal" size="small" @click="publish">Publish</VBtn>
        <VBtn v-else color="warning" variant="tonal" size="small" @click="unpublish">Unpublish</VBtn>
        <VBtn variant="outlined" size="small" :href="route('instructor.quizzes.edit', [course.id, quiz.id])">Edit</VBtn>
        <VBtn
          color="secondary"
          variant="tonal"
          size="small"
          prepend-icon="mdi-arrow-left"
          :href="route('instructor.quizzes.index', course.id)"
        >
          Back
        </VBtn>
      </div>
    </div>

    <VRow>
      <VCol cols="12" lg="8">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-3">Quiz Info</h5>
          <div class="d-flex flex-column ga-2 text-body-2 mb-4">
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Description:</span><span>{{ quiz.description || '-' }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Section:</span><span>{{ quiz.section?.title || '-' }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Passing Grade:</span><span>{{ quiz.passing_grade }}%</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Time Limit:</span><span>{{ quiz.time_limit ? quiz.time_limit + ' min' : 'No limit' }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Max Attempts:</span><span>{{ quiz.max_attempts }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Status:</span><VChip :color="quiz.published ? 'success' : 'warning'" size="x-small" variant="tonal" label>{{ quiz.published ? 'Published' : 'Draft' }}</VChip></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Exam:</span><VChip color="error" v-if="quiz.is_exam" size="x-small" variant="tonal" label>Yes</VChip><span v-else class="text-medium-emphasis">No</span></div>
          </div>
        </VCard>

        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-3">Questions ({{ quiz.questions?.length || 0 }})</h5>
          <div v-if="quiz.questions?.length" class="d-flex flex-column ga-2">
            <div v-for="(q, i) in quiz.questions" :key="q.id" class="d-flex align-center pa-3 bg-light-info rounded">
              <span class="font-weight-bold mr-3 text-caption text-medium-emphasis">{{ i + 1 }}.</span>
              <div class="flex-grow-1">
                <div class="text-body-2 font-weight-medium">{{ q.question_text }}</div>
                <div class="d-flex ga-2 mt-1">
                  <VChip size="x-small" variant="tonal" :color="{ multiple_choice: 'primary', true_false: 'success', essay: 'warning' }[q.question_type] || 'secondary'" label>{{ q.question_type?.replace(/_/g, ' ') }}</VChip>
                  <VChip size="x-small" variant="tonal" label>{{ q.points }} pts</VChip>
                </div>
              </div>
            </div>
          </div>
          <p v-else class="text-body-2 text-medium-emphasis">No questions assigned.</p>
        </VCard>
      </VCol>

      <VCol cols="12" lg="4">
        <VCard flat border class="pa-6 mb-6">
          <h6 class="text-h6 font-weight-semibold mb-3">Attempts</h6>
          <div class="d-flex flex-column ga-2 text-body-2">
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Total:</span><span class="font-weight-semibold">{{ quiz.attempts?.length || 0 }}</span></div>
            <div v-if="quiz.is_exam" class="mt-3">
              <VBtn block variant="tonal" color="primary" size="small" :href="route('instructor.exams.monitor', [course.id, quiz.id])">
                <VIcon start icon="mdi-monitor-eye" /> Monitor Exam
              </VBtn>
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
