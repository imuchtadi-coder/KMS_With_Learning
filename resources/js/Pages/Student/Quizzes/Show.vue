<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  quiz: { type: Object, required: true },
  attemptsCount: { type: Number, default: 0 },
  bestAttempt: { type: Object, default: null },
})

function startQuiz() {
  router.post(route('student.quizzes.start', props.quiz.id), {}, {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <nav class="d-flex align-center text-body-2 text-medium-emphasis mb-6 ga-1">
      <a class="text-link text-decoration-none" :href="route('student.dashboard')">Dashboard</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <a class="text-link text-decoration-none" :href="route('student.enrollments')">My Learning</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <span class="text-high-emphasis font-weight-medium">{{ quiz.title }}</span>
    </nav>

    <VCard flat border class="pa-8 text-center">
      <VAvatar
        color="primary"
        variant="tonal"
        size="64"
        class="mb-6"
      >
        <VIcon icon="mdi-clipboard-list" size="32" />
      </VAvatar>

      <h4 class="text-h4 font-weight-bold mb-4">{{ quiz.title }}</h4>
      <p v-if="quiz.description" class="text-body-1 text-medium-emphasis mb-6" style="max-width: 480px; margin: 0 auto;">
        {{ quiz.description }}
      </p>

      <VRow class="max-w-lg mx-auto mb-8" dense>
        <VCol cols="6" md="3">
          <div class="pa-4 bg-light-info rounded text-center">
            <div class="text-h4 font-weight-bold">{{ quiz.questions_count || quiz.questions?.length || 0 }}</div>
            <div class="text-caption text-medium-emphasis">Questions</div>
          </div>
        </VCol>
        <VCol cols="6" md="3">
          <div class="pa-4 bg-light-info rounded text-center">
            <div class="text-h4 font-weight-bold">{{ quiz.time_limit ? quiz.time_limit + 'm' : '∞' }}</div>
            <div class="text-caption text-medium-emphasis">Time Limit</div>
          </div>
        </VCol>
        <VCol cols="6" md="3">
          <div class="pa-4 bg-light-info rounded text-center">
            <div class="text-h4 font-weight-bold">{{ quiz.passing_grade }}%</div>
            <div class="text-caption text-medium-emphasis">Passing Grade</div>
          </div>
        </VCol>
        <VCol cols="6" md="3">
          <div class="pa-4 bg-light-info rounded text-center">
            <div class="text-h4 font-weight-bold">{{ quiz.max_attempts }}</div>
            <div class="text-caption text-medium-emphasis">Attempts</div>
          </div>
        </VCol>
      </VRow>

      <VAlert
        v-if="bestAttempt"
        color="success"
        variant="tonal"
        class="mb-6 d-inline-flex"
        density="compact"
      >
        Best Score: <strong>{{ bestAttempt.score }}%</strong>
        ({{ quiz.max_attempts - attemptsCount }} attempt{{ quiz.max_attempts - attemptsCount !== 1 ? 's' : '' }} remaining)
      </VAlert>

      <div v-if="attemptsCount < quiz.max_attempts">
        <VBtn
          color="primary"
          size="large"
          variant="elevated"
          @click="startQuiz"
        >
          {{ attemptsCount === 0 ? 'Start Quiz' : `Retake Quiz (Attempt ${attemptsCount + 1})` }}
        </VBtn>
      </div>
      <VAlert
        v-else
        color="warning"
        variant="tonal"
        density="compact"
        class="d-inline-flex"
      >
        You have used all {{ quiz.max_attempts }} attempts.
      </VAlert>
    </VCard>
  </div>
</template>
