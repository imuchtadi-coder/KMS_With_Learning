<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  quiz: {
    type: Object,
    required: true,
  },
  attempt: {
    type: Object,
    required: true,
  },
  essayAnswers: {
    type: Array,
    default: () => [],
  },
  autoGraded: {
    type: Boolean,
    default: false,
  },
})

const scoreForm = useForm({
  answers: props.essayAnswers.map(answer => ({
    id: answer.id,
    points_earned: answer.points_earned ?? '',
    grader_notes: answer.grader_notes ?? '',
  })),
})

const submitGrades = () => {
  scoreForm.post(
    route(
      'instructor.quizzes.essay-grade',
      [props.course.id, props.quiz.id, props.attempt.id]
    ),
    {
      preserveScroll: true,
    }
  )
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">
      Essay Review
    </h4>

    <p class="text-body-1 text-medium-emphasis mb-2">
      {{ quiz.title }}
    </p>

    <p class="text-body-2 text-medium-emphasis mb-6">
      Student: {{ attempt.user?.name }}
      &mdash;
      Attempt #{{ attempt.attempt_number }}
    </p>

    <VCard
      flat
      border
      class="pa-6 mb-6"
    >
      <h5 class="text-h5 font-weight-bold mb-4">
        Essay Answers
      </h5>

      <div
        v-if="essayAnswers.length"
        class="d-flex flex-column ga-4"
      >
        <div
          v-for="(answer, index) in essayAnswers"
          :key="answer.id"
          class="pa-4 bg-light-info rounded"
        >
          <!-- Question -->
          <div class="font-weight-medium mb-2">
            Question {{ index + 1 }}:
            {{ answer.question?.question_text }}
          </div>

          <!-- Student Answer -->
          <div
            class="text-body-2 bg-white pa-3 rounded border mb-4 text-h5 font-weight-bold mb-4"
            style="white-space: pre-wrap;"
          >
            {{ answer.answer_text }}
          </div>

          <!-- Score -->
          <VTextField
            v-model="scoreForm.answers[index].points_earned"
            :label="`Score (${answer.question?.points || 0} pts max)`"
            variant="outlined"
            density="default"
            type="number"
            min="0"
            :max="answer.question?.points || 0"
            :error-messages="scoreForm.errors[`answers.${index}.points_earned`]"
            class="mb-3"
            style="max-width: 300px;"
          />

          <!-- Grader Notes -->
          <VTextarea
            v-model="scoreForm.answers[index].grader_notes"
            label="Grader Notes"
            variant="outlined"
            density="default"
            rows="3"
            auto-grow
            :error-messages="scoreForm.errors[`answers.${index}.grader_notes`]"
            placeholder="Tambahkan catatan atau feedback untuk jawaban siswa..."
          />
        </div>
      </div>

      <p
        v-else
        class="text-body-2 text-medium-emphasis"
      >
        No essay answers to review.
      </p>
    </VCard>

    <div
      v-if="essayAnswers.length"
      class="d-flex ga-3"
    >
      <VBtn
        color="primary"
        variant="elevated"
        :loading="scoreForm.processing"
        @click="submitGrades"
      >
        Submit Grades
      </VBtn>

      <VBtn
        variant="outlined"
        :href="route('instructor.quizzes.attempts', [course.id, quiz.id])"
      >
        Cancel
      </VBtn>
    </div>
  </div>
</template>