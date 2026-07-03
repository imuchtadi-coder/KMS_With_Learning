<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  quiz: { type: Object, required: true },
  attempt: { type: Object, required: true },
  essayAnswers: { type: Array, default: () => [] },
  autoGraded: { type: Boolean, default: false },
})

const scoreForm = useForm({
  scores: Object.fromEntries(props.essayAnswers.map(a => [a.id, a.score || ''])),
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Essay Review</h4>
    <p class="text-body-1 text-medium-emphasis mb-2">{{ quiz.title }}</p>
    <p class="text-body-2 text-medium-emphasis mb-6">Student: {{ attempt.user?.name }} &mdash; Attempt #{{ attempt.attempt_number }}</p>

    <VCard flat border class="pa-6 mb-6">
      <h5 class="text-h5 font-weight-bold mb-4">Essay Answers</h5>
      <div v-if="essayAnswers.length" class="d-flex flex-column ga-4">
        <div v-for="answer in essayAnswers" :key="answer.id" class="pa-4 bg-light-info rounded">
          <div class="font-weight-medium mb-2">Question: {{ answer.question?.question_text }}</div>
          <div class="text-body-2 bg-white pa-3 rounded border mb-3" style="white-space: pre-wrap;">{{ answer.text_answer }}</div>
          <div class="d-flex align-center ga-3">
            <VTextField v-model="scoreForm.scores[answer.id]" label="Score ({{ answer.question?.points || 0 }} pts max)" variant="outlined" density="default" type="number" :min="0" :max="answer.question?.points || 0" hide-details style="max-width: 240px;" />
          </div>
        </div>
      </div>
      <p v-else class="text-body-2 text-medium-emphasis">No essay answers to review.</p>
    </VCard>

    <div v-if="essayAnswers.length" class="d-flex ga-3">
      <VBtn color="primary" variant="elevated" :loading="scoreForm.processing"
        @click="scoreForm.post(route('instructor.quizzes.essay-grade', [course.id, quiz.id, attempt.id]), { preserveScroll: true })">
        Submit Grades
      </VBtn>
      <VBtn variant="outlined" :href="route('instructor.quizzes.show', [course.id, quiz.id])">Cancel</VBtn>
    </div>
  </div>
</template>
