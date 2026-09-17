<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  quiz: {
    type: Object,
    required: true,
  },
  attempts: {
    type: Array,
    default: () => [],
  },
})

const totalQuestions = computed(() => {
  return props.quiz.questions?.length || 0
})

const essayQuestionCount = computed(() => {
  return (props.quiz.questions || []).filter(
    question => question.question_type === 'essay'
  ).length
})

function formatDate(date) {
  if (!date) return '-'

  return new Date(date).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

function getAttemptNumber(attempt) {
  const attempts = props.attempts
    .filter(item => item.user_id === attempt.user_id)
    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))

  return attempts.findIndex(item => item.id === attempt.id) + 1
}

function getEssayAnswers(attempt) {
  return (attempt.answers || []).filter(
    answer => answer.question?.question_type === 'essay'
  )
}

function getAutoGradedAnswers(attempt) {
  return (attempt.answers || []).filter(
    answer => answer.question?.question_type !== 'essay'
  )
}

function getEssayCount(attempt) {
  return getEssayAnswers(attempt).length
}

function getGradedEssayCount(attempt) {
  return getEssayAnswers(attempt).filter(
    answer => answer.points_earned !== null && answer.points_earned !== undefined
  ).length
}

function hasEssay(attempt) {
  return getEssayCount(attempt) > 0
}

function isEssayFullyGraded(attempt) {
  const essays = getEssayAnswers(attempt)

  if (!essays.length) return true

  return essays.every(
    answer => answer.points_earned !== null && answer.points_earned !== undefined
  )
}

function getScore(attempt) {
  if (attempt.score === null || attempt.score === undefined) {
    return '-'
  }

  return Number(attempt.score).toFixed(2)
}

function getScoreColor(attempt) {
  if (attempt.score === null || attempt.score === undefined) {
    return 'default'
  }

  return Number(attempt.score) >= Number(props.quiz.passing_grade || 0)
    ? 'success'
    : 'error'
}

function getStatusText(attempt) {
  if (!hasEssay(attempt)) {
    return 'Auto Graded'
  }

  if (isEssayFullyGraded(attempt)) {
    return 'Graded'
  }

  return 'Needs Grading'
}

function getStatusColor(attempt) {
  if (!hasEssay(attempt)) {
    return 'info'
  }

  if (isEssayFullyGraded(attempt)) {
    return 'success'
  }

  return 'warning'
}
</script>

<template>
  <Head :title="`Quiz Attempts - ${quiz.title}`" />

  <div>
    <!-- Breadcrumb -->
    <nav class="d-flex align-center text-body-2 text-medium-emphasis mb-4 ga-1">
      <a
        class="text-link text-decoration-none"
        :href="route('instructor.courses.quiz-grading', course.id)"
      >
        Quiz Grading
      </a>

      <VIcon
        icon="mdi-chevron-right"
        size="16"
      />

      <span class="text-high-emphasis font-weight-medium">
        {{ quiz.title }}
      </span>
    </nav>

    <!-- Header -->
    <VCard
      flat
      border
      class="mb-6"
    >
      <VCardText>
        <div class="d-flex flex-wrap justify-space-between align-start ga-4">
          <div>
            <div class="d-flex align-center ga-2 mb-2">
              <VIcon
                icon="mdi-clipboard-text-outline"
                color="primary"
                size="28"
              />

              <h4 class="text-h4 font-weight-bold">
                {{ quiz.title }}
              </h4>
            </div>

            <p class="text-body-2 text-medium-emphasis mb-0">
              {{ course.title }}
            </p>
          </div>

          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="mdi-arrow-left"
            :href="route('instructor.courses.quiz-grading', course.id)"
          >
            Back to Quiz Grading
          </VBtn>
        </div>

        <VDivider class="my-5" />

        <!-- Quiz Summary -->
        <VRow>
          <VCol
            cols="12"
            sm="4"
          >
            <div class="d-flex align-center ga-3">
              <VAvatar
                color="primary"
                variant="tonal"
                rounded
              >
                <VIcon icon="mdi-help-circle-outline" />
              </VAvatar>

              <div>
                <div class="text-body-2 text-medium-emphasis">
                  Questions
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ totalQuestions }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <div class="d-flex align-center ga-3">
              <VAvatar
                color="warning"
                variant="tonal"
                rounded
              >
                <VIcon icon="mdi-text-box-edit-outline" />
              </VAvatar>

              <div>
                <div class="text-body-2 text-medium-emphasis">
                  Essay Questions
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ essayQuestionCount }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol
            cols="12"
            sm="4"
          >
            <div class="d-flex align-center ga-3">
              <VAvatar
                color="success"
                variant="tonal"
                rounded
              >
                <VIcon icon="mdi-account-group-outline" />
              </VAvatar>

              <div>
                <div class="text-body-2 text-medium-emphasis">
                  Student Attempts
                </div>
                <div class="text-h6 font-weight-bold">
                  {{ attempts.length }}
                </div>
              </div>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Attempts -->
    <VCard
      flat
      border
    >
      <VCardItem>
        <VCardTitle class="d-flex align-center ga-2">
          <VIcon
            icon="mdi-account-multiple-outline"
            color="primary"
          />

          Student Attempts
        </VCardTitle>

        <VCardSubtitle>
          Review student quiz attempts and manually grade essay answers.
        </VCardSubtitle>
      </VCardItem>

      <VDivider />

      <!-- Empty State -->
      <VCardText v-if="!attempts.length">
        <VAlert
          type="info"
          variant="tonal"
          icon="mdi-information-outline"
        >
          No student attempts have been submitted for this quiz yet.
        </VAlert>
      </VCardText>

      <!-- Attempt List -->
      <VCardText v-else>
        <VTable class="text-no-wrap">
          <thead>
            <tr>
              <th class="text-uppercase text-caption font-weight-bold">
                #
              </th>

              <th class="text-uppercase text-caption font-weight-bold">
                Student
              </th>

              <th class="text-uppercase text-caption font-weight-bold">
                Submitted
              </th>

              <th class="text-uppercase text-caption font-weight-bold text-center">
                Essay
              </th>

              <th class="text-uppercase text-caption font-weight-bold text-center">
                Score
              </th>

              <th class="text-uppercase text-caption font-weight-bold text-center">
                Status
              </th>

              <th class="text-uppercase text-caption font-weight-bold text-end">
                Action
              </th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="attempt in attempts"
              :key="attempt.id"
            >
              <!-- Attempt Number -->
              <td>
                <VChip
                  size="small"
                  variant="tonal"
                  color="secondary"
                >
                  Attempt {{ getAttemptNumber(attempt) }}
                </VChip>
              </td>

              <!-- Student -->
              <td>
                <div class="d-flex align-center ga-3">
                  <VAvatar
                    size="36"
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon icon="mdi-account" />
                  </VAvatar>

                  <div>
                    <div class="font-weight-medium">
                      {{ attempt.user?.name || 'Unknown Student' }}
                    </div>

                    <div
                      v-if="attempt.user?.email"
                      class="text-caption text-medium-emphasis"
                    >
                      {{ attempt.user.email }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Submitted -->
              <td>
                <span class="text-body-2">
                  {{ formatDate(attempt.completed_at || attempt.created_at) }}
                </span>
              </td>

              <!-- Essay -->
              <td class="text-center">
                <div class="d-flex flex-column align-center">
                  <span class="font-weight-medium">
                    {{ getGradedEssayCount(attempt) }}/{{ getEssayCount(attempt) }}
                  </span>

                  <span class="text-caption text-medium-emphasis">
                    graded
                  </span>
                </div>
              </td>

              <!-- Score -->
              <td class="text-center">
                <VChip
                  size="small"
                  :color="getScoreColor(attempt)"
                  variant="tonal"
                >
                  {{ getScore(attempt) }}%
                </VChip>
              </td>

              <!-- Status -->
              <td class="text-center">
                <VChip
                  size="small"
                  :color="getStatusColor(attempt)"
                  variant="tonal"
                >
                  <VIcon
                    start
                    :icon="
                      !hasEssay(attempt)
                        ? 'mdi-robot-outline'
                        : isEssayFullyGraded(attempt)
                          ? 'mdi-check-circle-outline'
                          : 'mdi-clock-alert-outline'
                    "
                    size="16"
                  />

                  {{ getStatusText(attempt) }}
                </VChip>
              </td>

              <!-- Action -->
              <td class="text-end">
                <VBtn
                  v-if="hasEssay(attempt)"
                  size="small"
                  color="primary"
                  variant="tonal"
                  prepend-icon="mdi-file-document-edit-outline"
                  :href="
                    route(
                      'instructor.quizzes.essay-review',
                      [course.id, quiz.id, attempt.id]
                    )
                  "
                >
                  {{ isEssayFullyGraded(attempt) ? 'Review' : 'Grade Essay' }}
                </VBtn>

                <VChip
                  v-else
                  size="small"
                  color="info"
                  variant="tonal"
                >
                  <VIcon
                    start
                    icon="mdi-check-circle-outline"
                    size="16"
                  />
                  Auto Graded
                </VChip>
              </td>
            </tr>
          </tbody>
        </VTable>
      </VCardText>
    </VCard>
  </div>
</template>
