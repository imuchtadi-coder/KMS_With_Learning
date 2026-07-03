<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'

const props = defineProps({
  course: { type: Object, required: true },
  isEnrolled: { type: Boolean, default: false },
})

const selectedLesson = ref(null)
const videoRef = ref(null)
const hlsInstance = ref(null)

const readyVideo = computed(() => {
  return selectedLesson.value?.videos?.find(v => v.status === 'ready')
})

function selectLesson(lesson) {
  selectedLesson.value = lesson
}

function backToOverview() {
  selectedLesson.value = null
}

onMounted(() => {
  initHls()
})

watch(readyVideo, () => {
  if (hlsInstance.value) {
    hlsInstance.value.destroy()
    hlsInstance.value = null
  }
  if (readyVideo.value) {
    nextTick(() => initHls())
  }
})

onBeforeUnmount(() => {
  if (hlsInstance.value) {
    hlsInstance.value.destroy()
    hlsInstance.value = null
  }
})

function initHls() {
  const video = videoRef.value
  if (!video) return

  const vid = readyVideo.value
  if (!vid) return

  const hlsUrl = vid.hls_path
    ? '/storage/' + vid.hls_path + '/master.m3u8'
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

function formatFileSize(bytes) {
  if (!bytes) return '0 MB'
  return (bytes / 1024 / 1024).toFixed(2) + ' MB'
}

function isPreviewable(type) {
  return ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(type?.toLowerCase())
}
</script>

<template>
  <div>
    <VAlert color="warning" variant="tonal" density="compact" class="mb-6">
      <div class="d-flex align-center justify-space-between w-100">
        <div>
          <div class="font-weight-semibold">Preview Mode</div>
          <div class="text-body-2">This is how students will see your course. <strong>{{ course.published ? '' : 'It is not yet published.' }}</strong></div>
        </div>
        <div class="d-flex ga-2 flex-shrink-0">
          <VBtn size="small" color="primary" variant="elevated" :href="route('instructor.courses.edit', course.id)">Edit Course</VBtn>
          <VBtn size="small" variant="tonal" :href="route('instructor.courses.show', course.id)">Back to Detail</VBtn>
        </div>
      </div>
    </VAlert>

    <div v-if="!selectedLesson" class="mb-8">
      <h4 class="text-h4 font-weight-bold mb-2">{{ course.title }}</h4>
      <p class="text-body-1 text-medium-emphasis mb-4">{{ course.description || 'No description provided' }}</p>
      <div class="d-flex align-center ga-4 flex-wrap">
        <span class="text-body-2 text-medium-emphasis">by <strong>{{ course.instructor?.name || 'Unknown' }}</strong></span>
        <VChip v-if="course.level" color="info" size="small" variant="tonal" label>{{ course.level }}</VChip>
        <VChip :color="course.published ? 'success' : 'warning'" size="small" variant="tonal" label>{{ course.published ? 'Published' : 'Draft' }}</VChip>
      </div>
    </div>

    <VRow>
      <VCol cols="12" :lg="selectedLesson ? 8 : 12">
        <!-- Back button (when lesson selected) -->
        <div v-if="selectedLesson" class="d-flex align-center ga-2 mb-4">
          <VBtn variant="text" color="primary" size="small" prepend-icon="mdi-arrow-left" @click="backToOverview">
            Back to Overview
          </VBtn>
        </div>

        <!-- Lesson Player (when lesson selected) -->
        <template v-if="selectedLesson">
          <!-- Video Player -->
          <VCard flat border class="mb-6 overflow-hidden">
            <div class="bg-black position-relative" style="aspect-ratio: 16 / 9;">
              <video
                v-if="readyVideo"
                ref="videoRef"
                class="w-100 h-100"
                controls
              />
              <div v-else class="d-flex flex-column align-center justify-center text-white h-100 pa-4">
                <VIcon icon="mdi-play-circle-outline" size="64" color="grey-lighten-1" />
                <p class="mt-3 text-grey-lighten-1">
                  {{ selectedLesson.videos?.length > 0 ? 'Video is processing (' + selectedLesson.videos[0].status + ')' : 'No video uploaded for this lesson' }}
                </p>
              </div>
            </div>
          </VCard>

          <!-- Lesson Info -->
          <VCard flat border class="mb-6">
            <VCardText>
              <h4 class="text-h4 font-weight-bold mb-1">{{ selectedLesson.title }}</h4>
              <p v-if="selectedLesson.description" class="text-body-1 text-medium-emphasis mt-2">
                {{ selectedLesson.description }}
              </p>

              <VDivider class="my-4" />

              <!-- Lesson Content -->

              <div v-if="selectedLesson.content" class="text-body-1 mb-4" v-html="selectedLesson.content" />

              <!-- Materials -->

                <div v-if="selectedLesson.materials?.length > 0" class="mb-4">
            <h6 class="text-h6 mb-3">
              <VIcon icon="mdi-paperclip" class="me-1" />
              Lesson Materials
            </h6>
            <VList class="bg-white rounded">
              <VListItem
                v-for="material in selectedLesson.materials"
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
                    :href="route('instructor.materials.download', material.id)"
                  >
                    <VIcon start icon="mdi-download" size="16" />
                    Download
                  </VBtn>
                </template>
              </VListItem>
            </VList>
          </div>


            </VCardText>
          </VCard>
        </template>

        <!-- Course Overview (when no lesson selected) -->
        <template v-if="!selectedLesson">
          <VCard flat border class="pa-6 mb-6">
            <h5 class="text-h5 font-weight-bold mb-4">Course Overview</h5>
            <p class="text-body-1">{{ course.description || 'No detailed description available.' }}</p>
          </VCard>

          <VCard v-if="course.learning_outcomes" flat border class="pa-6 mb-6">
            <h5 class="text-h5 font-weight-bold mb-4">What You'll Learn</h5>
            <VList class="pa-0">
              <VListItem v-for="(outcome, i) in course.learning_outcomes?.split(',').filter(Boolean)" :key="i" class="px-0">
                <template #prepend><VIcon icon="mdi-check-circle" color="success" size="20" class="mr-2" /></template>
                <VListItemTitle>{{ outcome }}</VListItemTitle>
              </VListItem>
            </VList>
          </VCard>
        </template>

        <!-- Course Content (always visible, on right column when lesson selected) -->
        <VCard v-if="!selectedLesson" flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-4">Course Content</h5>
          <div class="d-flex flex-column ga-3">
            <VCard v-for="section in (course.sections || [])" :key="section.id" variant="outlined">
              <div class="pa-3 bg-light-primary d-flex align-center border-b">
                <h6 class="text-h6 font-weight-bold">{{ section.title }}</h6>
                <VChip size="x-small" variant="tonal" class="ml-3">{{ section.lessons?.length || 0 }} lesson(s)</VChip>
              </div>
              <VListItem
                v-for="lesson in (section.lessons || [])"
                :key="lesson.id"
                class="border-t"
                :class="{ 'bg-primary-lighten-5': selectedLesson?.id === lesson.id }"
                density="compact"
                style="cursor: pointer;"
                @click="selectLesson(lesson)"
              >
                <template #prepend><VIcon icon="mdi-play-box-outline" size="20" class="text-disabled mr-2" /></template>
                <VListItemTitle>{{ lesson.title }}</VListItemTitle>
                <template #append>
                  <span class="text-caption text-medium-emphasis d-flex align-center ga-2">
                    <VIcon icon="mdi-video" size="14" color="info" />{{ lesson.videos?.length || 0 }}
                    <VIcon icon="mdi-paperclip" size="14" color="primary" class="ms-1" />{{ lesson.materials?.length || 0 }}
                    <span v-if="lesson.duration_minutes" class="ms-1">{{ lesson.duration_minutes }} min</span>
                  </span>
                </template>
              </VListItem>
            </VCard>
          </div>
        </VCard>
      </VCol>

      <!-- Right sidebar (only when lesson selected) -->
      <VCol v-if="selectedLesson" cols="12" lg="4">
        <VCard flat border class="pa-6 sticky-top">
          <h6 class="text-h6 font-weight-semibold mb-3">Course Stats</h6>
          <div class="d-flex flex-column ga-2 text-body-2 mb-4">
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Sections:</span><span class="font-weight-semibold">{{ course.sections?.length || 0 }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Total Lessons:</span><span class="font-weight-semibold">{{ course.sections?.flatMap(s => s.lessons || []).length || 0 }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Students Enrolled:</span><span class="font-weight-semibold">{{ course.enrollments?.length || 0 }}</span></div>
          </div>

          <h6 class="text-h6 font-weight-semibold mb-3">Course Content</h6>
          <div class="d-flex flex-column ga-2">
            <div v-for="section in (course.sections || [])" :key="section.id" class="mb-2">
              <div class="text-caption font-weight-bold text-medium-emphasis text-uppercase mb-1">{{ section.title }}</div>
              <VListItem
                v-for="lesson in (section.lessons || [])"
                :key="lesson.id"
                :class="{ 'bg-primary-lighten-5 rounded': selectedLesson?.id === lesson.id }"
                density="compact"
                style="cursor: pointer; border-radius: 6px;"
                @click="selectLesson(lesson)"
              >
                <template #prepend>
                  <VIcon
                    :icon="selectedLesson?.id === lesson.id ? 'mdi-play-circle' : 'mdi-play-box-outline'"
                    size="18"
                    :color="selectedLesson?.id === lesson.id ? 'primary' : 'disabled'"
                    class="mr-2"
                  />
                </template>
                <VListItemTitle class="text-body-2">{{ lesson.title }}</VListItemTitle>
                <template #append>
                  <span class="text-caption text-medium-emphasis d-flex align-center ga-1">
                    <VIcon icon="mdi-video" size="12" color="info" />{{ lesson.videos?.length || 0 }}
                    <VIcon icon="mdi-paperclip" size="12" color="primary" class="ms-1" />{{ lesson.materials?.length || 0 }}
                  </span>
                </template>
              </VListItem>
            </div>
          </div>

          <VAlert color="warning" variant="tonal" density="compact" class="mt-4">
            <div class="text-body-2 font-weight-semibold">Preview Mode</div>
            <div class="text-caption">Students would see the enroll button here.</div>
          </VAlert>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
