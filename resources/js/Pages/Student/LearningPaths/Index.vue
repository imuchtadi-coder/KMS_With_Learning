<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  learningPaths: { type: Object, required: true },
  recommendations: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({ search: '' }) },
  stats: {
    type: Object,
    default: () => ({
      total_paths: 0,
      enrolled_paths: 0,
      completed_paths: 0,
    }),
  },
})

const searchQuery = ref(props.filters?.search || '')
const selectedFilter = ref('all') // 'all', 'enrolled', 'not_enrolled', 'completed'
const isEnrolling = ref(null)

function handleSearch() {
  router.get(
    route('student.learning-paths.index'),
    { search: searchQuery.value },
    { preserveState: true, replace: true }
  )
}

function clearSearch() {
  searchQuery.value = ''
  handleSearch()
}

function handlePageChange(page) {
  router.get(
    route('student.learning-paths.index'),
    { search: searchQuery.value, page },
    { preserveState: true, preserveScroll: true }
  )
}

function enroll(pathId) {
  isEnrolling.value = pathId
  router.post(route('student.learning-paths.enroll', pathId), {}, {
    preserveScroll: true,
    onFinish: () => {
      isEnrolling.value = null
    },
  })
}

const filteredPaths = computed(() => {
  const items = props.learningPaths?.data || []
  if (selectedFilter.value === 'enrolled') {
    return items.filter(p => p.isEnrolled && (p.progress ?? 0) < 100)
  }
  if (selectedFilter.value === 'completed') {
    return items.filter(p => p.isEnrolled && (p.progress ?? 0) === 100)
  }
  if (selectedFilter.value === 'not_enrolled') {
    return items.filter(p => !p.isEnrolled)
  }
  return items
})
</script>

<template>
  <div class="learning-paths-container">
    <!-- Header Hero Banner -->
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
              <VIcon icon="mdi-map-marker-path" size="32" color="primary" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h4 font-weight-bold text-high-emphasis mb-0">
                  Jalur Belajar
                </h4>
                <VChip size="small" color="primary" variant="tonal" label class="font-weight-medium">
                  Kurikulum Terstruktur
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                Kuasai kompetensi terarah melalui rangkaian kursus bertahap yang telah dirancang para pakar.
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
              :href="route('student.learning-paths.index')"
            >
              <VIcon start icon="mdi-compass-outline" size="18" />
              Semua Jalur
            </VBtn>
            <VBtn
              size="small"
              variant="text"
              color="default"
              rounded="pill"
              class="text-capitalize px-4"
              :href="route('student.learning-paths.my-paths')"
            >
              <VIcon start icon="mdi-book-account-outline" size="18" />
              Jalur Saya
              <VBadge
                v-if="stats.enrolled_paths > 0"
                :content="stats.enrolled_paths"
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
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border">
              <VAvatar color="primary" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-map-marker-distance" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Total Jalur Belajar</div>
                <div class="text-h6 font-weight-bold">{{ stats.total_paths || learningPaths.total || 0 }}</div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border">
              <VAvatar color="warning" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-progress-clock" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Sedang Dipelajari</div>
                <div class="text-h6 font-weight-bold text-warning">{{ stats.enrolled_paths || 0 }}</div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border">
              <VAvatar color="success" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-check-decagram" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Jalur Tuntas</div>
                <div class="text-h6 font-weight-bold text-success">{{ stats.completed_paths || 0 }}</div>
              </div>
            </div>
          </VCol>
        </VRow>
      </div>
    </VCard>

    <!-- Toolbar: Search & Filter Tabs -->
    <VCard flat border class="mb-6 rounded-xl pa-4 bg-surface">
      <div class="d-flex flex-column flex-md-row justify-space-between align-stretch align-md-center ga-3">
        <!-- Filter Chips -->
        <div class="d-flex align-center ga-2 flex-wrap">
          <span class="text-caption text-medium-emphasis font-weight-bold text-uppercase me-1">Filter:</span>
          <VChip
            filter
            size="small"
            :variant="selectedFilter === 'all' ? 'flat' : 'outlined'"
            :color="selectedFilter === 'all' ? 'primary' : 'default'"
            @click="selectedFilter = 'all'"
          >
            Semua
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedFilter === 'enrolled' ? 'flat' : 'outlined'"
            :color="selectedFilter === 'enrolled' ? 'warning' : 'default'"
            @click="selectedFilter = 'enrolled'"
          >
            Sedang Berjalan
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedFilter === 'completed' ? 'flat' : 'outlined'"
            :color="selectedFilter === 'completed' ? 'success' : 'default'"
            @click="selectedFilter = 'completed'"
          >
            Selesai
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedFilter === 'not_enrolled' ? 'flat' : 'outlined'"
            :color="selectedFilter === 'not_enrolled' ? 'secondary' : 'default'"
            @click="selectedFilter = 'not_enrolled'"
          >
            Belum Diikuti
          </VChip>
        </div>

        <!-- Search Bar -->
        <div style="min-width: 280px; max-width: 380px;">
          <VTextField
            v-model="searchQuery"
            placeholder="Cari jalur belajar..."
            density="compact"
            variant="outlined"
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
            @keyup.enter="handleSearch"
            @click:clear="clearSearch"
          />
        </div>
      </div>
    </VCard>

    <!-- Path Cards Grid -->
    <VRow>
      <VCol
        v-for="path in filteredPaths"
        :key="path.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard
          flat
          border
          class="h-100 d-flex flex-column rounded-lg path-card transition-swing"
        >
          <!-- Top Card Meta Header -->
          <div class="pa-4 pb-2">
            <div class="d-flex justify-space-between align-center mb-2">
              <VChip
                size="x-small"
                variant="tonal"
                color="primary"
                label
                class="font-weight-semibold"
              >
                <VIcon start icon="mdi-book-multiple-outline" size="12" />
                {{ path.courses?.length || 0 }} Kursus
              </VChip>

              <VChip
                v-if="path.isEnrolled && path.progress === 100"
                size="x-small"
                color="success"
                variant="flat"
                label
                class="font-weight-bold"
              >
                <VIcon start icon="mdi-check-circle" size="12" />
                Selesai
              </VChip>
              <VChip
                v-else-if="path.isEnrolled && path.progress > 0"
                size="x-small"
                color="warning"
                variant="tonal"
                label
                class="font-weight-bold"
              >
                <VIcon start icon="mdi-progress-clock" size="12" />
                {{ path.progress }}% Selesai
              </VChip>
              <VChip
                v-else-if="path.isEnrolled"
                size="x-small"
                color="info"
                variant="tonal"
                label
                class="font-weight-medium"
              >
                Terdaftar
              </VChip>
              <VChip
                v-else
                size="x-small"
                color="secondary"
                variant="tonal"
                label
                class="font-weight-medium"
              >
                Tersedia
              </VChip>
            </div>

            <!-- Path Title -->
            <h5
              class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1 line-clamp-1"
              :title="path.title"
            >
              {{ path.title }}
            </h5>

            <!-- Path Description -->
            <p class="text-caption text-medium-emphasis mb-1 line-clamp-2" style="line-height: 1.4;">
              {{ path.description || 'Pelajari kurikulum komprehensif ini untuk meningkatkan kemampuan Anda secara bertahap.' }}
            </p>
          </div>

          <VDivider />

          <!-- Mini Syllabus Roadmap -->
          <VCardText class="pa-4 pt-3 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <div class="d-flex align-center justify-space-between mb-2">
                <span class="text-caption font-weight-bold text-uppercase text-medium-emphasis" style="font-size: 0.72rem !important; letter-spacing: 0.5px;">
                  Alur Kursus
                </span>
                <span class="text-caption text-disabled" style="font-size: 0.72rem !important;">
                  {{ path.courses?.length || 0 }} Modul Terangkai
                </span>
              </div>

              <div class="d-flex flex-column ga-1-5 mb-2">
                <div
                  v-for="(course, idx) in (path.courses || []).slice(0, 3)"
                  :key="course.id"
                  class="d-flex align-center ga-2 pa-1-5 px-2 rounded bg-surface-variant-subtle border"
                >
                  <VAvatar
                    size="18"
                    :color="path.isEnrolled && path.progress === 100 ? 'success' : 'primary'"
                    variant="tonal"
                    class="font-weight-bold"
                    style="font-size: 0.65rem !important; min-width: 18px;"
                  >
                    {{ idx + 1 }}
                  </VAvatar>
                  <div class="text-caption font-weight-medium text-truncate flex-grow-1" style="font-size: 0.78rem !important;">
                    {{ course.title }}
                  </div>
                  <div v-if="course.sections_count" class="text-caption text-disabled" style="font-size: 0.7rem !important; white-space: nowrap;">
                    {{ course.sections_count }} sesi
                  </div>
                </div>

                <!-- More courses chip -->
                <div
                  v-if="(path.courses || []).length > 3"
                  class="text-caption text-medium-emphasis text-center py-1 bg-surface-variant-subtle rounded border border-dashed"
                  style="font-size: 0.7rem !important;"
                >
                  + {{ path.courses.length - 3 }} kursus lanjutan lainnya
                </div>
              </div>
            </div>

            <!-- Progress Bar (if enrolled) -->
            <div v-if="path.isEnrolled" class="pt-2">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-caption text-medium-emphasis font-weight-medium" style="font-size: 0.75rem !important;">Kemajuan Belajar</span>
                <span class="text-caption font-weight-bold text-primary" style="font-size: 0.75rem !important;">{{ path.progress || 0 }}%</span>
              </div>
              <VProgressLinear
                :model-value="path.progress || 0"
                color="primary"
                height="5"
                rounded
                bg-opacity="0.15"
              />
            </div>
          </VCardText>

          <VDivider />

          <!-- Card Actions Footer -->
          <VCardActions class="pa-2-5 px-3 bg-surface-variant-subtle">
            <VBtn
              v-if="path.isEnrolled"
              color="primary"
              variant="elevated"
              size="small"
              block
              class="font-weight-medium"
              prepend-icon="mdi-play-circle"
              append-icon="mdi-arrow-right"
              :href="route('student.learning-paths.show', path.id)"
            >
              {{ path.progress === 100 ? 'Review Kurikulum' : 'Lanjutkan Belajar' }}
            </VBtn>

            <VBtn
              v-else
              color="primary"
              variant="elevated"
              size="small"
              block
              class="font-weight-medium"
              prepend-icon="mdi-lightning-bolt"
              append-icon="mdi-arrow-right"
              :loading="isEnrolling === path.id"
              @click="enroll(path.id)"
            >
              Ikuti Jalur Belajar
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- Empty State -->
      <VCol
        v-if="filteredPaths.length === 0"
        cols="12"
      >
        <VCard flat border class="pa-12 text-center rounded-xl bg-surface">
          <VAvatar size="72" color="primary" variant="tonal" class="mb-4">
            <VIcon icon="mdi-map-marker-path" size="40" color="primary" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">
            {{ searchQuery ? 'Tidak Ada Jalur Belajar yang Cocok' : 'Belum Ada Jalur Belajar' }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mx-auto mb-6" style="max-width: 480px;">
            {{ searchQuery
              ? `Tidak ditemukan jalur belajar dengan kata kunci "${searchQuery}". Silakan coba kata kunci lain atau reset pencarian.`
              : 'Jalur belajar terstruktur belum dirilis oleh instruktur. Silakan periksa kembali beberapa saat lagi.'
            }}
          </p>
          <VBtn
            v-if="searchQuery"
            variant="tonal"
            color="primary"
            prepend-icon="mdi-refresh"
            @click="clearSearch"
          >
            Reset Pencarian
          </VBtn>
        </VCard>
      </VCol>
    </VRow>

    <!-- Pagination -->
    <div
      v-if="learningPaths.last_page > 1"
      class="d-flex justify-center mt-8"
    >
      <VPagination
        :model-value="learningPaths.current_page"
        :length="learningPaths.last_page"
        :total-visible="5"
        rounded="circle"
        density="comfortable"
        active-color="primary"
        @update:model-value="handlePageChange"
      />
    </div>
  </div>
</template>

<style scoped>
.path-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.path-card:hover {
  transform: translateY(-2px);
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
  box-shadow: 0 6px 18px -4px rgba(0, 0, 0, 0.08) !important;
}

.bg-surface-variant-subtle {
  background-color: rgba(var(--v-theme-on-surface), 0.03);
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
