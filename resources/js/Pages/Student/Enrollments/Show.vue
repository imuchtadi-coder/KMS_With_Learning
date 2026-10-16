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

const activeTab = ref('content') // 'content' or 'assessments'
const markingComplete = ref(false)

/*
|--------------------------------------------------------------------------
| Course & Instructor
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
| Selected Lesson & Section
|--------------------------------------------------------------------------
*/
const selectedLesson = ref(null)

const currentSection = computed(() => {
  if (!selectedLesson.value) return null
  return sections.value.find(s => (s.lessons || []).some(l => l.id === selectedLesson.value.id)) || null
})

/*
|--------------------------------------------------------------------------
| Progress Tracker for Video
|--------------------------------------------------------------------------
*/
let lastSavedWatchedSeconds = 0
let savingProgress = false

watch(
  allLessons,
  lessons => {
    if (!lessons.length) {
      selectedLesson.value = null
      return
    }

    if (
      selectedLesson.value &&
      lessons.some(lesson => lesson.id === selectedLesson.value.id)
    ) {
      return
    }

    // Default to the first uncompleted lesson, or the first lesson
    const firstUncompleted = lessons.find(l => !l.progresses?.[0]?.completed)
    selectedLesson.value = firstUncompleted || lessons[0]
  },
  {
    immediate: true,
  }
)

const selectedVideo = computed(() => {
  if (!selectedLesson.value) return null
  return (selectedLesson.value.videos || []).find(v => v.status === 'ready') || null
})

const selectedProgress = computed(() => {
  if (!selectedLesson.value) return null
  return selectedLesson.value.progresses?.[0] || null
})

const isCurrentLessonCompleted = computed(() => {
  return !!selectedProgress.value?.completed
})

const progressValue = computed(() => {
  return Number(selectedProgress.value?.watched_seconds ?? 0)
})

const currentLessonIndex = computed(() => {
  if (!selectedLesson.value) return -1
  return allLessons.value.findIndex(lesson => lesson.id === selectedLesson.value.id)
})

const previousLesson = computed(() => {
  const index = currentLessonIndex.value
  if (index <= 0) return null
  return allLessons.value[index - 1]
})

const nextLesson = computed(() => {
  const index = currentLessonIndex.value
  if (index < 0 || index >= allLessons.value.length - 1) return null
  return allLessons.value[index + 1]
})

const selectLesson = lesson => {
  if (!lesson) return
  lastSavedWatchedSeconds = 0
  savingProgress = false
  selectedLesson.value = lesson

  window.scrollTo({
    top: 220,
    behavior: 'smooth',
  })
}

const goToLesson = lesson => {
  selectLesson(lesson)
}

const saveProgress = async ({
  currentTime,
  duration,
  watchedUntil,
}) => {
  if (!selectedLesson.value) return

  const watchedSeconds = Math.max(
    Number(watchedUntil || 0),
    Number(currentTime || 0)
  )
  const totalSeconds = Number(duration || 0)

  if (watchedSeconds <= 0 || totalSeconds <= 0) return

  if (
    watchedSeconds - lastSavedWatchedSeconds < 5 &&
    watchedSeconds < totalSeconds
  ) {
    return
  }

  if (savingProgress) return
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
    console.error('Failed to save video progress:', error)
  } finally {
    savingProgress = false
  }
}

const onTimeUpdate = payload => {
  saveProgress(payload)
}

const onVideoEnded = async payload => {
  if (!selectedLesson.value) return

  await saveProgress({
    ...payload,
    watchedUntil: payload?.duration ?? payload?.watchedUntil,
  })

  markAsComplete()
}

const markAsComplete = () => {
  if (!selectedLesson.value) return
  markingComplete.value = true
  router.post(
    route('student.lessons.complete', selectedLesson.value.id),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        markingComplete.value = false
      },
    }
  )
}

watch(
  () => selectedLesson.value?.id,
  () => {
    lastSavedWatchedSeconds = 0
    savingProgress = false
  }
)

const formatDate = dateString => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })
  } catch (e) {
    return dateString
  }
}

const formatDateTime = dateString => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleString('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch (e) {
    return dateString
  }
}
</script>

<template>
  <div class="enrollment-learning-page pb-12">
    <!-- Top Action & Navigation Bar -->
    <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-4">
      <VBtn
        variant="text"
        color="primary"
        size="small"
        class="font-weight-medium px-2"
        :href="route('student.enrollments')"
      >
        <VIcon start icon="mdi-arrow-left" size="18" />
        Kembali ke Pembelajaran Saya
      </VBtn>

      <div class="d-flex align-center ga-2">
        <VBtn
          v-if="course.slug || course.id"
          variant="tonal"
          color="default"
          size="small"
          prepend-icon="mdi-information-outline"
          class="font-weight-medium"
          :href="route('student.courses.show', course.slug || course.id)"
        >
          Detail Kursus
        </VBtn>

        <VChip
          v-if="enrollment.completed_at || courseProgress === 100"
          color="success"
          size="small"
          variant="flat"
          label
          class="font-weight-bold"
        >
          <VIcon start icon="mdi-check-decagram" size="14" />
          Kursus Tuntas
        </VChip>
        <VChip
          v-else
          color="primary"
          size="small"
          variant="tonal"
          label
          class="font-weight-medium"
        >
          <VIcon start icon="mdi-progress-clock" size="14" />
          {{ courseProgress }}% Selesai
        </VChip>
      </div>
    </div>

    <!-- Course Header Hero Banner -->
    <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
      <div class="pa-6 pa-md-8">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-6">
          <div class="flex-grow-1">
            <div class="d-flex align-center ga-2 flex-wrap mb-2">
              <VChip size="small" color="primary" variant="tonal" label class="font-weight-bold">
                {{ course.category?.name || 'Kursus Aktif' }}
              </VChip>
              <VChip
                v-if="course.level"
                size="small"
                variant="outlined"
                color="default"
                label
              >
                <VIcon start icon="mdi-signal" size="14" />
                {{ course.level }}
              </VChip>
            </div>

            <h3 class="text-h4 font-weight-bold text-high-emphasis mb-2">
              {{ course.title }}
            </h3>

            <div class="d-flex align-center ga-4 flex-wrap text-caption text-medium-emphasis">
              <div class="d-flex align-center ga-2">
                <VAvatar size="24" :image="instructor.avatar_url">
                  <span class="text-caption font-weight-bold">{{ (instructor.name || 'I').charAt(0) }}</span>
                </VAvatar>
                <span class="text-high-emphasis font-weight-medium">{{ instructor.name || 'Instruktur' }}</span>
              </div>
              <span>•</span>
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-play-box-multiple-outline" size="16" />
                <span>{{ totalLessons }} Materi Belajar</span>
              </div>
              <span>•</span>
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-clipboard-list-outline" size="16" />
                <span>{{ courseQuizzes.length + courseAssignments.length }} Asesmen</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick KPI Progress Strip -->
        <VDivider class="my-5" />

        <VRow>
          <!-- 1. Progress -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex flex-column justify-center pa-3 rounded-lg bg-background border h-100">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-caption text-medium-emphasis font-weight-medium">Kemajuan Belajar</span>
                <span class="text-caption font-weight-bold text-primary">{{ courseProgress }}%</span>
              </div>
              <VProgressLinear
                :model-value="courseProgress"
                :color="courseProgress === 100 ? 'success' : 'primary'"
                height="8"
                rounded
                bg-opacity="0.15"
              />
            </div>
          </VCol>

          <!-- 2. Lessons Completed -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="primary" variant="tonal" size="40" rounded="lg">
                <VIcon icon="mdi-check-circle-outline" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis">Materi Tuntas</div>
                <div class="text-subtitle-1 font-weight-bold text-high-emphasis">
                  {{ completedLessons }} <span class="text-caption text-medium-emphasis">/ {{ totalLessons }}</span>
                </div>
              </div>
            </div>
          </VCol>

          <!-- 3. Assessments -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="warning" variant="tonal" size="40" rounded="lg">
                <VIcon icon="mdi-file-document-edit-outline" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis">Kuis & Tugas</div>
                <div class="text-subtitle-1 font-weight-bold text-high-emphasis">
                  {{ courseQuizzes.length + courseAssignments.length }} Evaluasi
                </div>
              </div>
            </div>
          </VCol>

          <!-- 4. Completion Status -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar
                :color="enrollment.completed_at || courseProgress === 100 ? 'success' : 'info'"
                variant="tonal"
                size="40"
                rounded="lg"
              >
                <VIcon
                  :icon="enrollment.completed_at || courseProgress === 100 ? 'mdi-trophy-variant' : 'mdi-play-circle-outline'"
                  size="22"
                />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis">Status Kursus</div>
                <div
                  v-if="enrollment.completed_at || courseProgress === 100"
                  class="text-subtitle-2 font-weight-bold text-success"
                >
                  Tuntas ({{ formatDate(enrollment.completed_at) }})
                </div>
                <div v-else class="text-subtitle-2 font-weight-bold text-primary">
                  Sedang Berjalan
                </div>
              </div>
            </div>
          </VCol>
        </VRow>
      </div>
    </VCard>

    <!-- Main Learning Area: Player + Playlist Sidebar -->
    <VRow>
      <!-- Main Content / Video Area (cols 8 on lg) -->
      <VCol cols="12" lg="8">
        <VCard flat border class="rounded-xl overflow-hidden bg-surface mb-6 shadow-sm">
          <!-- Video Player Display -->
          <div class="video-display-area bg-grey-darken-4 position-relative">
            <VideoPlayer
              v-if="selectedVideo"
              :key="`${selectedLesson?.id}-${selectedVideo.id}`"
              :video="selectedVideo"
              :initial-watched-until="progressValue"
              @timeupdate="onTimeUpdate"
              @ended="onVideoEnded"
            />

            <!-- Fallback Placeholder when no ready video -->
            <div
              v-else
              class="d-flex flex-column align-center justify-center pa-8 text-center"
              style="min-height: 380px; background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.95) 0%, rgba(var(--v-theme-primary), 0.1) 100%);"
            >
              <VAvatar color="primary" variant="tonal" size="64" class="mb-3">
                <VIcon icon="mdi-book-open-page-variant-outline" size="36" color="primary" />
              </VAvatar>
              <h5 class="text-h6 font-weight-bold mb-1">Materi Berbasis Teks & Bacaan</h5>
              <p class="text-body-2 text-medium-emphasis mb-4" style="max-width: 440px;">
                Materi pelajaran ini berfokus pada pendalaman modul teks atau panduan tertulis di bawah ini.
              </p>
              <VBtn
                v-if="!isCurrentLessonCompleted"
                color="primary"
                variant="elevated"
                size="small"
                :loading="markingComplete"
                @click="markAsComplete"
              >
                <VIcon start icon="mdi-check" size="18" />
                Tandai Selesai Dibaca
              </VBtn>
            </div>
          </div>

          <!-- Lesson Details Body -->
          <div class="pa-6">
            <!-- Breadcrumb Navigation for Current Lesson -->
            <div class="d-flex align-center justify-space-between flex-wrap ga-2 mb-2">
              <span class="text-caption font-weight-bold text-uppercase text-medium-emphasis">
                {{ currentSection?.title || 'Modul Pembelajaran' }}
              </span>

              <VChip
                v-if="isCurrentLessonCompleted"
                size="small"
                color="success"
                variant="tonal"
                label
                class="font-weight-bold"
              >
                <VIcon start icon="mdi-check-circle" size="14" />
                Materi Terselesaikan
              </VChip>
              <VChip
                v-else
                size="small"
                color="warning"
                variant="tonal"
                label
                class="font-weight-medium"
              >
                Belum Selesai
              </VChip>
            </div>

            <!-- Lesson Title -->
            <h4 class="text-h5 font-weight-bold text-high-emphasis mb-4">
              {{ selectedLesson?.title || 'Pilih Materi Pembelajaran' }}
            </h4>

            <!-- Lesson Content Text / Description -->
            <div
              v-if="selectedLesson?.content"
              class="text-body-1 text-high-emphasis lesson-body-content mb-6"
              style="line-height: 1.7;"
            >
              {{ selectedLesson.content }}
            </div>
            <div
              v-else
              class="text-body-2 text-medium-emphasis mb-6"
            >
              Pelajari materi melalui pemutar video di atas atau gunakan panduan modul yang disediakan instruktur.
            </div>

            <VDivider class="mb-5" />

            <!-- Lesson Navigation Footer Controls -->
            <div class="d-flex flex-column flex-sm-row justify-space-between align-stretch align-sm-center ga-3">
              <VBtn
                variant="tonal"
                color="default"
                prepend-icon="mdi-arrow-left"
                class="font-weight-medium"
                :disabled="!previousLesson"
                @click="goToLesson(previousLesson)"
              >
                Pelajaran Sebelumnya
              </VBtn>

              <div class="d-flex align-center ga-2 justify-end">
                <VBtn
                  v-if="!isCurrentLessonCompleted"
                  color="success"
                  variant="tonal"
                  class="font-weight-medium"
                  :loading="markingComplete"
                  @click="markAsComplete"
                >
                  <VIcon start icon="mdi-check" size="18" />
                  Tandai Selesai
                </VBtn>

                <VBtn
                  color="primary"
                  variant="elevated"
                  append-icon="mdi-arrow-right"
                  class="font-weight-medium"
                  :disabled="!nextLesson"
                  @click="goToLesson(nextLesson)"
                >
                  Pelajaran Berikutnya
                </VBtn>
              </div>
            </div>
          </div>
        </VCard>

        <!-- Course 100% Completed Banner -->
        <VCard
          v-if="courseProgress === 100 || enrollment.completed_at"
          flat
          border
          class="rounded-xl pa-6 bg-surface border-success mb-6 text-center"
          style="border-left: 6px solid rgb(var(--v-theme-success)) !important;"
        >
          <div class="d-flex flex-column align-center">
            <VAvatar color="success" variant="tonal" size="64" class="mb-3">
              <VIcon icon="mdi-trophy-variant" size="36" color="success" />
            </VAvatar>
            <h5 class="text-h5 font-weight-bold text-success mb-1">
              Selamat! Anda Telah Menyelesaikan Kursus Ini
            </h5>
            <p class="text-body-2 text-medium-emphasis mb-4" style="max-width: 520px;">
              Seluruh materi kurikulum telah tuntas Anda pelajari. Anda dapat mengulas kembali materi kapan pun dibutuhkan.
            </p>
            <div class="d-flex align-center ga-3">
              <VBtn
                color="success"
                variant="elevated"
                class="font-weight-bold"
                :href="route('student.courses.show', course.slug || course.id)"
              >
                Beri Ulasan Kursus
              </VBtn>
            </div>
          </div>
        </VCard>
      </VCol>

      <!-- Right Column: Course Content & Assessments Playlist (cols 4 on lg) -->
      <VCol cols="12" lg="4">
        <div class="sticky-playlist-sidebar">
          <VCard flat border class="rounded-xl overflow-hidden bg-surface shadow-sm">
            <!-- Playlist Top Header Tabs -->
            <div class="pa-4 border-b bg-background">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="text-subtitle-2 font-weight-bold text-high-emphasis">
                  Daftar Pembelajaran
                </span>
                <span class="text-caption text-medium-emphasis">
                  {{ completedLessons }}/{{ totalLessons }} Selesai
                </span>
              </div>

              <!-- Segmented Tabs -->
              <div class="d-flex bg-surface rounded-pill pa-1 border">
                <VBtn
                  size="small"
                  :variant="activeTab === 'content' ? 'flat' : 'text'"
                  :color="activeTab === 'content' ? 'primary' : 'default'"
                  rounded="pill"
                  class="flex-grow-1 text-capitalize font-weight-medium"
                  @click="activeTab = 'content'"
                >
                  <VIcon start icon="mdi-play-box-multiple" size="16" />
                  Materi ({{ allLessons.length }})
                </VBtn>

                <VBtn
                  size="small"
                  :variant="activeTab === 'assessments' ? 'flat' : 'text'"
                  :color="activeTab === 'assessments' ? 'primary' : 'default'"
                  rounded="pill"
                  class="flex-grow-1 text-capitalize font-weight-medium"
                  @click="activeTab = 'assessments'"
                >
                  <VIcon start icon="mdi-clipboard-list" size="16" />
                  Asesmen ({{ courseQuizzes.length + courseAssignments.length }})
                </VBtn>
              </div>
            </div>

            <!-- Tab 1: Course Syllabus Sections -->
            <div
              v-if="activeTab === 'content'"
              class="playlist-scroll-area overflow-y-auto"
            >
              <div
                v-for="(section, sIndex) in sections"
                :key="section.id"
                class="section-group border-b"
              >
                <!-- Section Header -->
                <div class="pa-3 bg-background border-b d-flex align-center justify-space-between">
                  <span class="text-caption font-weight-bold text-high-emphasis">
                    {{ sIndex + 1 }}. {{ section.title }}
                  </span>
                  <span class="text-caption text-medium-emphasis">
                    {{ (section.lessons || []).length }} Materi
                  </span>
                </div>

                <!-- Lessons in Section -->
                <div class="d-flex flex-column">
                  <div
                    v-for="(lesson, lIndex) in (section.lessons || [])"
                    :key="lesson.id"
                    class="lesson-row d-flex align-center ga-3 pa-3 transition-swing cursor-pointer border-b"
                    :class="{
                      'active-lesson': selectedLesson?.id === lesson.id,
                      'completed-lesson': lesson.progresses?.[0]?.completed,
                    }"
                    @click="selectLesson(lesson)"
                  >
                    <!-- Status Icon -->
                    <VAvatar
                      size="28"
                      :color="lesson.progresses?.[0]?.completed ? 'success' : (selectedLesson?.id === lesson.id ? 'primary' : 'default')"
                      :variant="lesson.progresses?.[0]?.completed ? 'flat' : 'tonal'"
                      class="flex-shrink-0"
                    >
                      <VIcon
                        v-if="lesson.progresses?.[0]?.completed"
                        icon="mdi-check"
                        size="16"
                        color="white"
                      />
                      <VIcon
                        v-else-if="selectedLesson?.id === lesson.id"
                        icon="mdi-play"
                        size="16"
                      />
                      <span v-else class="text-caption font-weight-bold">{{ lIndex + 1 }}</span>
                    </VAvatar>

                    <!-- Lesson Title & Info -->
                    <div class="flex-grow-1 overflow-hidden">
                      <div
                        class="text-body-2 font-weight-medium line-clamp-2"
                        :class="selectedLesson?.id === lesson.id ? 'text-primary font-weight-bold' : 'text-high-emphasis'"
                      >
                        {{ lesson.title }}
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        {{ lesson.duration_minutes ? `${lesson.duration_minutes} mnt` : 'Materi Belajar' }}
                      </div>
                    </div>

                    <!-- Direct Lesson Page Link Icon -->
                    <VBtn
                      icon
                      size="x-small"
                      variant="text"
                      color="default"
                      title="Buka Halaman Modul"
                      :href="route('student.lessons.view', { course: course.id, lesson: lesson.id })"
                      @click.stop
                    >
                      <VIcon icon="mdi-open-in-new" size="14" />
                    </VBtn>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab 2: Quizzes & Assignments -->
            <div
              v-else
              class="playlist-scroll-area pa-4 overflow-y-auto"
            >
              <template v-if="courseQuizzes.length || courseAssignments.length">
                <!-- Quizzes & Exams -->
                <div
                  v-for="quiz in courseQuizzes"
                  :key="quiz.id"
                  class="assessment-card pa-3 mb-3 rounded-lg border bg-background transition-swing"
                >
                  <a
                    :href="route('student.quizzes.show', quiz.id)"
                    class="text-decoration-none d-flex align-start ga-3"
                  >
                    <VAvatar
                      size="36"
                      :color="quiz.is_exam ? 'error' : 'primary'"
                      variant="tonal"
                      rounded="lg"
                      class="flex-shrink-0"
                    >
                      <VIcon
                        :icon="quiz.is_exam ? 'mdi-clipboard-text-clock-outline' : 'mdi-file-document-outline'"
                        size="20"
                      />
                    </VAvatar>

                    <div class="flex-grow-1">
                      <div class="d-flex align-center justify-space-between ga-2 mb-1">
                        <span class="text-subtitle-2 font-weight-bold text-high-emphasis">
                          {{ quiz.title }}
                        </span>
                        <VChip
                          :color="quiz.is_exam ? 'error' : 'primary'"
                          size="x-small"
                          variant="tonal"
                          label
                          class="font-weight-bold"
                        >
                          {{ quiz.is_exam ? 'Ujian' : 'Kuis' }}
                        </VChip>
                      </div>

                      <div v-if="quiz.is_exam" class="text-caption text-medium-emphasis">
                        <div>Mulai: {{ formatDateTime(quiz.start_date) }}</div>
                        <div>Selesai: {{ formatDateTime(quiz.end_date) }}</div>
                      </div>
                      <div v-else class="text-caption text-medium-emphasis">
                        Evaluasi Pemahaman Materi
                      </div>
                    </div>
                  </a>
                </div>

                <!-- Assignments -->
                <div
                  v-for="assignment in courseAssignments"
                  :key="assignment.id"
                  class="assessment-card pa-3 mb-3 rounded-lg border bg-background transition-swing"
                >
                  <a
                    :href="route('student.assignments.show', assignment.id)"
                    class="text-decoration-none d-flex align-start ga-3"
                  >
                    <VAvatar
                      size="36"
                      color="warning"
                      variant="tonal"
                      rounded="lg"
                      class="flex-shrink-0"
                    >
                      <VIcon icon="mdi-clipboard-text-outline" size="20" />
                    </VAvatar>

                    <div class="flex-grow-1">
                      <div class="d-flex align-center justify-space-between ga-2 mb-1">
                        <span class="text-subtitle-2 font-weight-bold text-high-emphasis">
                          {{ assignment.title }}
                        </span>
                        <VChip
                          color="warning"
                          size="x-small"
                          variant="tonal"
                          label
                          class="font-weight-bold"
                        >
                          Tugas
                        </VChip>
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        Kumpulkan lembar kerja tugas
                      </div>
                    </div>
                  </a>
                </div>
              </template>

              <div
                v-else
                class="pa-8 text-center"
              >
                <VIcon icon="mdi-clipboard-check-outline" size="40" color="medium-emphasis" class="mb-2" />
                <div class="text-subtitle-2 font-weight-medium">Belum Ada Asesmen</div>
                <div class="text-caption text-medium-emphasis">
                  Kursus ini belum memiliki kuis atau tugas yang aktif.
                </div>
              </div>
            </div>
          </VCard>
        </div>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.sticky-playlist-sidebar {
  position: sticky;
  top: 90px;
}

.playlist-scroll-area {
  max-height: 520px;
}

.lesson-row {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.lesson-row:last-child {
  border-bottom: none;
}

.lesson-row:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
}

.lesson-row.active-lesson {
  background-color: rgba(var(--v-theme-primary), 0.08);
  border-left: 4px solid rgb(var(--v-theme-primary)) !important;
}

.assessment-card {
  transition: transform 0.15s ease, border-color 0.15s ease;
}

.assessment-card:hover {
  transform: translateY(-2px);
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
