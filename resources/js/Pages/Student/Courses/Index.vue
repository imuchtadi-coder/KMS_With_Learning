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
    <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
      <div class="pa-6 pa-md-8">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-4">
          <div class="d-flex align-center ga-4">
            <VAvatar
              size="56"
              color="primary"
              variant="tonal"
              rounded="lg"
              class="elevation-1"
            >
              <VIcon icon="mdi-compass-outline" size="32" color="primary" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h4 font-weight-bold text-high-emphasis mb-0">
                  Jelajahi Kursus
                </h4>
                <VChip size="small" color="primary" variant="tonal" label class="font-weight-medium">
                  Katalog Terbuka
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                Temukan materi dan kurikulum terbaik untuk mengembangkan kompetensi profesional Anda.
              </p>
            </div>
          </div>

          <!-- Quick Navigation Pill Tabs -->
          <div class="d-flex align-center ga-2 bg-background pa-1 rounded-pill border">
            <VBtn
              size="small"
              variant="flat"
              color="primary"
              rounded="pill"
              class="text-capitalize px-4"
              :href="route('student.courses.list')"
            >
              <VIcon start icon="mdi-compass-outline" size="18" />
              Semua Kursus
            </VBtn>
            <VBtn
              size="small"
              variant="text"
              color="default"
              rounded="pill"
              class="text-capitalize px-4"
              :href="route('student.enrollments')"
            >
              <VIcon start icon="mdi-school-outline" size="18" />
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
        <VDivider class="my-6" />

        <VRow>
          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="primary" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-book-multiple-outline" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Total Kursus Tersedia</div>
                <div class="text-h6 font-weight-bold text-high-emphasis">
                  {{ stats.total_courses || courses.total || 0 }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="warning" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-school-outline" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Kursus Diikuti</div>
                <div class="text-h6 font-weight-bold text-warning">
                  {{ stats.enrolled_courses || 0 }}
                </div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="info" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-shape-outline" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Kategori Pilihan</div>
                <div class="text-h6 font-weight-bold text-info">
                  {{ stats.categories_count || categories.length || 0 }}
                </div>
              </div>
            </div>
          </VCol>
        </VRow>
      </div>
    </VCard>

    <!-- Toolbar: Search & Filters -->
    <VCard flat border class="mb-6 rounded-xl pa-4 bg-surface">
      <VRow dense align="center" class="ga-y-3">
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
            density="comfortable"
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
        <VCard flat border class="text-center pa-12 rounded-xl bg-surface">
          <VAvatar size="72" color="primary" variant="tonal" class="mb-4">
            <VIcon icon="mdi-book-search-outline" size="40" color="primary" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">
            Tidak Ditemukan Kursus
          </h5>
          <p class="text-body-2 text-medium-emphasis mx-auto mb-6" style="max-width: 460px;">
            Tidak ada kursus yang cocok dengan kriteria pencarian Anda. Silakan coba sesuaikan kata kunci atau bersihkan filter.
          </p>
          <VBtn
            variant="tonal"
            color="primary"
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
      >
        <VCard
          flat
          border
          class="h-100 d-flex flex-column rounded-xl course-card transition-swing overflow-hidden bg-surface"
        >
          <!-- Thumbnail Cover -->
          <div class="course-cover position-relative">
            <a
              :href="route('student.courses.show', course.slug || course.id)"
              class="text-decoration-none d-block w-100 position-relative"
            >
              <VImg
                :src="course.thumbnail || course.tutorImg"
                cover
                height="180"
                class="w-100 bg-grey-darken-4"
              >
                <template #placeholder>
                  <div class="d-flex align-center justify-center fill-height bg-grey-darken-3">
                    <VIcon icon="mdi-school-outline" size="48" color="grey-lighten-1" />
                  </div>
                </template>
              </VImg>
            </a>

            <!-- Overlay Badges -->
            <div class="pa-3 d-flex justify-space-between align-start position-absolute w-100" style="top: 0; left: 0; pointer-events: none;">
              <VChip
                size="small"
                variant="flat"
                color="primary"
                label
                class="font-weight-medium shadow-sm"
              >
                {{ course.category || course.tags || 'Umum' }}
              </VChip>

              <div class="d-flex flex-column align-end ga-1">
                <VChip
                  :color="levelColor(course.level)"
                  size="small"
                  variant="flat"
                  label
                  class="font-weight-bold shadow-sm"
                >
                  {{ formatLevel(course.level) }}
                </VChip>

                <VChip
                  v-if="course.isEnrolled ?? course.enrolled"
                  size="small"
                  color="success"
                  variant="flat"
                  label
                  class="font-weight-bold shadow-sm"
                >
                  <VIcon start icon="mdi-check-circle" size="14" />
                  Terdaftar
                </VChip>
              </div>
            </div>
          </div>

          <!-- Card Content Body -->
          <VCardText class="pa-5 flex-grow-1 d-flex flex-column">
            <!-- Rating & Duration -->
            <div class="d-flex justify-space-between align-center mb-2">
              <div class="d-flex align-center ga-1 text-caption">
                <VIcon icon="mdi-star" color="warning" size="16" />
                <span class="font-weight-bold text-high-emphasis">{{ Number(course.rating || 0).toFixed(1) }}</span>
                <span class="text-medium-emphasis">({{ course.ratingCount || course.reviews_count || 0 }})</span>
              </div>

              <span class="text-caption text-medium-emphasis d-flex align-center ga-1">
                <VIcon icon="mdi-clock-outline" size="14" />
                {{ course.time || course.length || 'Akses Fleksibel' }}
              </span>
            </div>

            <!-- Title -->
            <h5
              class="text-subtitle-1 font-weight-bold text-high-emphasis mb-2 line-clamp-2 course-title"
              :title="course.courseTitle ?? course.title"
            >
              <a
                :href="route('student.courses.show', course.slug || course.id)"
                class="text-decoration-none text-high-emphasis"
              >
                {{ course.courseTitle ?? course.title }}
              </a>
            </h5>

            <!-- Description -->
            <p class="text-body-2 text-medium-emphasis mb-3 line-clamp-2" style="min-height: 40px;">
              {{ course.desc ?? course.description ?? 'Pelajari modul komprehensif ini untuk meningkatkan pemahaman keahlian Anda.' }}
            </p>

            <!-- Instructor Line -->
            <div class="d-flex align-center ga-2 mt-auto pt-2">
              <VAvatar size="24" :image="course.instructorAvatar">
                <span class="text-caption font-weight-bold">{{ (course.instructorName ?? course.instructor?.name ?? 'I').charAt(0) }}</span>
              </VAvatar>
              <span class="text-caption text-medium-emphasis text-truncate flex-grow-1">
                {{ course.instructorName ?? course.instructor?.name ?? 'Instruktur' }}
              </span>

              <span class="text-caption text-medium-emphasis d-flex align-center ga-1">
                <VIcon icon="mdi-account-group-outline" size="14" />
                {{ course.totalStudents || course.enrollmentCount || course.enrollments_count || 0 }}
              </span>
            </div>
          </VCardText>

          <VDivider />

          <!-- Card Actions Footer -->
          <VCardActions class="pa-4 bg-background">
            <div class="text-h6 font-weight-bold me-auto text-high-emphasis">
              {{ course.price > 0 ? `Rp ${formatPrice(course.price)}` : 'Gratis' }}
            </div>

            <div class="d-flex ga-2">
              <!-- If Enrolled: Go to learn player -->
              <VBtn
                v-if="course.isEnrolled ?? course.enrolled"
                color="primary"
                variant="tonal"
                size="small"
                class="font-weight-medium"
                :href="route('student.enrollments.show', course.enrollment_id ?? course.id)"
              >
                <VIcon start icon="mdi-play-circle" size="16" />
                Lanjut Belajar
              </VBtn>

              <!-- If Not Enrolled: Instant Enroll -->
              <VBtn
                v-else
                color="primary"
                variant="elevated"
                size="small"
                class="font-weight-medium"
                :loading="isEnrolling === course.id"
                @click="enroll(course.id)"
              >
                <VIcon start icon="mdi-book-plus" size="16" />
                Daftar Kursus
              </VBtn>

              <!-- View Details -->
              <VBtn
                variant="tonal"
                color="default"
                size="small"
                icon
                rounded="lg"
                title="Lihat Detail Kursus"
                :href="route('student.courses.show', course.slug || course.id)"
              >
                <VIcon icon="mdi-arrow-right" size="18" />
              </VBtn>
            </div>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <!-- Pagination -->
    <div
      v-if="courses.last_page > 1"
      class="mt-8 d-flex justify-center"
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
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.course-card:hover {
  transform: translateY(-3px);
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
  box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.08) !important;
}

.course-cover {
  position: relative;
  overflow: hidden;
}

.course-title a:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
