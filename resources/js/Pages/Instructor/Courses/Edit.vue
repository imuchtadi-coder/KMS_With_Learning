<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const flash = computed(() => page.props.flash || {})

const props = defineProps({
  course: { type: Object, required: true },
  quizzes: { type: Array, default: () => [] },
  assignments: { type: Array, default: () => [] },
})

const tab = ref('details')

const form = useForm({
  title: props.course.title,
  slug: props.course.slug,
  description: props.course.description,
  level: props.course.level,
  price: props.course.price,
  rating: props.course.rating,
  category_id: props.course.category_id,
  tags: props.course.tags?.map(t => t.id) || [],
  thumbnail: null,
})

function saveCourse() {
  form
    .transform(data => ({
      ...data,
      _method: 'put',
    }))
    .post(route('instructor.courses.update', props.course.id), {
      preserveScroll: true,
      forceFormData: true,

      onSuccess: () => {
        // Reset file input setelah berhasil
        form.reset('thumbnail')
      },

      onError: errors => {
        console.error('Course update error:', errors)
      },
    })
}

const sectionForm = useForm({ title: '' })
const lessonForm = useForm({ title: '', description: '' })
const sectionDialog = ref(false)
const lessonDialog = ref(false)
const editingSection = ref(null)
const editingLesson = ref(null)
const currentSectionId = ref(null)

const contentDialog = ref(false)
const contentLessonId = ref(null)
const uploadingMaterial = ref(false)
const showVideoForm = ref(false)
const videoForm = useForm({
  source_type: 'hls',
  title: '',
  video: null,
  youtube_url: '',
})
const materialFile = ref(null)
const materialTitle = ref('')

const contentLesson = computed(() => {
  if (!contentLessonId.value) return null

  return (props.course.sections || [])
    .flatMap(section => section.lessons || [])
    .find(lesson => lesson.id === contentLessonId.value) || null
})

function openSectionDialog(section) {
  editingSection.value = section || null
  sectionForm.title = section?.title || ''
  sectionDialog.value = true
}

function saveSection() {
  if (editingSection.value) {
    sectionForm.put(route('instructor.sections.update', editingSection.value.id), {
      preserveScroll: true,
      onSuccess: () => { sectionDialog.value = false; sectionForm.reset() },
    })
  } else {
    sectionForm.post(route('instructor.sections.store', props.course.id), {
      preserveScroll: true,
      onSuccess: () => { sectionDialog.value = false; sectionForm.reset() },
    })
  }
}

function deleteSection(section) {
  if (!confirm('Delete this section?')) return
  router.delete(route('instructor.sections.destroy', section.id), { preserveScroll: true })
}

function openLessonDialog(sectionId, lesson) {
  editingLesson.value = lesson || null
  currentSectionId.value = sectionId
  lessonForm.title = lesson?.title || ''
  lessonForm.description = lesson?.description || ''
  lessonDialog.value = true
}

function saveLesson() {
  if (editingLesson.value) {
    lessonForm.put(route('instructor.lessons.update', editingLesson.value.id), {
      preserveScroll: true,
      onSuccess: () => { lessonDialog.value = false; lessonForm.reset() },
    })
  } else {
    lessonForm.post(route('instructor.lessons.store', currentSectionId.value), {
      preserveScroll: true,
      onSuccess: () => { lessonDialog.value = false; lessonForm.reset() },
    })
  }
}

function deleteLesson(lesson) {
  if (!confirm('Delete this lesson?')) return
  router.delete(route('instructor.lessons.destroy', lesson.id), { preserveScroll: true })
}

function openContentDialog(lesson) {
  contentLessonId.value = lesson.id
  resetVideoForm()
  materialTitle.value = ''
  materialFile.value = null
  contentDialog.value = true
}

function resetVideoForm() {
  videoForm.reset()
  videoForm.clearErrors()
  showVideoForm.value = false
}

function isSupportedYouTubeUrl(value) {
  try {
    const url = new URL(value)
    const hosts = new Set([
      'youtube.com', 'www.youtube.com', 'm.youtube.com',
      'youtu.be', 'www.youtu.be',
      'youtube-nocookie.com', 'www.youtube-nocookie.com',
    ])

    if (url.protocol !== 'https:' || !hosts.has(url.hostname) || url.username || url.password || url.port)
      return false

    const segments = url.pathname.split('/').filter(Boolean)
    let videoId = null

    if (url.hostname === 'youtu.be' || url.hostname === 'www.youtu.be')
      videoId = segments.length === 1 ? segments[0] : null
    else if (url.pathname === '/watch')
      videoId = url.searchParams.get('v')
    else if (segments.length === 2 && ['shorts', 'embed'].includes(segments[0]))
      videoId = segments[1]

    return /^[A-Za-z0-9_-]{11}$/.test(videoId || '')
  } catch {
    return false
  }
}

function uploadVideo() {
  videoForm.clearErrors()

  if (videoForm.source_type === 'youtube' && !isSupportedYouTubeUrl(videoForm.youtube_url)) {
    videoForm.setError('youtube_url', 'Masukkan URL YouTube HTTPS yang valid dan berasal dari domain resmi YouTube.')
    showVideoForm.value = true
    return
  }

  videoForm.post(route('instructor.videos.store', contentLesson.value.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: page => {
      const videoErrors = Object.fromEntries(
        Object.entries(page.props.errors || {}).filter(([field]) =>
          ['source_type', 'title', 'video', 'youtube_url'].includes(field),
        ),
      )

      if (Object.keys(videoErrors).length) {
        videoForm.setError(videoErrors)
        showVideoForm.value = true
        return
      }

      // A validation redirect is still an Inertia "success" response. Only
      // close the form after the controller confirms that a video was stored.
      if (!page.props.flash?.success) {
        showVideoForm.value = true
        return
      }

      resetVideoForm()
    },
    onError: errors => {
      videoForm.setError(errors)
      showVideoForm.value = true
    },
  })
}

watch(() => videoForm.source_type, sourceType => {
  videoForm.clearErrors('video', 'youtube_url')

  if (sourceType === 'youtube') {
    videoForm.video = null
  } else {
    videoForm.youtube_url = ''
  }
})

watch(contentDialog, isOpen => {
  if (!isOpen) resetVideoForm()
})

function deleteVideo(video) {
  if (!confirm('Delete this video?')) return
  router.delete(route('instructor.videos.destroy', video.id), {
    preserveScroll: true,
  })
}

function uploadMaterial() {
  if (!materialFile.value || !materialTitle.value) return
  uploadingMaterial.value = true
  const data = new FormData()
  data.append('title', materialTitle.value)
  data.append('file', materialFile.value)
  router.post(route('instructor.materials.store', contentLesson.value.id), data, {
    preserveScroll: true,
    onSuccess: () => {
      uploadingMaterial.value = false
      materialFile.value = null
      materialTitle.value = ''
    },
    onError: () => {
      uploadingMaterial.value = false
    },
  })
}

function deleteMaterial(material) {
  if (!confirm('Delete this material?')) return
  router.delete(route('instructor.materials.destroy', material.id), {
    preserveScroll: true,
  })
}

function formatFileSize(bytes) {
  if (!bytes) return '0 MB'
  return (bytes / 1024 / 1024).toFixed(2) + ' MB'
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-2">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ course.title }}</h4>
        <div class="d-flex align-center ga-2">
          <span class="text-body-2 text-medium-emphasis">Status:</span>
          <VChip :color="course.published ? 'success' : 'warning'" size="small" variant="tonal" label>
            {{ course.published ? 'Published' : 'Draft (Pending Approval)' }}
          </VChip>
        </div>
      </div>
    </div>

    <VAlert v-if="flash.success" type="success" closable class="mb-4">{{ flash.success }}</VAlert>

    <VTabs v-model="tab" color="primary" class="mb-6">
      <VTab value="details">Course Details</VTab>
      <VTab value="sections">Sections & Lessons</VTab>
      <VTab value="assessments">Assessments</VTab>
    </VTabs>

    <VWindow v-model="tab">
      <VWindowItem value="details">
        <VCard flat border class="pa-8">
          <!-- <VForm @submit.prevent="form.put(route('instructor.courses.update', course.id))" class="d-flex flex-column ga-5"> -->
          <VForm @submit.prevent="saveCourse" class="d-flex flex-column ga-5">
            <VTextField v-model="form.title" label="Course Title" variant="outlined" density="default" />
            <VTextField v-model="form.slug" label="Course URL Slug" variant="outlined" density="default" />
            <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="5" />
            <VSelect v-model="form.level" label="Level" variant="outlined" density="default"
              :items="['beginner', 'intermediate', 'advanced'].map(v => ({ title: v.charAt(0).toUpperCase() + v.slice(1), value: v }))" />
            <VTextField v-model="form.price" label="Price" variant="outlined" density="default" type="number" step="0.01" min="0" />
            <VTextField v-model="form.rating" label="Rating" variant="outlined" density="default" type="number" step="0.1" min="0" max="5" />

            <div class="d-flex flex-column ga-3">
              <div v-if="course.thumbnail" class="position-relative rounded overflow-hidden" style="max-width: 320px; height: 140px;">
                <img :src="course.thumbnail" class="w-100 h-100" style="object-fit: cover;" alt="Current thumbnail"/>
                <div class="position-absolute text-caption text-white pa-1" style="bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.5);">
                  Current thumbnail
                </div>
              </div>
              <VFileInput v-model="form.thumbnail" :label="course.thumbnail ? 'Replace thumbnail' : 'Thumbnail Image'" variant="outlined" density="default"
                accept="image/jpg,image/jpeg,image/png,image/webp"
                prepend-icon="mdi-camera"
                hint="Recommended: 1280x720px, max 2MB" persistent-hint
                :rules="[v => !v || v.size <= 2097152 || 'Max 2MB']" hide-details="auto"
                :error-messages="form.errors?.thumbnail"
                @update:model-value="form.clearErrors('thumbnail')" />
            </div>

            <div class="d-flex ga-3 pt-2">
              <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Save Changes</VBtn>
              <VBtn variant="outlined" :href="route('instructor.courses.index')">Cancel</VBtn>
            </div>
          </VForm>
        </VCard>
      </VWindowItem>

      <VWindowItem value="sections">
        <div class="d-flex flex-column ga-4">
          <VBtn color="primary" variant="tonal" prepend-icon="mdi-plus" @click="openSectionDialog(null)" class="align-self-start">
            Add Section
          </VBtn>

          <VCard v-for="section in (course.sections || [])" :key="section.id" flat border class="pa-4">
            <div class="d-flex align-center justify-space-between mb-3">
              <div>
                <h6 class="text-h6 font-weight-semibold">{{ section.title }}</h6>
                <span class="text-caption text-medium-emphasis">{{ section.lessons?.length || 0 }} lessons</span>
              </div>
              <div class="d-flex ga-2">
                <VBtn variant="text" color="primary" size="small" @click="openSectionDialog(section)">Edit</VBtn>
                <VBtn variant="text" color="error" size="small" @click="deleteSection(section)">Delete</VBtn>
              </div>
            </div>
            <div class="ml-4 pl-4 border-l">
              <div v-for="lesson in (section.lessons || [])" :key="lesson.id" class="d-flex align-center justify-space-between pa-3 bg-light-primary rounded mb-2">
                <div class="flex-grow-1" style="min-width: 0;">
                  <div class="font-weight-medium text-body-2">{{ lesson.title }}</div>
                  <div class="text-caption text-medium-emphasis">{{ lesson.description?.substring(0, 50) || '' }}</div>
                  <div class="d-flex align-center ga-2 mt-1">
                    <span class="text-caption text-info">
                      <VIcon icon="mdi-video" size="14" class="me-1" />{{ lesson.videos?.length || 0 }}
                    </span>
                    <span class="text-caption text-primary">
                      <VIcon icon="mdi-paperclip" size="14" class="me-1" />{{ lesson.materials?.length || 0 }}
                    </span>
                  </div>
                </div>
                <div class="d-flex ga-1 flex-shrink-0">
                  <VBtn variant="text" color="info" size="x-small" @click="openContentDialog(lesson)">Manage Content</VBtn>
                  <VBtn variant="text" color="primary" size="x-small" @click="openLessonDialog(section.id, lesson)">Edit</VBtn>
                  <VBtn variant="text" color="error" size="x-small" @click="deleteLesson(lesson)">Delete</VBtn>
                </div>
              </div>
              <VBtn variant="text" color="primary" size="small" class="mt-1" @click="openLessonDialog(section.id, null)">
                + Add Lesson
              </VBtn>
            </div>
          </VCard>

          <VCard v-if="!(course.sections?.length)" flat border class="pa-12 text-center">
            <p class="text-body-1 text-medium-emphasis mb-3">No sections yet</p>
            <VBtn color="primary" variant="elevated" @click="openSectionDialog(null)">Create First Section</VBtn>
          </VCard>
        </div>
      </VWindowItem>

      <VWindowItem value="assessments">
        <div class="d-flex flex-column ga-6">
          <VCard flat border>
            <VCardTitle>Quizzes</VCardTitle>
            <VDataTable :items="quizzes" :headers="[{ title: 'Title', key: 'title' }, { title: 'Section', key: 'section?.title' }, { title: 'Passing Grade', key: 'passing_grade' }, { title: 'Time Limit', key: 'time_limit' }, { title: 'Status', key: 'published' }, { title: 'Actions', key: 'actions' }]" hide-default-footer disable-sort class="text-no-wrap">
              <template #item.passing_grade="{ value }">{{ value }}%</template>
              <template #item.time_limit="{ value }">{{ value }} min</template>
              <template #item.published="{ value }">
                <VChip :color="value ? 'success' : 'warning'" size="x-small" variant="tonal" label>{{ value ? 'Published' : 'Draft' }}</VChip>
              </template>
              <template #item.actions="{ item }">
                <VBtn variant="text" color="primary" size="small" :href="route('instructor.quizzes.show', [course.id, item.id])">Manage</VBtn>
              </template>
            </VDataTable>
            <VCardText v-if="!quizzes.length" class="text-center text-medium-emphasis py-8">
              No quizzes yet. <a :href="route('instructor.quizzes.create', course.id)" class="text-link">Create one</a>.
            </VCardText>
          </VCard>

          <VCard flat border>
            <VCardTitle>Assignments</VCardTitle>
            <VDataTable :items="assignments" :headers="[{ title: 'Title', key: 'title' }, { title: 'Section', key: 'section?.title' }, { title: 'Max Score', key: 'max_score' }, { title: 'Deadline', key: 'deadline' }, { title: 'Actions', key: 'actions' }]" hide-default-footer disable-sort class="text-no-wrap">
              <template #item.deadline="{ value }">{{ value ? new Date(value).toLocaleDateString() : '-' }}</template>
              <template #item.actions="{ item }">
                <VBtn variant="text" color="primary" size="small" :href="route('instructor.assignments.show', [course.id, item.id])">Manage</VBtn>
              </template>
            </VDataTable>
            <VCardText v-if="!assignments.length" class="text-center text-medium-emphasis py-8">
              No assignments yet.
            </VCardText>
          </VCard>
        </div>
      </VWindowItem>
    </VWindow>

    <VCard flat border class="pa-4 mt-4 bg-info-light">
      <VAlert color="info" variant="tonal" density="compact">
        <div class="font-weight-semibold">Course Approval</div>
        <p class="text-body-2">This course is currently in <strong>{{ course.published ? 'published' : 'draft' }}</strong> state. Your tenant admin must review and approve the course before it becomes visible to students.</p>
      </VAlert>
    </VCard>

    <VDialog v-model="sectionDialog" max-width="500">
      <VCard>
        <VCardTitle>{{ editingSection ? 'Edit Section' : 'Add Section' }}</VCardTitle>
        <VCardText>
          <VForm @submit.prevent="saveSection" class="d-flex flex-column ga-4">
            <VTextField v-model="sectionForm.title" label="Section Title" variant="outlined" density="default" :rules="[v => !!v || 'Title is required']" hide-details="auto" />
            <div class="d-flex ga-3">
              <VBtn type="submit" color="primary" variant="elevated" :loading="sectionForm.processing">{{ editingSection ? 'Update' : 'Create' }} Section</VBtn>
              <VBtn variant="outlined" @click="sectionDialog = false">Cancel</VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog v-model="lessonDialog" max-width="600">
      <VCard>
        <VCardTitle>{{ editingLesson ? 'Edit Lesson' : 'Add Lesson' }}</VCardTitle>
        <VCardText>
          <VForm @submit.prevent="saveLesson" class="d-flex flex-column ga-4">
            <VTextField v-model="lessonForm.title" label="Lesson Title" variant="outlined" density="default" :rules="[v => !!v || 'Title is required']" hide-details="auto" />
            <VTextarea v-model="lessonForm.description" label="Description (Optional)" variant="outlined" density="default" rows="3" hide-details="auto" />
            <div class="d-flex ga-3">
              <VBtn type="submit" color="primary" variant="elevated" :loading="lessonForm.processing">{{ editingLesson ? 'Update' : 'Create' }} Lesson</VBtn>
              <VBtn variant="outlined" @click="lessonDialog = false">Cancel</VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>

    <VDialog v-model="contentDialog" max-width="640" scrollable>
      <VCard v-if="contentLesson">
        <VCardTitle>
          <div class="d-flex align-center ga-2">
            <VIcon icon="mdi-play-box-multiple" />
            <span>Lesson Content: {{ contentLesson.title }}</span>
          </div>
        </VCardTitle>
        <VDivider />
        <VCardText class="pt-4">
          <div class="d-flex flex-column ga-6">
            <!-- Videos Section -->
            <div>
              <h6 class="text-h6 font-weight-semibold mb-3 d-flex align-center">
                <VIcon icon="mdi-video" class="me-2" color="info" />
                Videos
              </h6>
              <div v-if="contentLesson.videos?.length" class="d-flex flex-column ga-2 mb-3">
                <VCard v-for="video in contentLesson.videos" :key="video.id" variant="outlined" class="pa-3">
                  <div class="d-flex align-center ga-3">
                    <VAvatar rounded variant="tonal" color="info" size="40">
                      <VIcon :icon="video.source_type === 'youtube' ? 'mdi-youtube' : 'mdi-filmstrip'" size="24" />
                    </VAvatar>
                    <div class="flex-grow-1" style="min-width: 0;">
                      <div class="font-weight-medium text-body-2 text-truncate">{{ video.title }}</div>
                      <div class="d-flex align-center ga-1 mt-1 flex-wrap">
                        <VChip :color="video.status === 'ready' ? 'success' : 'warning'" size="x-small" variant="tonal" label>
                          {{ video.status }}
                        </VChip>
                        <VChip :color="video.source_type === 'youtube' ? 'error' : 'info'" size="x-small" variant="tonal" label>
                          <VIcon start :icon="video.source_type === 'youtube' ? 'mdi-youtube' : 'mdi-upload'" size="12" />
                          {{ video.source_type === 'youtube' ? 'YouTube' : 'Upload' }}
                        </VChip>
                      </div>
                    </div>
                    <VBtn variant="text" color="error" size="x-small" icon @click="deleteVideo(video)">
                      <VIcon icon="mdi-delete" size="20" />
                    </VBtn>
                  </div>
                </VCard>
              </div>
              <div v-else class="text-body-2 text-medium-emphasis mb-3">
                <VIcon icon="mdi-video-off" size="20" class="me-1" />Belum ada materi video.
              </div>
              <VExpandTransition>
                <VForm v-if="showVideoForm" @submit.prevent="uploadVideo" class="d-flex flex-column ga-3 pa-3 bg-white rounded border">
                  <div>
                    <div class="text-body-2 font-weight-medium mb-2">Sumber video</div>
                    <VBtnToggle v-model="videoForm.source_type" color="info" variant="outlined" density="comfortable" mandatory divided class="w-100">
                      <VBtn value="hls" class="flex-grow-1">
                        <VIcon start icon="mdi-upload" />Unggah Video
                      </VBtn>
                      <VBtn value="youtube" class="flex-grow-1">
                        <VIcon start icon="mdi-youtube" />Tautan YouTube
                      </VBtn>
                    </VBtnToggle>
                    <div v-if="videoForm.errors.source_type" class="text-error text-caption mt-1">{{ videoForm.errors.source_type }}</div>
                  </div>

                  <VTextField v-model="videoForm.title" label="Judul Video" variant="outlined" density="compact"
                    :error-messages="videoForm.errors.title" hide-details="auto"
                    @update:model-value="videoForm.clearErrors('title')" />

                  <VFileInput v-if="videoForm.source_type === 'hls'" v-model="videoForm.video" label="File Video" variant="outlined" density="compact"
                    accept="video/mp4,video/mov,video/avi,video/mkv,video/webm"
                    prepend-icon="mdi-upload"
                    hint="MP4, MOV, AVI, MKV, atau WebM; maksimal 2 GB. Video akan diproses menjadi HLS."
                    persistent-hint :error-messages="videoForm.errors.video" hide-details="auto"
                    @update:model-value="videoForm.clearErrors('video')" />

                  <div v-else class="d-flex flex-column ga-2">
                    <VTextField v-model="videoForm.youtube_url" label="URL YouTube" variant="outlined" density="compact"
                      placeholder="https://www.youtube.com/watch?v=..." prepend-inner-icon="mdi-youtube"
                      :error-messages="videoForm.errors.youtube_url" hide-details="auto"
                      @update:model-value="videoForm.clearErrors('youtube_url')" />
                    <VAlert color="info" variant="tonal" density="compact" icon="mdi-information-outline">
                      <div class="text-caption">
                        Gunakan video <strong>public</strong> atau <strong>unlisted</strong> yang mengizinkan pemutaran melalui embed. Video tidak akan diunduh ke server KITsuluh.
                      </div>
                    </VAlert>
                  </div>

                  <div class="d-flex ga-2 flex-wrap">
                    <VBtn type="button" color="info" variant="elevated" size="small" :loading="videoForm.processing" @click="uploadVideo">
                      <VIcon start :icon="videoForm.source_type === 'youtube' ? 'mdi-link-plus' : 'mdi-upload'" size="16" />
                      {{ videoForm.source_type === 'youtube' ? 'Tambahkan Video' : 'Unggah Video' }}
                    </VBtn>
                    <VBtn variant="text" size="small" :disabled="videoForm.processing" @click="resetVideoForm">Batal</VBtn>
                  </div>
                </VForm>
              </VExpandTransition>
              <VBtn v-if="!showVideoForm" variant="tonal" color="info" size="small" class="mt-2" @click="showVideoForm = true">
                <VIcon start icon="mdi-plus" size="16" />Tambah Video
              </VBtn>
            </div>

            <VDivider />

            <!-- Materials Section -->
            <div>
              <h6 class="text-h6 font-weight-semibold mb-3 d-flex align-center">
                <VIcon icon="mdi-paperclip" class="me-2" color="primary" />
                Materials
              </h6>
              <div v-if="contentLesson.materials?.length" class="d-flex flex-column ga-2 mb-3">
                <VCard v-for="material in contentLesson.materials" :key="material.id" variant="outlined" class="pa-3">
                  <div class="d-flex align-center ga-3">
                    <VAvatar rounded variant="tonal" color="primary" size="40">
                      <VIcon icon="mdi-file-document-outline" size="24" />
                    </VAvatar>
                    <div class="flex-grow-1" style="min-width: 0;">
                      <div class="font-weight-medium text-body-2 text-truncate">{{ material.title }}</div>
                      <div class="text-caption text-medium-emphasis">{{ material.file_type?.toUpperCase() || '' }} &bull; {{ formatFileSize(material.file_size) }}</div>
                    </div>
                    <VBtn variant="text" color="error" size="x-small" icon @click="deleteMaterial(material)">
                      <VIcon icon="mdi-delete" size="20" />
                    </VBtn>
                  </div>
                </VCard>
              </div>
              <div v-else class="text-body-2 text-medium-emphasis mb-3">
                <VIcon icon="mdi-file-document-off" size="20" class="me-1" />No materials uploaded yet.
              </div>
              <VExpandTransition>
                <VForm v-show="!!materialFile || !!materialTitle" @submit.prevent="uploadMaterial" class="d-flex flex-column ga-2 pa-3 bg-white rounded">
                  <VTextField v-model="materialTitle" label="Material Title" variant="outlined" density="compact" hide-details="auto" />
                  <VFileInput v-model="materialFile" label="File" variant="outlined" density="compact"
                    accept=".pdf,.pptx,.ppt,.docx,.doc"
                    prepend-icon="mdi-upload"
                    hint="PDF, PPT, DOC (max 50MB)" persistent-hint
                    hide-details="auto" />
                  <div class="d-flex ga-2">
                    <VBtn type="submit" color="primary" variant="elevated" size="small" :loading="uploadingMaterial">
                      <VIcon start icon="mdi-upload" size="16" />Upload Material
                    </VBtn>
                    <VBtn variant="text" size="small" @click="materialFile = null; materialTitle = ''">Cancel</VBtn>
                  </div>
                </VForm>
              </VExpandTransition>
              <VBtn v-if="!materialFile && !materialTitle" variant="tonal" color="primary" size="small" class="mt-2" @click="materialTitle = ' '; materialTitle = ''">
                <VIcon start icon="mdi-plus" size="16" />Add Material
              </VBtn>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
