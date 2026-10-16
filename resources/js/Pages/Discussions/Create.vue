<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import TiptapEditor from '@core/components/TiptapEditor.vue'

const props = defineProps({
  course: {
    type: Object,
    default: null,
  },
  lesson: {
    type: Object,
    default: null,
  },
  courses: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
})

// Default to course mode if course was passed via query, otherwise general
const isCourseSpecific = ref(!!props.course)

const form = useForm({
  is_general: !props.course,
  course_id: props.course?.id ?? null,
  lesson_id: props.lesson?.id ?? null,
  category_id: null,
  title: '',
  content: '',
})

const onScopeChange = (val) => {
  isCourseSpecific.value = (val === 'course')
  form.is_general = !isCourseSpecific.value
  if (!isCourseSpecific.value) {
    form.course_id = null
    form.lesson_id = null
  }
}

const selectedCourse = computed(() => {
  return props.courses.find(
    course => Number(course.id) === Number(form.course_id)
  ) ?? null
})

const lessonItems = computed(() => {
  if (!selectedCourse.value?.sections) {
    return []
  }

  return selectedCourse.value.sections.flatMap(section =>
    (section.lessons ?? []).map(lesson => ({
      id: lesson.id,
      title: lesson.title,
    }))
  )
})

const isFormValid = computed(() => {
  if (!form.title?.trim() || !form.content?.trim()) return false
  if (isCourseSpecific.value && !form.course_id) return false
  return true
})

const submit = () => {
  form.post(route('discussions.store'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="discussion-create-page">
    <!-- Header Section -->
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div class="d-flex align-center ga-3">
        <Link
          :href="route('discussions.index', { course_id: course?.id })"
          class="text-decoration-none"
        >
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="mdi-arrow-left"
          >
            Kembali ke Diskusi
          </VBtn>
        </Link>
        <div>
          <h4 class="text-h4 font-weight-bold mb-0">Ajukan Pertanyaan / Mulai Diskusi</h4>
          <span class="text-caption text-medium-emphasis">
            Dapatkan solusi dan panduan terverifikasi dari instruktur atau rekan di organisasi Anda.
          </span>
        </div>
      </div>
    </div>

    <!-- Main Form Card -->
    <VCard flat border class="pa-5 pa-md-6 rounded-lg">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <!-- 1. Scope Selection (Interactive Scope Cards) -->
        <div>
          <label class="text-subtitle-2 font-weight-bold d-block mb-2">
            Pilih Ruang Lingkup Diskusi
          </label>
          <VRow>
            <!-- Scope 1: General Q&A -->
            <VCol cols="12" md="6">
              <div
                class="scope-option-card pa-3 rounded border d-flex align-start ga-3 cursor-pointer"
                :class="{ 'active-scope': !isCourseSpecific }"
                @click="onScopeChange('general')"
              >
                <VAvatar
                  size="36"
                  :color="!isCourseSpecific ? 'primary' : 'secondary'"
                  variant="tonal"
                  class="flex-shrink-0 mt-1"
                >
                  <VIcon icon="mdi-forum-outline" size="20" />
                </VAvatar>
                <div>
                  <div class="d-flex align-center ga-2 mb-1">
                    <span class="text-body-2 font-weight-bold">Tanya-Jawab Umum (Non-Kursus)</span>
                    <VChip v-if="!isCourseSpecific" size="x-small" color="primary" variant="elevated">
                      Dipilih
                    </VChip>
                  </div>
                  <span class="text-caption text-medium-emphasis d-block">
                    Diskusikan SOP lapangan, praktik budidaya, atau topik teknis umum tanpa terikat materi kursus.
                  </span>
                </div>
              </div>
            </VCol>

            <!-- Scope 2: Course-specific -->
            <VCol cols="12" md="6">
              <div
                class="scope-option-card pa-3 rounded border d-flex align-start ga-3 cursor-pointer"
                :class="{ 'active-scope': isCourseSpecific }"
                @click="onScopeChange('course')"
              >
                <VAvatar
                  size="36"
                  :color="isCourseSpecific ? 'primary' : 'secondary'"
                  variant="tonal"
                  class="flex-shrink-0 mt-1"
                >
                  <VIcon icon="mdi-book-open-page-variant-outline" size="20" />
                </VAvatar>
                <div>
                  <div class="d-flex align-center ga-2 mb-1">
                    <span class="text-body-2 font-weight-bold">Pertanyaan Terkait Kursus</span>
                    <VChip v-if="isCourseSpecific" size="x-small" color="primary" variant="elevated">
                      Dipilih
                    </VChip>
                  </div>
                  <span class="text-caption text-medium-emphasis d-block">
                    Tautkan pertanyaan langsung ke materi kursus atau kuis tertentu agar dapat dijawab pengajar.
                  </span>
                </div>
              </div>
            </VCol>
          </VRow>
        </div>

        <VDivider class="my-1" />

        <!-- 2. Course & Category Selectors -->
        <VRow>
          <!-- Category Selector -->
          <VCol cols="12" :md="isCourseSpecific ? 4 : 12">
            <div>
              <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                Kategori Topik <span class="text-caption text-medium-emphasis">(Opsional)</span>
              </label>
              <VSelect
                v-model="form.category_id"
                :items="categories"
                item-title="name"
                item-value="id"
                placeholder="Pilih Kategori Pembahasan"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-tag-outline"
                clearable
                hide-details="auto"
              />
            </div>
          </VCol>

          <!-- Course & Lesson Selectors (Visible only when course-specific) -->
          <template v-if="isCourseSpecific">
            <VCol cols="12" md="4">
              <div>
                <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                  Kursus Terkait <span class="text-error">*</span>
                </label>
                <VSelect
                  v-model="form.course_id"
                  :items="courses"
                  item-title="title"
                  item-value="id"
                  placeholder="Pilih Kursus"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-book-multiple-outline"
                  :rules="[v => !!v || 'Kursus wajib dipilih']"
                  :error-messages="form.errors.course_id"
                  hide-details="auto"
                />
              </div>
            </VCol>

            <VCol cols="12" md="4">
              <div>
                <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                  Materi / Pelajaran <span class="text-caption text-medium-emphasis">(Opsional)</span>
                </label>
                <VSelect
                  v-model="form.lesson_id"
                  :items="lessonItems"
                  item-title="title"
                  item-value="id"
                  placeholder="Semua Materi Kursus"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-file-document-outline"
                  clearable
                  :disabled="!form.course_id || lessonItems.length === 0"
                  hide-details="auto"
                />
              </div>
            </VCol>
          </template>
        </VRow>

        <!-- 3. Question Title -->
        <div>
          <label class="text-subtitle-2 font-weight-medium d-block mb-1">
            Judul Pertanyaan / Masalah <span class="text-error">*</span>
          </label>
          <VTextField
            v-model="form.title"
            placeholder="Contoh: Mengapa daun padi menguning setelah 14 HST dan bagaimana penanganannya?"
            variant="outlined"
            density="compact"
            prepend-inner-icon="mdi-format-title"
            :rules="[v => !!v || 'Judul pertanyaan wajib diisi']"
            :error-messages="form.errors.title"
            hide-details="auto"
          />
        </div>

        <!-- 4. Question Content (Rich Text Editor) -->
        <div>
          <div class="d-flex align-center justify-space-between mb-1">
            <label class="text-subtitle-2 font-weight-medium">
              Rincian Pertanyaan / Deskripsi Kondisi <span class="text-error">*</span>
            </label>
            <span class="text-caption text-medium-emphasis">
              Mendukung formatting teks, poin daftar, dan kutipan
            </span>
          </div>

          <div class="rich-editor-box border rounded">
            <TiptapEditor
              v-model="form.content"
              placeholder="Jelaskan detail kendala, gejala di lapangan, atau konteks pertanyaan secara spesifik agar mudah dipahami dan dijawab..."
            />
          </div>

          <span v-if="form.errors.content" class="text-caption text-error mt-1 d-block">
            {{ form.errors.content }}
          </span>
          <span v-else class="text-caption text-medium-emphasis mt-1 d-block">
            Semakin rinci kronologi, lokasi, dan gejala yang diceritakan, semakin akurat solusi yang diberikan.
          </span>
        </div>

        <!-- 5. Form Action Buttons -->
        <div class="d-flex align-center ga-3 pt-3 border-t">
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            size="default"
            prepend-icon="mdi-send"
            :loading="form.processing"
            :disabled="form.processing || !isFormValid"
          >
            Kirim Pertanyaan
          </VBtn>

          <Link
            :href="route('discussions.index', {
              course_id: form.course_id || undefined,
            })"
            class="text-decoration-none"
          >
            <VBtn
              variant="outlined"
              size="default"
              prepend-icon="mdi-close"
            >
              Batal
            </VBtn>
          </Link>
        </div>
      </VForm>
    </VCard>
  </div>
</template>

<style scoped>
.scope-option-card {
  transition: all 0.18s ease-in-out;
  background-color: rgb(var(--v-theme-surface));
}
.scope-option-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.5) !important;
  background-color: rgba(var(--v-theme-primary), 0.02);
}
.active-scope {
  border-color: rgb(var(--v-theme-primary)) !important;
  background-color: rgba(var(--v-theme-primary), 0.05) !important;
}

.rich-editor-box {
  background-color: rgb(var(--v-theme-surface));
  min-height: 220px;
}

.cursor-pointer {
  cursor: pointer;
}

.border-t {
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}
</style>
