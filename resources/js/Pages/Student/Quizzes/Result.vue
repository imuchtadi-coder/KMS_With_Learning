<script setup>
import { computed } from 'vue'

const props = defineProps({
  attempt: { type: Object, required: true },
})

const passed = computed(() => {
  if (props.attempt.status === 'timeout') return false
  return props.attempt.score >= props.attempt.quiz.passing_grade
})

const showReview = computed(() => {
  return props.attempt.quiz.show_result && props.attempt.answers?.length > 0
})

const statusLabel = computed(() => {
  if (props.attempt.status === 'timeout') return 'Timeout'
  if (props.attempt.status === 'completed') return 'Completed'
  return props.attempt.status
})

function statusIcon() {
  if (props.attempt.status === 'timeout') return 'mdi-clock-alert-outline'
  if (passed.value) return 'mdi-check-circle-outline'
  return 'mdi-close-circle-outline'
}

function statusColor() {
  if (props.attempt.status === 'timeout') return 'warning'
  if (passed.value) return 'success'
  return 'error'
}

function statusTitle() {
  if (props.attempt.status === 'timeout') return "Time's Up!"
  if (passed.value) return 'Congratulations! You Passed!'
  return 'Unfortunately, You Did Not Pass'
}
</script>

<template>
  <div>
    <nav class="d-flex align-center text-body-2 text-medium-emphasis mb-6 ga-1">
      <a class="text-link text-decoration-none" :href="route('student.dashboard')">Dashboard</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <a class="text-link text-decoration-none" :href="route('student.enrollments')">My Learning</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <span class="text-high-emphasis font-weight-medium">Quiz Result</span>
    </nav>

    <VCard flat border class="pa-8 text-center mb-6">
      <VAvatar
        :color="statusColor"
        variant="tonal"
        size="80"
        class="mb-6"
      >
        <VIcon :icon="statusIcon" size="40" />
      </VAvatar>

      <h4 class="text-h4 font-weight-bold mb-2">{{ statusTitle }}</h4>
      <p class="text-body-1 text-medium-emphasis mb-6">{{ attempt.quiz.title }}</p>

      <VRow class="max-w-md mx-auto mb-6" dense>
        <VCol cols="4">
          <div class="pa-4 bg-light-info rounded text-center">
            <div
              class="text-h3 font-weight-bold"
              :class="passed ? 'text-success' : 'text-error'"
            >
              {{ attempt.score }}%
            </div>
            <div class="text-caption text-medium-emphasis">Your Score</div>
          </div>
        </VCol>
        <VCol cols="4">
          <div class="pa-4 bg-light-info rounded text-center">
            <div class="text-h3 font-weight-bold">{{ attempt.quiz.passing_grade }}%</div>
            <div class="text-caption text-medium-emphasis">Passing Grade</div>
          </div>
        </VCol>
        <VCol cols="4">
          <div class="pa-4 bg-light-info rounded text-center">
            <div class="text-h3 font-weight-bold">{{ statusLabel }}</div>
            <div class="text-caption text-medium-emphasis">Status</div>
          </div>
        </VCol>
      </VRow>

      <VBtn
        color="primary"
        variant="elevated"
        :href="route('student.enrollments')"
      >
        Back to My Learning
      </VBtn>
    </VCard>

    <!-- Answer Review -->
    <div v-if="showReview" class="d-flex flex-column ga-4 mb-8">
      <h5 class="text-h5 font-weight-bold">Answer Review</h5>
      <VCard
        v-for="(answer, index) in attempt.answers"
        :key="answer.id"
        flat
        border
        class="pa-6"
      >
        <div class="d-flex align-start justify-space-between mb-3">
          <h6 class="text-h6">Question {{ index + 1 }}</h6>
          <VChip
            v-if="answer.question.question_type !== 'essay'"
            size="small"
            :color="answer.is_correct ? 'success' : 'error'"
            variant="tonal"
            label
          >
            {{ answer.is_correct ? 'Correct' : 'Incorrect' }}
          </VChip>
          <VChip
            v-else
            size="small"
            color="warning"
            variant="tonal"
            label
          >
            Manual Review
          </VChip>
        </div>

        <p class="text-body-1 mb-4">{{ answer.question.question_text }}</p>

        <div class="text-body-2">
          <p><span class="font-weight-medium">Your answer:</span> {{ answer.answer_text ?? '(No answer)' }}</p>
          <p
            v-if="answer.question.question_type !== 'essay' && answer.question.correct_answer !== null"
            class="mt-1"
          >
            <span class="font-weight-medium">Correct answer:</span> {{ answer.question.correct_answer }}
          </p>
        </div>
      </VCard>
    </div>
  </div>
</template>
