<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },

  quizzes: {
    type: Array,
    required: true,
  },
})

const search = ref('')

/*
|--------------------------------------------------------------------------
| Filter Quiz
|--------------------------------------------------------------------------
*/
const filteredQuizzes = computed(() => {
  const q = search.value.trim().toLowerCase()

  if (!q) {
    return props.quizzes
  }

  return props.quizzes.filter(quiz =>
    quiz.title?.toLowerCase().includes(q)
  )
})

/*
|--------------------------------------------------------------------------
| Quiz Statistics
|--------------------------------------------------------------------------
*/
const getQuestionCount = (quiz) => {
  return quiz.questions?.length || 0
}

const getAttemptCount = (quiz) => {
  return quiz.attempts?.length || 0
}

const getEssayCount = (quiz) => {
  return quiz.questions?.filter(
    question => question.question_type === 'essay'
  ).length || 0
}

const hasEssay = (quiz) => {
  return getEssayCount(quiz) > 0
}

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

/**
 * Kembali ke Instructor My Courses
 */
const goBack = () => {
  router.get(route('instructor.courses.index'))
}

/**
 * Membuka daftar attempt mahasiswa untuk quiz yang dipilih.
 *
 * Route:
 * instructor.quizzes.attempts
 *
 * URL:
 * /instructor/courses/{course}/quizzes/{quiz}/attempts
 */
const openQuiz = (quiz) => {
  router.get(
    route('instructor.quizzes.attempts', {
      course: props.course.id,
      quiz: quiz.id,
    })
  )
}
</script>

<template>
  <div>

    <!-- ========================================================= -->
    <!-- Header -->
    <!-- ========================================================= -->
    <div
      class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4"
    >
      <div>

        <div class="d-flex align-center ga-2 mb-2">

          <VBtn
            icon
            variant="text"
            size="small"
            @click="goBack"
          >
            <VIcon icon="mdi-arrow-left" />
          </VBtn>

          <h4 class="text-h4 font-weight-bold">
            Quiz Grading
          </h4>

        </div>

        <p class="text-body-1 text-medium-emphasis mb-0">
          {{ course.title }}
        </p>

      </div>
    </div>


    <!-- ========================================================= -->
    <!-- Course Information -->
    <!-- ========================================================= -->
    <VCard
      flat
      border
      class="pa-5 mb-6"
    >
      <div class="d-flex align-center ga-4">

        <VAvatar
          color="primary"
          variant="tonal"
          size="48"
        >
          <VIcon
            icon="mdi-school-outline"
            size="26"
          />
        </VAvatar>

        <div>

          <div class="text-h6 font-weight-bold">
            {{ course.title }}
          </div>

          <div class="text-body-2 text-medium-emphasis">
            Manage quiz attempts and grade essay answers
          </div>

        </div>

      </div>
    </VCard>


    <!-- ========================================================= -->
    <!-- Search -->
    <!-- ========================================================= -->
    <VCard
      flat
      border
      class="pa-4 mb-6"
    >
      <VTextField
        v-model="search"
        placeholder="Search quiz..."
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        hide-details
        clearable
      />
    </VCard>


    <!-- ========================================================= -->
    <!-- Quiz List Header -->
    <!-- ========================================================= -->
    <div
      class="d-flex align-center justify-space-between mb-4"
    >

      <div>

        <h5 class="text-h5 font-weight-bold mb-1">
          Quizzes
        </h5>

        <p class="text-body-2 text-medium-emphasis mb-0">
          Select a quiz to view student attempts and grading.
        </p>

      </div>

      <VChip
        color="primary"
        variant="tonal"
        size="small"
      >
        {{ filteredQuizzes.length }} Quiz
      </VChip>

    </div>


    <!-- ========================================================= -->
    <!-- Quiz List -->
    <!-- ========================================================= -->
    <VRow>

      <VCol
        v-for="quiz in filteredQuizzes"
        :key="quiz.id"
        cols="12"
        md="6"
        lg="4"
      >

        <VCard
          flat
          border
          hover
          class="h-100 d-flex flex-column"
        >

          <!-- ================================================= -->
          <!-- Quiz Content -->
          <!-- ================================================= -->
          <VCardText class="flex-grow-1">

            <!-- Quiz Header -->
            <div
              class="d-flex align-start justify-space-between ga-3 mb-4"
            >

              <VAvatar
                color="primary"
                variant="tonal"
                rounded
                size="44"
              >
                <VIcon
                  icon="mdi-clipboard-text-outline"
                />
              </VAvatar>

              <VChip
                :color="quiz.published ? 'success' : 'warning'"
                variant="tonal"
                size="small"
                label
              >
                {{ quiz.published ? 'Published' : 'Draft' }}
              </VChip>

            </div>


            <!-- Quiz Title -->
            <h6 class="text-h6 font-weight-bold mb-2">
              {{ quiz.title }}
            </h6>


            <!-- Quiz Description -->
            <p
              v-if="quiz.description"
              class="text-body-2 text-medium-emphasis text-truncate mb-4"
            >
              {{ quiz.description }}
            </p>


            <!-- ================================================= -->
            <!-- Statistics -->
            <!-- ================================================= -->
            <div class="d-flex flex-column ga-2">

              <!-- Questions -->
              <div
                class="d-flex justify-space-between"
              >

                <span class="text-body-2 text-medium-emphasis">
                  Questions
                </span>

                <span class="font-weight-semibold">
                  {{ getQuestionCount(quiz) }}
                </span>

              </div>


              <!-- Students -->
              <div
                class="d-flex justify-space-between"
              >

                <span class="text-body-2 text-medium-emphasis">
                  Students
                </span>

                <span class="font-weight-semibold">
                  {{ getAttemptCount(quiz) }}
                </span>

              </div>


              <!-- Essay Questions -->
              <div
                class="d-flex justify-space-between"
              >

                <span class="text-body-2 text-medium-emphasis">
                  Essay Questions
                </span>

                <span class="font-weight-semibold">
                  {{ getEssayCount(quiz) }}
                </span>

              </div>

            </div>


            <!-- ================================================= -->
            <!-- Essay Indicator -->
            <!-- ================================================= -->

            <VAlert
              v-if="hasEssay(quiz)"
              type="info"
              variant="tonal"
              density="compact"
              class="mt-4"
              icon="mdi-pencil-outline"
            >
              This quiz contains essay questions that require
              manual grading.
            </VAlert>


            <!-- Automatic Grading Indicator -->
            <VAlert
              v-else
              type="success"
              variant="tonal"
              density="compact"
              class="mt-4"
              icon="mdi-check-circle-outline"
            >
              No essay questions. Grading is automatic.
            </VAlert>

          </VCardText>


          <!-- ================================================= -->
          <!-- Action -->
          <!-- ================================================= -->
          <VCardActions
            class="pa-4 pt-0"
          >

            <VBtn
              block
              color="primary"
              variant="elevated"
              @click="openQuiz(quiz)"
            >

              <VIcon
                icon="mdi-format-list-bulleted"
                class="mr-2"
              />

              View Attempts

            </VBtn>

          </VCardActions>

        </VCard>

      </VCol>

    </VRow>


    <!-- ========================================================= -->
    <!-- Empty State -->
    <!-- ========================================================= -->
    <VCard
      v-if="!filteredQuizzes.length"
      flat
      border
      class="pa-12 text-center mt-4"
    >

      <VIcon
        icon="mdi-clipboard-text-off-outline"
        size="64"
        color="disabled"
      />

      <h5 class="text-h5 mt-4 mb-2">
        No quizzes found
      </h5>

      <p class="text-body-2 text-medium-emphasis mb-0">
        There are no quizzes matching your search.
      </p>

    </VCard>

  </div>
</template>