<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import VideoPlayer from '@/components/learning/VideoPlayer.vue'

const props = defineProps({
  course: { type: Object, required: true },
  lesson: { type: Object, required: true },
  enrollment: { type: Object, default: null },
  progress: { type: Object, default: null },
  courseProgress: { type: Number, default: 0 },
  previousLesson: { type: Object, default: null },
  nextLesson: { type: Object, default: null },
})

const completing = ref(false)
const previewDialog = ref(false)
const previewMaterial = ref(null)

function openPreview(material) {
  previewMaterial.value = material
  previewDialog.value = true
}

function closePreview() {
  previewDialog.value = false
}
const sections = computed(() => props.course.sections || [])
const readyVideo = computed(() => {
  return props.lesson.videos?.find(v => v.status === 'ready')
})

const progressValue = ref(props.progress?.watched_seconds ?? 0)
const totalSeconds = ref(props.progress?.total_seconds ?? 0)
const progressCompleted = ref(props.progress?.completed ?? false)
let lastUpdateTime = 0
let progressRequestPending = false

function onTimeUpdate({ currentTime, duration: playerDuration }) {
  const current = Math.floor(currentTime)
  const duration = Math.floor(playerDuration) || 0
  totalSeconds.value = duration

  if (duration > 0 && current - lastUpdateTime >= 5 && !progressRequestPending) {
    lastUpdateTime = current
    sendProgress(current, duration)
  }
}

async function sendProgress(current, duration) {
  progressRequestPending = true

  try {
    const response = await fetch(route('student.lessons.update-progress'), {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({
        lesson_id: props.lesson.id,
        video_id: readyVideo.value?.id,
        watched_seconds: current,
        total_seconds: duration,
      }),
    })

    if (!response.ok) return

    const result = await response.json()
    progressValue.value = result.progress?.watched_seconds ?? progressValue.value
    totalSeconds.value = result.progress?.total_seconds ?? totalSeconds.value
    progressCompleted.value = result.progress?.completed ?? progressCompleted.value
  } catch {
    // Pembaruan berikutnya akan mencoba lagi tanpa mengganggu pemutaran video.
  } finally {
    progressRequestPending = false
  }
}

function markComplete() {
  completing.value = true
  router.post(route('student.lessons.complete', props.lesson.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      completing.value = false
    },
    onError: () => {
      completing.value = false
    },
  })
}

const allCourseLessons = computed(() => {
  const list = []
  for (const section of sections.value) {
    for (const lesson of (section.lessons || [])) {
      list.push(lesson)
    }
  }
  return list
})

function formatFileSize(bytes) {
  if (!bytes) return '0 MB'
  return (bytes / 1024 / 1024).toFixed(2) + ' MB'
}

function canPreview(material) {
  const extension = material.file_type
    ?.toLowerCase()
    ?.replace('.', '')

  return ['pdf', 'ppt', 'pptx', 'doc', 'docx'].includes(extension)
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric',
  })
}
</script>

<template>
  <div class="lesson-page">
    <div class="lesson-main">
      <!-- Video Player -->
      <VCard flat border class="mb-6 overflow-hidden">
        <div class="bg-black position-relative" style="aspect-ratio: 16 / 9;">
          <VideoPlayer
            v-if="readyVideo"
            :video="readyVideo"
            @timeupdate="onTimeUpdate"
          />
          <div v-else class="d-flex flex-column align-center justify-center text-white h-100 pa-4">
            <VIcon icon="mdi-play-circle-outline" size="64" color="grey-lighten-1" />
            <p class="mt-3 text-grey-lighten-1">
              {{ props.lesson.videos?.length > 0 ? 'Video is processing (' + props.lesson.videos[0].status + ')' : 'No video uploaded for this lesson' }}
            </p>
          </div>
        </div>
      </VCard>

      <!-- Breadcrumb -->
      <nav class="d-flex align-center text-body-2 text-medium-emphasis mb-4 ga-1">
        <a class="text-link text-decoration-none" :href="route('student.courses.list')">Courses</a>
        <VIcon icon="mdi-chevron-right" size="16" />
        <a class="text-link text-decoration-none" :href="route('student.courses.show', course.id)">{{ course.title }}</a>
        <VIcon icon="mdi-chevron-right" size="16" />
        <span>{{ lesson.section?.title }}</span>
        <VIcon icon="mdi-chevron-right" size="16" />
        <span class="text-high-emphasis font-weight-medium">{{ lesson.title }}</span>
      </nav>

      <!-- Lesson Info -->
      <VCard flat border class="mb-6">
        <VCardText>
          <div class="d-flex justify-space-between align-start mb-4">
            <div>
              <h4 class="text-h4 font-weight-bold mb-1">{{ lesson.title }}</h4>
              <p class="text-body-2 text-medium-emphasis">
                {{ course.title }} &gt; {{ lesson.section?.title }}
              </p>
            </div>
            <VChip
              v-if="progressCompleted"
              color="success"
              variant="tonal"
              size="small"
              label
            >
              <VIcon start icon="mdi-check" size="16" />
              Completed
            </VChip>
          </div>

          <VDivider class="mb-4" />

          <!-- Lesson Content -->
          <div v-if="lesson.content" class="text-body-1 mb-4" v-html="lesson.content" />

          <!-- Materials -->
          <div v-if="lesson.materials?.length > 0" class="mb-4">
            <h6 class="text-h6 mb-3">
              <VIcon icon="mdi-paperclip" class="me-1" />
              Lesson Materials
            </h6>
            <VList class="bg-white rounded">
              <VListItem
                v-for="material in lesson.materials"
                :key="material.id"
              >
                <template #prepend>
                  <VAvatar rounded variant="tonal" color="primary">
                    <VIcon icon="mdi-file-document-outline" size="24" />
                  </VAvatar>
                </template>
                <VListItemTitle class="font-weight-medium">
                  {{ material.title }}
                </VListItemTitle>
                <VListItemSubtitle>
                  {{ material.file_type?.toUpperCase() }} • {{ formatFileSize(material.file_size) }}
                </VListItemSubtitle>
                <template #append>
                  <div class="d-flex ga-2">
                    <VBtn
                      v-if="canPreview(material)"
                      variant="tonal"
                      color="primary"
                      size="small"
                      @click="openPreview(material)"
                    >
                      <VIcon start icon="mdi-eye" size="16" />
                      Preview
                    </VBtn>

                    <VBtn
                      variant="outlined"
                      color="primary"
                      size="small"
                      :href="route('student.materials.download', material.id)"
                    >
                      <VIcon start icon="mdi-download" size="16" />
                      Download
                    </VBtn>
                  </div>
                </template>
              </VListItem>
            </VList>
          </div>

          <!-- Mark as Complete -->
          <div v-if="!progressCompleted" class="mt-4">
            <VBtn
              color="success"
              variant="elevated"
              :loading="completing"
              @click="markComplete"
            >
              <VIcon start icon="mdi-check" />
              Mark as Complete
            </VBtn>
          </div>
          <VAlert
            v-else
            color="success"
            variant="tonal"
            class="mt-4"
            density="compact"
            icon="mdi-check-circle"
          >
            You have completed this lesson
          </VAlert>
        </VCardText>
      </VCard>

      <!-- Material Preview Dialog -->
      <VDialog
        v-if="previewMaterial"
        v-model="previewDialog"
        max-width="1000"
        scrollable
      >
        <VCard>
          <VCardTitle class="d-flex align-center justify-space-between">
            <span class="text-h6">
              {{ previewMaterial?.title || 'Material Preview' }}
            </span>

            <VBtn
              icon
              variant="text"
              size="small"
              @click="closePreview"
            >
              <VIcon icon="mdi-close" />
            </VBtn>
          </VCardTitle>

          <VDivider />

          <VCardText class="pa-0">
            <div class="material-preview">
              <iframe
                :src="route('student.materials.preview', previewMaterial.id)"
                class="material-preview__iframe"
                title="Material Preview"
              />
            </div>
          </VCardText>

          <VDivider />

          <VCardActions>
            <VSpacer />

            <VBtn
              variant="tonal"
              @click="closePreview"
            >
              Close
            </VBtn>

            <VBtn
              color="primary"
              :href="route('student.materials.download', previewMaterial.id)"
            >
              <VIcon start icon="mdi-download" />
              Download
            </VBtn>
          </VCardActions>
        </VCard>
      </VDialog>

      <!-- Navigation -->
      <div class="d-flex justify-space-between ga-4 mb-6">
        <VBtn
          v-if="previousLesson"
          variant="tonal"
          color="secondary"
          prepend-icon="mdi-chevron-left"
          :href="route('student.lessons.view', [course.id, previousLesson.id])"
        >
          Previous Lesson
        </VBtn>
        <VSpacer v-else />
        <VBtn
          v-if="nextLesson"
          variant="elevated"
          color="primary"
          append-icon="mdi-chevron-right"
          :href="route('student.lessons.view', [course.id, nextLesson.id])"
        >
          Next Lesson
        </VBtn>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="lesson-sidebar">
      <VCard flat border class="sticky-top">
        <VCardText>
          <h6 class="text-h6 mb-1">{{ course.title }}</h6>
          <p class="text-body-2 text-medium-emphasis mb-4">{{ course.sections?.length || 0 }} sections</p>

          <!-- Progress -->
          <div class="mb-4">
            <div class="d-flex justify-space-between mb-1">
              <span class="text-body-2 font-weight-medium">Course Progress</span>
              <span class="text-body-2 font-weight-bold text-primary">{{ courseProgress }}%</span>
            </div>
            <VProgressLinear
              :model-value="courseProgress"
              color="primary"
              height="8"
              rounded
            />
          </div>
        </VCardText>

        <VDivider />

        <!-- Sections & Lessons -->
        <div class="lesson-list px-2 py-2">
          <template v-for="section in sections" :key="section.id">
            <div class="px-2 py-2">
              <div class="d-flex align-center ga-2 mb-1">
                <VChip
                  size="x-small"
                  variant="tonal"
                  color="default"
                  label
                >
                  {{ sections.indexOf(section) + 1 }}
                </VChip>
                <span class="text-body-2 font-weight-medium">{{ section.title }}</span>
              </div>
              <VList density="compact" class="ms-2">
                <VListItem
                  v-for="sl in (section.lessons || [])"
                  :key="sl.id"
                  :class="{ 'bg-primary-light rounded': sl.id === lesson.id }"
                  :href="route('student.lessons.view', [course.id, sl.id])"
                  class="lesson-item mb-1"
                  density="compact"
                >
                  <template #prepend>
                    <VIcon
                      v-if="sl.progresses?.[0]?.completed"
                      icon="mdi-check-circle"
                      color="success"
                      size="18"
                    />
                    <VIcon
                      v-else
                      icon="mdi-circle-outline"
                      color="grey"
                      size="18"
                    />
                  </template>
                  <VListItemTitle class="text-body-2">
                    {{ sl.title }}
                  </VListItemTitle>
                </VListItem>
              </VList>
            </div>
            <VDivider v-if="sections.indexOf(section) < sections.length - 1" />
          </template>
        </div>

        <VDivider />
        <VCardText>
          <VBtn
            variant="text"
            color="primary"
            block
            :href="route('student.courses.show', course.id)"
          >
            <VIcon start icon="mdi-arrow-left" />
            Back to Course
          </VBtn>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style scoped>
.lesson-page {
  display: flex;
  gap: 24px;
}

.lesson-main {
  flex: 1;
  min-width: 0;
}

.lesson-sidebar {
  width: 320px;
  flex-shrink: 0;
}

.sticky-top {
  position: sticky;
  top: 80px;
}

.lesson-list {
  max-height: 400px;
  overflow-y: auto;
}

.lesson-item {
  border-radius: 6px;
}

.lesson-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.08);
}

.material-preview {
  width: 100%;
  height: 70vh;
  background: #f5f5f5;
}

.material-preview__iframe {
  width: 100%;
  height: 100%;
  border: 0;
  display: block;
}
</style>
