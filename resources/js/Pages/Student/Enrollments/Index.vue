<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  courses: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const flash = computed(() => page.props.flash || {})

const searchQuery = ref('')
const selectedStatus = ref('all') // 'all', 'in_progress', 'completed'
const selectedCategory = ref('all')
const currentPage = ref(1)
const itemsPerPage = ref(6)

// Extract unique categories from actual enrolled courses
const categoriesList = computed(() => {
  const cats = new Set()
  props.courses.forEach(c => {
    const val = c.category || c.tags
    if (val) cats.add(val)
  })
  return Array.from(cats)
})

// KPI Stats
const totalEnrolled = computed(() => props.courses.length)
const inProgressCount = computed(() => {
  return props.courses.filter(c => (c.progress ?? 0) < 100 && !c.completed_at).length
})
const completedCount = computed(() => {
  return props.courses.filter(c => (c.progress ?? 0) === 100 || !!c.completed_at).length
})
const averageProgress = computed(() => {
  if (!props.courses.length) return 0
  const total = props.courses.reduce((sum, c) => sum + (Number(c.progress) || 0), 0)
  return Math.round(total / props.courses.length)
})

// Filtered Courses
const filteredCourses = computed(() => {
  let list = props.courses || []

  // Status Filter
  if (selectedStatus.value === 'in_progress') {
    list = list.filter(c => (c.progress ?? 0) < 100 && !c.completed_at)
  } else if (selectedStatus.value === 'completed') {
    list = list.filter(c => (c.progress ?? 0) === 100 || !!c.completed_at)
  }

  // Category Filter
  if (selectedCategory.value !== 'all') {
    list = list.filter(c => (c.category || c.tags) === selectedCategory.value)
  }

  // Search Query
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(c =>
      c.courseTitle?.toLowerCase().includes(q) ||
      c.instructorName?.toLowerCase().includes(q)
    )
  }

  return list
})

// Paginated
const paginatedCourses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredCourses.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => {
  return Math.ceil(filteredCourses.value.length / itemsPerPage.value)
})

function goToLearning(course) {
  if (course.enrollment_id) {
    router.get(route('student.enrollments.show', course.enrollment_id))
  } else {
    router.get(route('student.courses.show', course.slug || course.id))
  }
}

function resetFilters() {
  searchQuery.value = ''
  selectedStatus.value = 'all'
  selectedCategory.value = 'all'
  currentPage.value = 1
}

function resolveChipColor(tag) {
  if (!tag) return 'primary'
  const lower = tag.toLowerCase()
  if (lower.includes('web') || lower.includes('program')) return 'primary'
  if (lower.includes('desain') || lower.includes('ui')) return 'info'
  if (lower.includes('bisnis') || lower.includes('manage')) return 'success'
  if (lower.includes('data') || lower.includes('ai')) return 'purple'
  return 'primary'
}
</script>

<template>
  <div class="my-courses-page pb-12">
    <!-- Flash Messages -->
    <VAlert
      v-if="flash.error"
      type="error"
      closable
      variant="tonal"
      class="mb-6 rounded-lg"
    >
      {{ flash.error }}
    </VAlert>
    <VAlert
      v-if="flash.success"
      type="success"
      closable
      variant="tonal"
      class="mb-6 rounded-lg"
    >
      {{ flash.success }}
    </VAlert>

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
              <VIcon icon="mdi-school-outline" size="32" color="primary" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h4 font-weight-bold text-high-emphasis mb-0">
                  Pembelajaran Saya
                </h4>
                <VChip size="small" color="primary" variant="tonal" label class="font-weight-medium">
                  {{ totalEnrolled }} Kursus Terdaftar
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                Lanjutkan materi pembelajaran, pantau capaian kuis, dan raih sertifikat kompetensi Anda.
              </p>
            </div>
          </div>

          <VBtn
            color="primary"
            variant="elevated"
            prepend-icon="mdi-compass-outline"
            class="font-weight-medium px-4"
            :href="route('student.courses.list')"
          >
            Jelajahi Kursus Lainnya
          </VBtn>
        </div>

        <!-- Quick Stats Overview -->
        <VDivider class="my-6" />

        <VRow>
          <!-- 1. Total Enrolled -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="primary" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-book-multiple-outline" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Total Kursus</div>
                <div class="text-h6 font-weight-bold text-high-emphasis">{{ totalEnrolled }}</div>
              </div>
            </div>
          </VCol>

          <!-- 2. In Progress -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="warning" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-progress-clock" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Sedang Dipelajari</div>
                <div class="text-h6 font-weight-bold text-warning">{{ inProgressCount }}</div>
              </div>
            </div>
          </VCol>

          <!-- 3. Completed -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="success" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-check-decagram" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Kursus Selesai</div>
                <div class="text-h6 font-weight-bold text-success">{{ completedCount }}</div>
              </div>
            </div>
          </VCol>

          <!-- 4. Avg Progress -->
          <VCol cols="12" sm="6" md="3">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border h-100">
              <VAvatar color="info" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-chart-arc" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Rata-rata Kemajuan</div>
                <div class="text-h6 font-weight-bold text-info">{{ averageProgress }}%</div>
              </div>
            </div>
          </VCol>
        </VRow>
      </div>
    </VCard>

    <!-- Toolbar: Search & Filter Bar -->
    <VCard flat border class="mb-6 rounded-xl pa-4 bg-surface">
      <div class="d-flex flex-column flex-md-row justify-space-between align-stretch align-md-center ga-3">
        <!-- Status Filter Chips -->
        <div class="d-flex align-center ga-2 flex-wrap">
          <span class="text-caption text-medium-emphasis font-weight-bold text-uppercase me-1">Status:</span>
          <VChip
            filter
            size="small"
            :variant="selectedStatus === 'all' ? 'flat' : 'outlined'"
            :color="selectedStatus === 'all' ? 'primary' : 'default'"
            @click="selectedStatus = 'all'"
          >
            Semua ({{ totalEnrolled }})
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedStatus === 'in_progress' ? 'flat' : 'outlined'"
            :color="selectedStatus === 'in_progress' ? 'warning' : 'default'"
            @click="selectedStatus = 'in_progress'"
          >
            Sedang Berjalan ({{ inProgressCount }})
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedStatus === 'completed' ? 'flat' : 'outlined'"
            :color="selectedStatus === 'completed' ? 'success' : 'default'"
            @click="selectedStatus = 'completed'"
          >
            Selesai ({{ completedCount }})
          </VChip>
        </div>

        <!-- Right Side: Category Select & Search Input -->
        <div class="d-flex align-center ga-3 flex-wrap">
          <!-- Dynamic Category Filter -->
          <div v-if="categoriesList.length > 0" style="min-width: 170px;">
            <VSelect
              v-model="selectedCategory"
              :items="[
                { title: 'Semua Kategori', value: 'all' },
                ...categoriesList.map(c => ({ title: c, value: c }))
              ]"
              density="compact"
              variant="outlined"
              hide-details
            />
          </div>

          <!-- Search Bar -->
          <div style="min-width: 240px; max-width: 340px;" class="flex-grow-1">
            <VTextField
              v-model="searchQuery"
              placeholder="Cari kursus atau instruktur..."
              density="compact"
              variant="outlined"
              hide-details
              clearable
              prepend-inner-icon="mdi-magnify"
            />
          </div>
        </div>
      </div>
    </VCard>

    <!-- Enrolled Courses Grid -->
    <VRow v-if="paginatedCourses.length > 0">
      <VCol
        v-for="course in paginatedCourses"
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
          <!-- Card Thumbnail with Ambient Overlay -->
          <div
            class="course-card-cover position-relative cursor-pointer"
            @click="goToLearning(course)"
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

            <!-- Overlay Badges -->
            <div class="card-cover-badges pa-3 d-flex justify-space-between align-start w-100 position-absolute" style="top: 0; left: 0;">
              <VChip
                size="small"
                variant="flat"
                :color="resolveChipColor(course.tags || course.category)"
                label
                class="font-weight-medium shadow-sm"
              >
                {{ course.tags || course.category || 'Umum' }}
              </VChip>

              <VChip
                v-if="course.progress === 100 || course.completed_at"
                size="small"
                color="success"
                variant="flat"
                label
                class="font-weight-bold shadow-sm"
              >
                <VIcon start icon="mdi-check-circle" size="14" />
                Selesai
              </VChip>
              <VChip
                v-else-if="course.progress > 0"
                size="small"
                color="warning"
                variant="flat"
                label
                class="font-weight-bold shadow-sm"
              >
                <VIcon start icon="mdi-progress-clock" size="14" />
                {{ course.progress }}%
              </VChip>
              <VChip
                v-else
                size="small"
                color="secondary"
                variant="flat"
                label
                class="font-weight-medium shadow-sm"
              >
                Belum Dimulai
              </VChip>
            </div>
          </div>

          <!-- Card Content Body -->
          <VCardText class="pa-5 flex-grow-1 d-flex flex-column">
            <!-- Rating & Level Row -->
            <div class="d-flex justify-space-between align-center mb-2">
              <div class="d-flex align-center ga-1 text-caption">
                <VIcon icon="mdi-star" color="warning" size="16" />
                <span class="font-weight-bold text-high-emphasis">{{ Number(course.rating || 0).toFixed(1) }}</span>
                <span class="text-medium-emphasis">({{ course.ratingCount || 0 }})</span>
              </div>

              <span class="text-caption text-medium-emphasis d-flex align-center ga-1">
                <VIcon icon="mdi-clock-outline" size="14" />
                {{ course.time || 'Akses Fleksibel' }}
              </span>
            </div>

            <!-- Title -->
            <h5
              class="text-subtitle-1 font-weight-bold text-high-emphasis mb-2 line-clamp-2 cursor-pointer course-hover-title"
              :title="course.courseTitle"
              @click="goToLearning(course)"
            >
              {{ course.courseTitle }}
            </h5>

            <!-- Instructor Line -->
            <div class="d-flex align-center ga-2 mb-4">
              <VAvatar size="22" :image="course.instructorAvatar">
                <span class="text-caption font-weight-bold">{{ (course.instructorName || 'I').charAt(0) }}</span>
              </VAvatar>
              <span class="text-caption text-medium-emphasis text-truncate">
                {{ course.instructorName || 'Instruktur' }}
              </span>
            </div>

            <!-- Progress Indicator Section -->
            <div class="mt-auto pt-2">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-caption text-medium-emphasis font-weight-medium">Kemajuan Materi</span>
                <span class="text-caption font-weight-bold" :class="course.progress === 100 ? 'text-success' : 'text-primary'">
                  {{ course.completedTasks || 0 }} / {{ course.totalTasks || 0 }} Selesai
                </span>
              </div>
              <VProgressLinear
                :model-value="course.progress || 0"
                :color="course.progress === 100 ? 'success' : 'primary'"
                height="6"
                rounded
                bg-opacity="0.15"
              />
            </div>
          </VCardText>

          <VDivider />

          <!-- Card Actions Footer -->
          <VCardActions class="pa-4 bg-background">
            <div class="d-flex ga-2 w-100">
              <VBtn
                v-if="course.progress === 100 || course.completed_at"
                color="success"
                variant="tonal"
                class="flex-grow-1 font-weight-medium"
                @click="goToLearning(course)"
              >
                <VIcon start icon="mdi-replay" size="18" />
                Ulas Kembali Materi
              </VBtn>

              <VBtn
                v-else
                color="primary"
                variant="elevated"
                class="flex-grow-1 font-weight-medium"
                @click="goToLearning(course)"
              >
                <VIcon start icon="mdi-play-circle" size="18" />
                Lanjutkan Belajar
              </VBtn>

              <VBtn
                variant="tonal"
                color="default"
                icon
                rounded="lg"
                title="Lihat Detail Kursus"
                :href="route('student.courses.show', course.slug || course.id)"
              >
                <VIcon icon="mdi-information-outline" size="20" />
              </VBtn>
            </div>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <!-- Empty State -->
    <VRow v-else>
      <VCol cols="12">
        <VCard flat border class="pa-12 text-center rounded-xl bg-surface">
          <VAvatar size="72" color="primary" variant="tonal" class="mb-4">
            <VIcon icon="mdi-book-open-page-variant-outline" size="40" color="primary" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">
            {{ searchQuery || selectedStatus !== 'all' || selectedCategory !== 'all'
              ? 'Tidak Ada Kursus yang Sesuai dengan Filter'
              : 'Belum Ada Kursus yang Diikuti'
            }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mx-auto mb-6" style="max-width: 480px;">
            {{ searchQuery || selectedStatus !== 'all' || selectedCategory !== 'all'
              ? 'Silakan sesuaikan kata kunci pencarian atau ubah filter untuk menemukan kursus Anda.'
              : 'Anda belum mendaftar di kursus mana pun. Mulai langkah pertama Anda dengan memilih kursus dari katalog kami!'
            }}
          </p>

          <div class="d-flex justify-center ga-3">
            <VBtn
              v-if="searchQuery || selectedStatus !== 'all' || selectedCategory !== 'all'"
              variant="tonal"
              color="primary"
              prepend-icon="mdi-refresh"
              @click="resetFilters"
            >
              Reset Filter
            </VBtn>
            <VBtn
              color="primary"
              variant="elevated"
              prepend-icon="mdi-compass-outline"
              :href="route('student.courses.list')"
            >
              Jelajahi Katalog Kursus
            </VBtn>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Pagination -->
    <div
      v-if="totalPages > 1"
      class="d-flex justify-center mt-8"
    >
      <VPagination
        v-model="currentPage"
        :length="totalPages"
        :total-visible="5"
        rounded="circle"
        density="comfortable"
        active-color="primary"
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

.course-card-cover {
  position: relative;
  overflow: hidden;
}

.course-hover-title:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
