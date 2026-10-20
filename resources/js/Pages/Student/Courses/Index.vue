<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  courses: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
  tenantName: { type: String, default: '' },
  filters: { type: Object, default: () => ({}) },
  stats: {
    type: Object,
    default: () => ({
      total_courses: 0,
      enrolled_courses: 0,
      categories_count: 0,
    }),
  },
})

const levelOptions = [
  { title: 'Semua Level', value: '' },
  { title: 'Pemula (Beginner)', value: 'beginner' },
  { title: 'Menengah (Intermediate)', value: 'intermediate' },
  { title: 'Lanjutan (Advanced)', value: 'advanced' },
]

const page = ref(props.courses.current_page ?? 1)
const isEnrolling = ref(null)

const filters = reactive({
  search: props.filters.search ?? '',
  level: props.filters.level ?? '',
  category_id: props.filters.category_id ?? '',
  tag_id: props.filters.tag_id ?? '',
})

let debounceTimer = null
function debouncedSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(applyFilters, 400)
}

function applyFilters() {
  page.value = 1
  router.get(route('student.courses.list'), filters, {
    preserveState: true,
    replace: true,
  })
}

function clearFilters() {
  filters.search = ''
  filters.level = ''
  filters.category_id = ''
  filters.tag_id = ''
  applyFilters()
}

function changePage(newPage) {
  router.get(route('student.courses.list'), { ...filters, page: newPage }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  })
}

function enroll(courseId) {
  isEnrolling.value = courseId
  router.post(route('student.courses.enroll', courseId), {}, {
    preserveScroll: true,
    onFinish: () => {
      isEnrolling.value = null
    },
  })
}

function levelColor(level) {
  const map = {
    beginner: 'success',
    pemula: 'success',
    intermediate: 'warning',
    menengah: 'warning',
    advanced: 'error',
    lanjutan: 'error',
  }
  return map[level?.toLowerCase()] ?? 'primary'
}

function formatLevel(level) {
  if (!level) return 'Semua Level'
  const map = {
    beginner: 'Pemula',
    intermediate: 'Menengah',
    advanced: 'Lanjutan',
  }
  return map[level.toLowerCase()] ?? level
}

function formatPrice(price) {
  return Number(price).toLocaleString('id-ID')
}
</script>

<template>
  <div class="explore-courses-page pb-12">
    <!-- Hero Header Banner -->
    <VCard flat border class="mb-5 rounded-lg overflow-hidden bg-surface">
      <div class="pa-4 pa-md-5">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-3">
          <div class="d-flex align-center ga-3">
            <VAvatar
              size="44"
              color="primary"
              variant="tonal"
              rounded="lg"
              class="elevation-1"
            >
              <VIcon icon="mdi-compass-outline" size="24" color="primary" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h6 font-weight-bold text-high-emphasis mb-0">
                  Jelajahi Kursus
                </h4>
                <VChip size="x-small" color="primary" variant="tonal" label class="font-weight-medium">
                  Katalog Terbuka
                </VChip>
              </div>
              <p class="text-caption text-medium-emphasis mt-0-5 mb-0">
                Temukan materi dan kurikulum terbaik untuk mengembangkan kompetensi profesional Anda.
              </p>
            </div>
          </div>

          <!-- Quick Navigation Pill Tabs -->
          <div class="d-flex align-center ga-1 bg-background pa-1 rounded-pill border">
            <VBtn
              size="small"
              density="comfortable"
              variant="flat"
              color="primary"
              rounded="pill"
              class="text-capitalize px-3"
              :href="route('student.courses.list')"
            >
              <VIcon start icon="mdi-compass-outline" size="16" />
              Semua Kursus
            </VBtn>
            <VBtn
              size="small"
              density="comfortable"
              variant="text"
              color="default"
              rounded="pill"
              class="text-capitalize px-3"
              :href="route('student.enrollments')"
            >
              <VIcon start icon="mdi-school-outline" size="16" />
              Kursus Saya
              <VBadge
                v-if="stats.enrolled_courses > 0"
                :content="stats.enrolled_courses"
                color="primary"
                inline
                class="ms-1"
              />
            </VBtn>
          </div>
        </div>

        <!-- Quick Stats Overview -->
        <VDivider class="my-3-5" />

        <VRow dense>
          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-2-5 px-3 rounded-lg bg-background border h-100">
              <VAvatar color="primary" variant="tonal" size="36" rounded="lg">
                <VIcon icon="mdi-book-multiple-outline" size="18" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium" style="font-size: 0.72rem !important;">Total Kursus Tersedia</div>
                <div class="text-subtitle-1 font-weight-bold text-high-emphasis lh-1">
                  {{ stats.total_courses || courses.total || 0 }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-2-5 px-3 rounded-lg bg-background border h-100">
              <VAvatar color="warning" variant="tonal" size="36" rounded="lg">
                <VIcon icon="mdi-school-outline" size="18" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium" style="font-size: 0.72rem !important;">Kursus Diikuti</div>
                <div class="text-subtitle-1 font-weight-bold text-warning lh-1">
                  {{ stats.enrolled_courses || 0 }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-2-5 px-3 rounded-lg bg-background border h-100">
              <VAvatar color="info" variant="tonal" size="36" rounded="lg">
                <VIcon icon="mdi-shape-outline" size="18" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium" style="font-size: 0.72rem !important;">Kategori Pilihan</div>
                <div class="text-subtitle-1 font-weight-bold text-info lh-1">
                  {{ stats.categories_count || categories.length || 0 }}
                </div>
              </div>
            </div>
          </VCol>
        </VRow>
      </div>
    </VCard>

    <!-- Toolbar: Search & Filters -->
    <VCard flat border class="mb-5 rounded-lg pa-3 pa-md-3-5 bg-surface">
      <VRow dense align="center" class="ga-y-2">
        <!-- Search Input -->
        <VCol cols="12" md="4">
          <VTextField
            v-model="filters.search"
            placeholder="Cari judul kursus atau materi..."
            variant="outlined"
            density="compact"
            prepend-inner-icon="mdi-magnify"
            hide-details
            clearable
            @update:model-value="debouncedSearch"
            @click:clear="clearFilters"
          />
        </VCol>

        <!-- Level Filter -->
        <VCol cols="12" sm="4" md="2">
          <VSelect
            v-model="filters.level"
            :items="levelOptions"
            placeholder="Semua Level"
            variant="outlined"
            density="compact"
            hide-details
            clearable
            @update:model-value="applyFilters"
          />
        </VCol>

        <!-- Category Filter -->
        <VCol cols="12" sm="4" md="3">
          <VSelect
            v-model="filters.category_id"
            :items="[
              { id: '', name: 'Semua Kategori' },
              ...categories
            ]"
            item-title="name"
            item-value="id"
            placeholder="Semua Kategori"
            variant="outlined"
            density="compact"
            hide-details
            clearable
            @update:model-value="applyFilters"
          />
        </VCol>

        <!-- Tag Filter (If tags exist) -->
        <VCol v-if="tags.length > 0" cols="12" sm="4" md="2">
          <VSelect
            v-model="filters.tag_id"
            :items="[
              { id: '', name: 'Semua Tag' },
              ...tags
            ]"
            item-title="name"
            item-value="id"
            placeholder="Semua Tag"
            variant="outlined"
            density="compact"
            hide-details
            clearable
            @update:model-value="applyFilters"
          />
        </VCol>

        <!-- Reset Button -->
        <VCol cols="12" sm="4" :md="tags.length > 0 ? 1 : 3" class="d-flex justify-end">
          <VBtn
            variant="tonal"
            color="default"
            density="compact"
            block
            prepend-icon="mdi-refresh"
            @click="clearFilters"
          >
            Reset
          </VBtn>
        </VCol>
      </VRow>
    </VCard>

    <!-- Empty State -->
    <VRow v-if="courses.data.length === 0">
      <VCol cols="12">
        <VCard flat border class="text-center pa-10 rounded-lg bg-surface">
          <VAvatar size="64" color="primary" variant="tonal" class="mb-3">
            <VIcon icon="mdi-book-search-outline" size="36" color="primary" />
          </VAvatar>
          <h5 class="text-subtitle-1 font-weight-bold mb-2">
            Tidak Ditemukan Kursus
          </h5>
          <p class="text-body-2 text-medium-emphasis mx-auto mb-5" style="max-width: 440px;">
            Tidak ada kursus yang cocok dengan kriteria pencarian Anda. Silakan coba sesuaikan kata kunci atau bersihkan filter.
          </p>
          <VBtn
            variant="tonal"
            color="primary"
            density="comfortable"
            prepend-icon="mdi-refresh"
            @click="clearFilters"
          >
            Bersihkan Filter
          </VBtn>
        </VCard>
      </VCol>
    </VRow>

    <!-- Courses Grid -->
    <VRow v-else>
      <VCol
        v-for="course in courses.data"
        :key="course.id"
        cols="12"
        sm="6"
        md="4"
        xl="3"
      >
        <VCard
          flat
          border
          class="h-100 d-flex flex-column rounded-lg course-card transition-swing overflow-hidden bg-surface"
        >
          <!-- Thumbnail Cover -->
          <div class="course-card-cover position-relative">
            <a
              :href="route('student.courses.show', course.slug || course.id)"
              class="text-decoration-none d-block w-100 position-relative"
            >
              <VImg
                :src="course.thumbnail || course.tutorImg"
                cover
                height="140"
                class="w-100 bg-grey-darken-4 course-cover-img"
              >
                <template #placeholder>
                  <div class="d-flex align-center justify-center fill-height bg-grey-darken-3">
                    <VIcon icon="mdi-school-outline" size="38" color="grey-lighten-1" />
                  </div>
                </template>
              </VImg>
            </a>

            <!-- Ambient Gradient Overlay for Badge Contrast -->
            <div
              class="position-absolute w-100 h-100"
              style="inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.02) 45%, rgba(0,0,0,0.45) 100%); pointer-events: none;"
            />

            <!-- Overlay Badges -->
            <div class="pa-2-5 d-flex justify-space-between align-start position-absolute w-100" style="top: 0; left: 0; z-index: 2; pointer-events: none;">
              <VChip
                size="x-small"
                variant="flat"
                color="primary"
                label
                class="font-weight-semibold shadow-sm text-truncate"
                style="max-width: 135px;"
              >
                {{ course.category || course.tags || 'Umum' }}
              </VChip>

              <div class="d-flex flex-column align-end ga-1">
                <VChip
                  :color="levelColor(course.level)"
                  size="x-small"
                  variant="flat"
                  label
                  class="font-weight-bold shadow-sm"
                >
                  {{ formatLevel(course.level) }}
                </VChip>

                <VChip
                  v-if="course.isEnrolled ?? course.enrolled"
                  size="x-small"
                  color="success"
                  variant="flat"
                  label
                  class="font-weight-bold shadow-sm"
                >
                  <VIcon start icon="mdi-check-circle" size="12" />
                  Terdaftar
                </VChip>
              </div>
            </div>
          </div>

          <!-- Card Content Body -->
          <VCardText class="pa-3-5 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <!-- Rating & Metadata Row -->
              <div class="d-flex align-center justify-space-between mb-1-5 flex-wrap ga-1">
                <div class="d-flex align-center ga-1 text-caption">
                  <VIcon icon="mdi-star" color="warning" size="14" />
                  <span class="font-weight-bold text-high-emphasis" style="font-size: 0.76rem !important;">{{ Number(course.rating || 0).toFixed(1) }}</span>
                  <span class="text-medium-emphasis" style="font-size: 0.72rem !important;">({{ course.ratingCount || course.reviews_count || 0 }})</span>
                </div>

                <div class="d-flex align-center ga-1-5 text-caption text-medium-emphasis" style="font-size: 0.72rem !important;">
                  <span class="d-flex align-center ga-1">
                    <VIcon icon="mdi-clock-outline" size="13" />
                    {{ course.time || course.length || 'Fleksibel' }}
                  </span>
                  <template v-if="course.totalTasks || course.note">
                    <span class="text-disabled">•</span>
                    <span class="d-flex align-center ga-1">
                      <VIcon icon="mdi-book-open-page-variant-outline" size="13" />
                      {{ course.totalTasks || course.note }} Materi
                    </span>
                  </template>
                </div>
              </div>

              <!-- Title -->
              <h5
                class="font-weight-bold text-high-emphasis mb-1-5 course-title"
                :title="course.courseTitle ?? course.title"
                style="font-size: 0.925rem !important; line-height: 1.35; height: 2.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
              >
                <a
                  :href="route('student.courses.show', course.slug || course.id)"
                  class="text-decoration-none text-high-emphasis course-hover-title"
                >
                  {{ course.courseTitle ?? course.title }}
                </a>
              </h5>

              <!-- Description -->
              <p
                class="text-caption text-medium-emphasis mb-2-5"
                style="font-size: 0.775rem !important; line-height: 1.25; height: 2.15rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
              >
                {{ course.desc ?? course.description ?? 'Pelajari materi ini untuk meningkatkan keahlian Anda.' }}
              </p>
            </div>

            <!-- Instructor Line & Students Count -->
            <div class="d-flex align-center justify-space-between ga-2 pt-2 border-t-subtle mt-auto">
              <div class="d-flex align-center ga-1-5 text-truncate flex-grow-1" style="min-width: 0;">
                <VAvatar size="20" color="primary" variant="tonal" class="font-weight-bold flex-shrink-0" style="font-size: 0.65rem !important; min-width: 20px;">
                  <VImg v-if="course.instructorAvatar && !course.instructorAvatar.includes('ui-avatars')" :src="course.instructorAvatar" />
                  <span v-else>{{ (course.instructorName ?? course.instructor?.name ?? 'I').charAt(0) }}</span>
                </VAvatar>
                <span class="text-caption text-medium-emphasis text-truncate font-weight-medium" style="font-size: 0.76rem !important;">
                  {{ course.instructorName ?? course.instructor?.name ?? 'Instruktur' }}
                </span>
              </div>

              <span class="text-caption text-medium-emphasis d-flex align-center ga-1 flex-shrink-0" style="font-size: 0.72rem !important;">
                <VIcon icon="mdi-account-group-outline" size="13" />
                {{ course.totalStudents || course.enrollmentCount || course.enrollments_count || 0 }}
              </span>
            </div>
          </VCardText>

          <VDivider />

          <!-- Card Actions Footer -->
          <VCardActions class="pa-2-5 px-3-5 bg-card-footer">
            <div class="d-flex align-center justify-space-between w-100 ga-2">
              <!-- Price display -->
              <div class="flex-shrink-0">
                <span
                  v-if="course.price > 0"
                  class="text-subtitle-2 font-weight-bold text-primary"
                  style="font-size: 0.875rem !important;"
                >
                  Rp {{ formatPrice(course.price) }}
                </span>
                <VChip
                  v-else
                  size="x-small"
                  color="success"
                  variant="tonal"
                  label
                  class="font-weight-bold"
                >
                  Gratis
                </VChip>
              </div>

              <div class="d-flex align-center ga-1-5">
                <!-- If Enrolled: Go to learn player -->
                <VBtn
                  v-if="course.isEnrolled ?? course.enrolled"
                  color="primary"
                  variant="tonal"
                  size="small"
                  density="comfortable"
                  class="font-weight-medium px-3 text-capitalize"
                  :href="route('student.enrollments.show', course.enrollment_id ?? course.id)"
                >
                  <VIcon start icon="mdi-play-circle" size="15" />
                  Lanjut
                </VBtn>

                <!-- If Not Enrolled: Instant Enroll -->
                <VBtn
                  v-else
                  color="primary"
                  variant="elevated"
                  size="small"
                  density="comfortable"
                  class="font-weight-medium px-3 text-capitalize"
                  :loading="isEnrolling === course.id"
                  @click="enroll(course.id)"
                >
                  <VIcon start icon="mdi-book-plus-outline" size="15" />
                  Daftar
                </VBtn>

                <!-- View Details -->
                <VBtn
                  variant="tonal"
                  color="default"
                  size="small"
                  density="comfortable"
                  icon
                  rounded="lg"
                  title="Lihat Detail Kursus"
                  :href="route('student.courses.show', course.slug || course.id)"
                >
                  <VIcon icon="mdi-arrow-right" size="15" />
                </VBtn>
              </div>
            </div>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <!-- Pagination -->
    <div
      v-if="courses.last_page > 1"
      class="mt-6 d-flex justify-center"
    >
      <VPagination
        v-model="page"
        :length="courses.last_page"
        :total-visible="5"
        rounded="circle"
        density="comfortable"
        active-color="primary"
        @update:model-value="changePage"
      />
    </div>
  </div>
</template>

<style scoped>
.course-card {
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}
.course-card:hover {
  transform: translateY(-2px);
  border-color: rgba(var(--v-theme-primary), 0.45) !important;
  box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.09) !important;
}

.course-card-cover {
  position: relative;
  overflow: hidden;
}

.course-cover-img :deep(.v-img__img) {
  transition: transform 0.35s ease;
}
.course-card:hover .course-cover-img :deep(.v-img__img) {
  transform: scale(1.05);
}

.course-hover-title {
  transition: color 0.15s ease;
}
.course-hover-title:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

.border-t-subtle {
  border-top: 1px dashed rgba(var(--v-border-color), var(--v-border-opacity) * 0.7);
}

.bg-card-footer {
  background-color: rgba(var(--v-theme-on-surface), 0.02);
}

.pa-2-5 {
  padding: 10px !important;
}
.pa-3-5 {
  padding: 14px !important;
}
.px-3-5 {
  padding-left: 14px !important;
  padding-right: 14px !important;
}
.my-3-5 {
  margin-top: 14px !important;
  margin-bottom: 14px !important;
}
.mb-1-5 {
  margin-bottom: 6px !important;
}
.mb-2-5 {
  margin-bottom: 10px !important;
}
.mt-0-5 {
  margin-top: 2px !important;
}
.ga-1-5 {
  gap: 6px !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
