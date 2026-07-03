<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { router } from '@inertiajs/vue3'

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
const videoRef = ref(null)
const hlsInstance = ref(null)
const sections = computed(() => props.course.sections || [])
const readyVideo = computed(() => {
  return props.lesson.videos?.find(v => v.status === 'ready')
})

onMounted(() => {
  initHls()
})

onBeforeUnmount(() => {
  if (hlsInstance.value) {
    hlsInstance.value.destroy()
    hlsInstance.value = null
  }
})

function initHls() {
  const video = videoRef.value
  const readyVid = readyVideo.value
  if (!video || !readyVid) return

  const hlsUrl = readyVid.hls_path
    ? '/storage/' + readyVid.hls_path + '/master.m3u8'
    : null

  if (!hlsUrl) return

  import('hls.js').then(({ default: Hls }) => {
    if (Hls.isSupported()) {
      const hls = new Hls()
      hls.loadSource(hlsUrl)
      hls.attachMedia(video)
      hlsInstance.value = hls
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = hlsUrl
    }
  })
}

const progressValue = ref(props.progress?.watched_seconds ?? 0)
const totalSeconds = ref(props.progress?.total_seconds ?? 0)
let lastUpdateTime = 0

function onTimeUpdate() {
  const video = videoRef.value
  if (!video) return

  const current = Math.floor(video.currentTime)
  const duration = Math.floor(video.duration) || 0
  totalSeconds.value = duration

  if (current - lastUpdateTime >= 5) {
    lastUpdateTime = current
    router.post(route('student.lessons.update-progress'), {
      lesson_id: props.lesson.id,
      watched_seconds: current,
      total_seconds: duration,
    }, {
      preserveScroll: true,
      preserveState: true,
    })
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
          <video
            v-if="readyVideo"
            ref="videoRef"
            class="w-100 h-100"
            controls
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
              v-if="progress?.completed"
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
                  <VBtn
                    variant="tonal"
                    color="primary"
                    size="small"
                    :href="route('student.materials.download', material.id)"
                  >
                    <VIcon start icon="mdi-download" size="16" />
                    Download
                  </VBtn>
                </template>
              </VListItem>
            </VList>
          </div>

          <!-- Mark as Complete -->
          <div v-if="!progress?.completed" class="mt-4">
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
</style>
