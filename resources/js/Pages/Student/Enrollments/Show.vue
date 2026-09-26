<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import VideoPlayer from '@/components/learning/VideoPlayer.vue'

const props = defineProps({
  enrollment: {
    type: Object,
    required: true,
  },

  totalLessons: {
    type: Number,
    default: 0,
  },

  completedLessons: {
    type: Number,
    default: 0,
  },

  courseProgress: {
    type: Number,
    default: 0,
  },

  courseQuizzes: {
    type: Array,
    default: () => [],
  },

  courseAssignments: {
    type: Array,
    default: () => [],
  },
})

/*
|--------------------------------------------------------------------------
| Course
|--------------------------------------------------------------------------
*/

const course = computed(() => props.enrollment.course || {})

const sections = computed(() => course.value.sections || [])

const instructor = computed(() => course.value.instructor || {})

/*
|--------------------------------------------------------------------------
| All Lessons
|--------------------------------------------------------------------------
*/

const allLessons = computed(() => {
  return sections.value.flatMap(section => section.lessons || [])
})

/*
|--------------------------------------------------------------------------
| Selected Lesson
|--------------------------------------------------------------------------
*/

const selectedLesson = ref(null)

/*
|--------------------------------------------------------------------------
| Progress Tracker
|--------------------------------------------------------------------------
|
| Digunakan untuk menghindari request progress terlalu sering.
|
*/

let lastSavedWatchedSeconds = 0
let savingProgress = false

/*
|--------------------------------------------------------------------------
| Initialize Selected Lesson
|--------------------------------------------------------------------------
|
| Saat halaman pertama kali dibuka,
| otomatis pilih lesson pertama.
|
*/

watch(
  allLessons,
  lessons => {
    if (!lessons.length) {
      selectedLesson.value = null
      return
    }

    // Jangan reset selected lesson jika masih tersedia
    if (
      selectedLesson.value &&
      lessons.some(
        lesson => lesson.id === selectedLesson.value.id
      )
    ) {
      return
    }

    selectedLesson.value = lessons[0]
  },
  {
    immediate: true,
  }
)

/*
|--------------------------------------------------------------------------
| Selected Video
|--------------------------------------------------------------------------
|
| Ambil video pertama yang status-nya ready.
|
*/

const selectedVideo = computed(() => {
  if (!selectedLesson.value) {
    return null
  }

  return (
    (selectedLesson.value.videos || []).find(
      video => video.status === 'ready'
    ) || null
  )
})

/*
|--------------------------------------------------------------------------
| Selected Lesson Progress
|--------------------------------------------------------------------------
|
| Backend mengirim:
|
| lesson.progresses = [
|   progress milik student
| ]
|
| Karena backend sudah memfilter berdasarkan user_id,
| kita cukup mengambil progress pertama.
|
*/

const selectedProgress = computed(() => {
  if (!selectedLesson.value) {
    return null
  }

  return selectedLesson.value.progresses?.[0] || null
})

/*
|--------------------------------------------------------------------------
| Initial Watched Until
|--------------------------------------------------------------------------
|
| VideoPlayer membutuhkan posisi terakhir yang sudah ditonton
| dalam satuan detik.
|
*/

const progressValue = computed(() => {
  return Number(
    selectedProgress.value?.watched_seconds ?? 0
  )
})

/*
|--------------------------------------------------------------------------
| Current Lesson Index
|--------------------------------------------------------------------------
*/

const currentLessonIndex = computed(() => {
  if (!selectedLesson.value) {
    return -1
  }

  return allLessons.value.findIndex(
    lesson => lesson.id === selectedLesson.value.id
  )
})

/*
|--------------------------------------------------------------------------
| Previous Lesson
|--------------------------------------------------------------------------
*/

const previousLesson = computed(() => {
  const index = currentLessonIndex.value

  if (index <= 0) {
    return null
  }

  return allLessons.value[index - 1]
})

/*
|--------------------------------------------------------------------------
| Next Lesson
|--------------------------------------------------------------------------
*/

const nextLesson = computed(() => {
  const index = currentLessonIndex.value

  if (
    index < 0 ||
    index >= allLessons.value.length - 1
  ) {
    return null
  }

  return allLessons.value[index + 1]
})

/*
|--------------------------------------------------------------------------
| Select Lesson
|--------------------------------------------------------------------------
|
| Digunakan ketika user mengklik lesson di sidebar.
|
| Lesson tidak pindah halaman.
| Video ditampilkan langsung di Enrollment Show.
|
*/

const selectLesson = lesson => {
  if (!lesson) {
    return
  }

  lastSavedWatchedSeconds = 0
  savingProgress = false

  selectedLesson.value = lesson
}

/*
|--------------------------------------------------------------------------
| Save Video Progress
|--------------------------------------------------------------------------
|
| VideoPlayer mengirim payload:
|
| {
|   sourceType,
|   currentTime,
|   duration,
|   watchedUntil
| }
|
*/

const saveProgress = async ({
  currentTime,
  duration,
  watchedUntil,
}) => {
  if (!selectedLesson.value) {
    return
  }

  const watchedSeconds = Math.max(
    Number(watchedUntil || 0),
    Number(currentTime || 0)
  )

  const totalSeconds = Number(duration || 0)

  // Tidak ada data progress yang valid
  if (
    watchedSeconds <= 0 ||
    totalSeconds <= 0
  ) {
    return
  }

  /*
   * Simpan progress jika:
   *
   * - bertambah minimal 5 detik
   * - atau video sudah selesai
   */

  if (
    watchedSeconds - lastSavedWatchedSeconds < 5 &&
    watchedSeconds < totalSeconds
  ) {
    return
  }

  // Jangan kirim request bersamaan
  if (savingProgress) {
    return
  }

  savingProgress = true

  try {
    await axios.post(
      route('student.lessons.update-progress'),
      {
        lesson_id: selectedLesson.value.id,
        watched_seconds: watchedSeconds,
        total_seconds: totalSeconds,
      }
    )

    lastSavedWatchedSeconds = watchedSeconds
  } catch (error) {
    console.error(
      'Failed to save video progress:',
      error
    )
  } finally {
    savingProgress = false
  }
}

/*
|--------------------------------------------------------------------------
| Video Time Update
|--------------------------------------------------------------------------
*/

const onTimeUpdate = payload => {
  saveProgress(payload)
}

/*
|--------------------------------------------------------------------------
| Video Ended
|--------------------------------------------------------------------------
|
| Saat video selesai:
|
| 1. Simpan progress sampai durasi terakhir
| 2. Tandai lesson sebagai completed
|
*/

const onVideoEnded = async payload => {
  if (!selectedLesson.value) {
    return
  }

  /*
   * Simpan progress terakhir.
   */

  await saveProgress({
    ...payload,
    watchedUntil:
      payload?.duration ??
      payload?.watchedUntil,
  })

  /*
   * Tandai lesson sebagai selesai.
   */

  router.post(
    route(
      'student.lessons.complete',
      selectedLesson.value.id
    ),
    {},
    {
      preserveScroll: true,
    }
  )
}

/*
|--------------------------------------------------------------------------
| Previous / Next Lesson Navigation
|--------------------------------------------------------------------------
*/

const goToLesson = lesson => {
  if (!lesson) {
    return
  }

  lastSavedWatchedSeconds = 0
  savingProgress = false

  selectedLesson.value = lesson

  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

/*
|--------------------------------------------------------------------------
| Reset Progress Tracker When Lesson Changes
|--------------------------------------------------------------------------
*/

watch(
  () => selectedLesson.value?.id,
  () => {
    lastSavedWatchedSeconds = 0
    savingProgress = false
  }
)
</script>

<template>
  <VContainer fluid class="bg-grey-lighten-4 min-h-screen py-6">
    <!-- Course Header -->
    <div class="mb-6">
      <h2 class="text-h4 font-weight-bold mb-1">{{ course.title }}</h2>
      <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
        <div class="d-flex align-center ga-4">
          <span class="text-body-2 text-medium-emphasis">by <strong>{{ instructor.name || 'Unknown' }}</strong></span>
          <VChip color="primary" size="small" variant="flat">{{ course.level?.charAt(0).toUpperCase() + course.level?.slice(1) }}</VChip>
        </div>
        <div>
          <VBtn 
            color="secondary" 
            variant="tonal" 
            size="small" 
            prepend-icon="mdi-arrow-left" 
            :href="route('student.enrollments')" 
          >
            Back
          </VBtn>
        </div>
      </div>
    </div>

    <!-- Progress Overview -->
    <VCard flat border class="mb-6 pa-4">
      <VRow>
        <VCol cols="12" md="3">
          <h6 class="text-body-2 font-weight-bold mb-2">Overall Progress</h6>
          <p class="text-h3 font-weight-bold text-primary mb-2">{{ enrollment.progress || 0 }}%</p>
          <VProgressLinear :model-value="enrollment.progress || 0" color="primary" height="8" rounded />
        </VCol>
        <VCol cols="12" md="3">
          <h6 class="text-body-2 font-weight-bold mb-2">Course Stats</h6>
          <div class="d-flex flex-column ga-1 text-body-2">
            <div class="d-flex justify-between"><span class="text-medium-emphasis">Total Lessons:</span><span class="font-weight-bold">{{ totalLessons }}</span></div>
            <div class="d-flex justify-between"><span class="text-medium-emphasis">Completed:</span><span class="font-weight-bold">{{ completedLessons }}</span></div>
          </div>
        </VCol>
        <VCol cols="12" md="3">
          <h6 class="text-body-2 font-weight-bold mb-2">Status</h6>
          <div v-if="enrollment.completed_at" class="d-flex align-center text-success ga-2">
            <VIcon icon="mdi-check-circle" size="20" />
            <span class="font-weight-bold">Completed</span>
            <span class="text-caption text-medium-emphasis">{{ new Date(enrollment.completed_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) }}</span>
          </div>
          <div v-else class="d-flex align-center text-primary ga-2">
            <!-- <VIcon icon="mdi-loading" size="20" class="spin" /> -->
            <span class="font-weight-bold">In Progress</span>
          </div>
        </VCol>
      </VRow>
    </VCard>

    <VRow>
      <!-- Sections & Lessons Sidebar -->
      <VCol cols="12" lg="3">
        <VCard flat border class="sticky-top">
          <VCardTitle>Course Content</VCardTitle>
          <VDivider />
          <div class="overflow-y-auto" style="max-height: 24rem;">
            <template v-for="section in sections" :key="section.id">
              <div class="pa-3 bg-grey-lighten-3 font-weight-bold text-body-2 border-bottom">{{ section.title }}</div>
              <div class="d-flex flex-column">
                <div
                  v-for="lesson in section.lessons || []"
                  :key="lesson.id"
                  class="d-flex align-center ga-2 pa-2 w-100"
                  :class="
                    selectedLesson?.id === lesson.id
                      ? 'bg-primary-lighten-5 border-left-thick border-primary'
                      : ''
                  "
                >
                  <!-- Pilih lesson untuk menampilkan video inline -->
                  <button
                    type="button"
                    class="lesson-item d-flex align-center ga-2 text-body-2 text-start flex-grow-1"
                    :class="
                      selectedLesson?.id === lesson.id
                        ? 'text-primary font-weight-bold'
                        : 'text-medium-emphasis'
                    "
                    @click="selectLesson(lesson)"
                  >
                    <VIcon icon="mdi-play-box-outline" size="16" />

                    <span class="line-clamp-2 flex-grow-1">
                      {{ lesson.title }}
                    </span>

                    <VIcon
                      v-if="lesson.progresses?.[0]?.completed"
                      icon="mdi-check-circle"
                      size="16"
                      color="success"
                    />
                  </button>

                  <!-- Masuk ke halaman Lesson -->
                  <VBtn
                    icon
                    size="small"
                    variant="text"
                    color="primary"
                    :href="
                      route('student.lessons.view', {
                        course: course.id,
                        lesson: lesson.id,
                      })
                    "
                    :title="`Open ${lesson.title}`"
                  >
                    <VIcon icon="mdi-open-in-new" size="18" />
                  </VBtn>
                </div>
              </div>
            </template>
            
          </div>
          <VDivider />
          <!-- Assessments -->
          <VCardText>
            <h6 class="text-body-2 font-weight-bold mb-3">Assessments</h6>

            <template v-if="courseQuizzes.length || courseAssignments.length">

              <!-- Quizzes & Exams -->
              <template v-for="quiz in courseQuizzes" :key="quiz.id">
                <a
                  :href="route('student.quizzes.show', quiz.id)"
                  class="assessment-item d-flex align-start ga-3 pa-3 mb-2 text-decoration-none rounded border"
                  :class="quiz.is_exam ? 'exam-item' : 'quiz-item'"
                >
                  <VIcon
                    :icon="quiz.is_exam
                      ? 'mdi-clipboard-text-clock-outline'
                      : 'mdi-file-document-outline'"
                    size="20"
                    :color="quiz.is_exam ? 'error' : 'primary'"
                    class="mt-1"
                  />

                  <div class="flex-grow-1">
                    <div class="d-flex align-center justify-space-between ga-2">
                      <span class="text-body-2 font-weight-bold">
                        {{ quiz.title }}
                      </span>

                      <VChip
                        :color="quiz.is_exam ? 'error' : 'primary'"
                        size="x-small"
                        variant="tonal"
                      >
                        {{ quiz.is_exam ? 'Exam' : 'Quiz' }}
                      </VChip>
                    </div>

                    <!-- Exam Schedule -->
                    <div
                      v-if="quiz.is_exam"
                      class="d-flex flex-column mt-2 ga-1"
                    >
                      <div class="d-flex align-center text-caption text-medium-emphasis">
                        <VIcon icon="mdi-calendar-start" size="14" class="me-1" />
                        <span class="font-weight-medium me-1">Start:</span>
                        <span>
                          {{
                            quiz.start_date
                              ? new Date(quiz.start_date).toLocaleString('id-ID', {
                                  day: '2-digit',
                                  month: 'short',
                                  year: 'numeric',
                                  hour: '2-digit',
                                  minute: '2-digit'
                                })
                              : '-'
                          }}
                        </span>
                      </div>

                      <div class="d-flex align-center text-caption text-medium-emphasis">
                        <VIcon icon="mdi-calendar-end" size="14" class="me-1" />
                        <span class="font-weight-medium me-1">End:</span>
                        <span>
                          {{
                            quiz.end_date
                              ? new Date(quiz.end_date).toLocaleString('id-ID', {
                                  day: '2-digit',
                                  month: 'short',
                                  year: 'numeric',
                                  hour: '2-digit',
                                  minute: '2-digit'
                                })
                              : '-'
                          }}
                        </span>
                      </div>
                    </div>

                    <!-- Quiz Information -->
                    <div
                      v-else
                      class="text-caption text-medium-emphasis mt-1"
                    >
                      Quiz
                    </div>
                  </div>
                </a>
              </template>

              <!-- Assignments -->
              <template v-for="assignment in courseAssignments" :key="assignment.id">
                <a
                  :href="route('student.assignments.show', assignment.id)"
                  class="assessment-item d-flex align-start ga-3 pa-3 mb-2 text-decoration-none rounded border assignment-item"
                >
                  <VIcon
                    icon="mdi-clipboard-text-outline"
                    size="20"
                    color="warning"
                    class="mt-1"
                  />

                  <div class="flex-grow-1">
                    <div class="d-flex align-center justify-space-between ga-2">
                      <span class="text-body-2 font-weight-bold">
                        {{ assignment.title }}
                      </span>

                      <VChip
                        color="warning"
                        size="x-small"
                        variant="tonal"
                      >
                        Assignment
                      </VChip>
                    </div>
                  </div>
                </a>
              </template>

            </template>

            <p
              v-else
              class="text-caption text-medium-emphasis"
            >
              No assessments available yet.
            </p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Main Content Area -->
      <VCol cols="12" lg="9">
        <VCard flat border>
          <div class="video-container">
            <VideoPlayer
              v-if="selectedVideo"
              :key="`${selectedLesson.id}-${selectedVideo.id}`"
              :video="selectedVideo"
              :initial-watched-until="progressValue"
              @timeupdate="onTimeUpdate"
              @ended="onVideoEnded"
            />

            <div
              v-else
              class="video-placeholder bg-grey-darken-4 d-flex align-center justify-center"
            >
              <div class="text-center text-white">
                <VIcon
                  icon="mdi-video-off-outline"
                  size="64"
                  color="disabled"
                />

                <p class="text-body-1 text-medium-emphasis mt-2">
                  No Video Available
                </p>

                <p class="text-caption text-medium-emphasis">
                  This lesson does not have a ready video yet.
                </p>
              </div>
            </div>
          </div>
          <VCardText>
            <h4 class="text-h4 font-weight-bold mb-2">
              {{ selectedLesson?.title || 'Lesson Overview' }}
            </h4>

            <p
              v-if="selectedLesson?.content"
              class="text-body-1 text-medium-emphasis mb-6"
            >
              {{ selectedLesson.content }}
            </p>

            <p
              v-else
              class="text-body-1 text-medium-emphasis mb-6"
            >
              Continue learning this lesson using the video above.
            </p>

            <!-- <VAlert type="info" variant="tonal" class="mb-6">
              <template #title>What You'll Learn</template>
              <ul class="mt-1">
                <li>Key concepts and fundamental knowledge</li>
                <li>Practical application of the concepts</li>
                <li>Best practices and industry standards</li>
                <li>Hands-on exercises and examples</li>
              </ul>
            </VAlert>

            <VCard flat border class="pa-4 mb-6 bg-grey-lighten-4">
              <h6 class="text-body-2 font-weight-bold mb-3">
                Resources
              </h6>

              <p class="text-caption text-medium-emphasis">
                No additional resources available for this lesson.
              </p>
            </VCard> -->

            <!-- <VBtn v-if="!enrollment.completed_at" color="success" variant="elevated" prepend-icon="mdi-check" class="mb-6">Mark as Complete</VBtn> -->

            <VDivider class="mb-4" />
            <div class="d-flex ga-3">
             <VBtn
                variant="tonal"
                prepend-icon="mdi-arrow-left"
                :disabled="!previousLesson"
                @click="goToLesson(previousLesson)"
              >
                Previous Lesson Video
              </VBtn>

              <VBtn
                color="primary"
                variant="elevated"
                append-icon="mdi-arrow-right"
                :disabled="!nextLesson"
                @click="goToLesson(nextLesson)"
              >
                Next Lesson Video
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.sticky-top { position: sticky; top: 80px; }
.bg-grey-lighten-4 { background-color: rgb(var(--v-theme-grey-lighten-4)) !important; }
.min-h-screen { min-height: 100vh; }
.border-left-thick { border-left: 4px solid; }
.border-bottom { border-bottom: 1px solid rgba(0,0,0,0.12); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.spin { animation: spin 1.5s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.assessment-item {
  transition: background-color 0.2s ease, border-color 0.2s ease;
}

.assessment-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
}

.quiz-item {
  border-color: rgba(var(--v-theme-primary), 0.15) !important;
}

.exam-item {
  border-color: rgba(var(--v-theme-error), 0.25) !important;
  background-color: rgba(var(--v-theme-error), 0.025);
}

.assignment-item {
  border-color: rgba(var(--v-theme-warning), 0.2) !important;
}

.lesson-item {
  border: 0;
  background: transparent;
  cursor: pointer;
  font-family: inherit;
}
</style>
