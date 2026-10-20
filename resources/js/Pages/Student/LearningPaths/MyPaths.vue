<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total_enrolled: 0,
      in_progress: 0,
      completed: 0,
    }),
  },
})

const selectedFilter = ref('all') // 'all', 'in_progress', 'completed'
const searchQuery = ref('')

const getProgress = enrollment => {
  const totalCourses = enrollment?.learning_path?.courses?.length || 0
  const completedCourses = Number(enrollment?.completed_count) || 0

  if (typeof enrollment?.progress === 'number' || typeof enrollment?.progress === 'string') {
    const progress = Number(enrollment.progress)
    if (Number.isFinite(progress)) {
      return Math.min(100, Math.max(0, progress))
    }
  }

  if (totalCourses > 0) {
    return Math.min(100, Math.round((completedCourses / totalCourses) * 100))
  }

  return 0
}

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

const filteredEnrollments = computed(() => {
  let list = props.enrollments || []

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(e => e.learning_path?.title?.toLowerCase().includes(q))
  }

  if (selectedFilter.value === 'in_progress') {
    list = list.filter(e => !e.completed_at && getProgress(e) < 100)
  } else if (selectedFilter.value === 'completed') {
    list = list.filter(e => !!e.completed_at || getProgress(e) === 100)
  }

  return list
})
</script>

<template>
  <div class="my-learning-paths-container">
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
              <VIcon icon="mdi-book-account-outline" size="32" color="primary" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h4 font-weight-bold text-high-emphasis mb-0">
                  Jalur Belajar Saya
                </h4>
                <VChip size="small" color="primary" variant="tonal" label class="font-weight-medium">
                  Perjalanan Belajar Aktif
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                Pantau progres kompetensi, selesaikan rangkaian kursus, dan raih sertifikat keahlian Anda.
              </p>
            </div>
          </div>

          <!-- Quick Navigation Pill Tabs -->
          <div class="d-flex align-center ga-2 bg-background pa-1 rounded-pill border">
            <VBtn
              size="small"
              variant="text"
              color="default"
              rounded="pill"
              class="text-capitalize px-4"
              :href="route('student.learning-paths.index')"
            >
              <VIcon start icon="mdi-compass-outline" size="18" />
              Semua Jalur
            </VBtn>
            <VBtn
              size="small"
              variant="flat"
              color="primary"
              rounded="pill"
              class="text-capitalize px-4"
              :href="route('student.learning-paths.my-paths')"
            >
              <VIcon start icon="mdi-book-account-outline" size="18" />
              Jalur Saya
            </VBtn>
          </div>
        </div>

        <!-- Quick Stats Overview -->
        <VDivider class="my-6" />

        <VRow>
          <VCol cols="12" sm="4">
            <div class="d-flex align-center ga-3 pa-3 rounded-lg bg-background border">
              <VAvatar color="primary" variant="tonal" size="42" rounded="lg">
                <VIcon icon="mdi-book-open-page-variant" size="22" />
              </VAvatar>
              <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Total Jalur Diikuti</div>
                <div class="text-h6 font-weight-bold">{{ stats.total_enrolled ?? enrollments.length }}</div>
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
                <div class="text-h6 font-weight-bold text-warning">{{ stats.in_progress ?? 0 }}</div>
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
                <div class="text-h6 font-weight-bold text-success">{{ stats.completed ?? 0 }}</div>
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
            Semua ({{ enrollments.length }})
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedFilter === 'in_progress' ? 'flat' : 'outlined'"
            :color="selectedFilter === 'in_progress' ? 'warning' : 'default'"
            @click="selectedFilter = 'in_progress'"
          >
            Sedang Berjalan ({{ stats.in_progress ?? 0 }})
          </VChip>
          <VChip
            filter
            size="small"
            :variant="selectedFilter === 'completed' ? 'flat' : 'outlined'"
            :color="selectedFilter === 'completed' ? 'success' : 'default'"
            @click="selectedFilter = 'completed'"
          >
            Selesai ({{ stats.completed ?? 0 }})
          </VChip>
        </div>

        <!-- Search Bar -->
        <div style="min-width: 260px; max-width: 360px;">
          <VTextField
            v-model="searchQuery"
            placeholder="Cari jalur belajar saya..."
            density="compact"
            variant="outlined"
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
          />
        </div>
      </div>
    </VCard>

    <!-- Enrolled Paths Grid -->
    <VRow>
      <VCol
        v-for="enrollment in filteredEnrollments"
        :key="enrollment.id"
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
                {{ enrollment.learning_path?.courses?.length || 0 }} Kursus
              </VChip>

              <VChip
                v-if="enrollment.completed_at || getProgress(enrollment) === 100"
                size="x-small"
                color="success"
                variant="flat"
                label
                class="font-weight-bold"
              >
                <VIcon start icon="mdi-check-circle" size="12" />
                Tuntas
              </VChip>
              <VChip
                v-else
                size="x-small"
                color="warning"
                variant="tonal"
                label
                class="font-weight-bold"
              >
                <VIcon start icon="mdi-progress-clock" size="12" />
                Sedang Berjalan
              </VChip>
            </div>

            <!-- Path Title -->
            <h5
              class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1 line-clamp-1"
              :title="enrollment.learning_path?.title"
            >
              {{ enrollment.learning_path?.title }}
            </h5>

            <!-- Path Description -->
            <p class="text-caption text-medium-emphasis mb-1 line-clamp-2" style="line-height: 1.4;">
              {{ enrollment.learning_path?.description || 'Pelajari rangkaian kurikulum bertahap ini untuk menuntaskan keahlian Anda.' }}
            </p>
          </div>

          <VDivider />

          <!-- Progress Overview Section -->
          <VCardText class="pa-4 pt-3 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <div class="mb-3">
                <div class="d-flex justify-space-between align-center mb-1">
                  <span class="text-caption text-medium-emphasis font-weight-medium" style="font-size: 0.75rem !important;">Kemajuan Belajar</span>
                  <span class="text-caption font-weight-bold text-primary" style="font-size: 0.75rem !important;">{{ getProgress(enrollment) }}%</span>
                </div>
                <VProgressLinear
                  :model-value="getProgress(enrollment)"
                  :color="getProgress(enrollment) === 100 ? 'success' : 'primary'"
                  height="5"
                  rounded
                  bg-opacity="0.15"
                />
              </div>

              <!-- Completion Date or Started Date -->
              <div
                v-if="enrollment.completed_at || getProgress(enrollment) === 100"
                class="d-flex align-center ga-2 pa-1-5 px-2 rounded bg-success-light border border-success-subtle mb-3"
              >
                <VIcon icon="mdi-trophy-variant" color="success" size="16" />
                <div class="text-caption text-success font-weight-medium" style="font-size: 0.72rem !important;">
                  Tuntas pada {{ formatDate(enrollment.completed_at) }}
                </div>
              </div>
              <div
                v-else
                class="d-flex align-center ga-2 pa-1-5 px-2 rounded bg-surface-variant-subtle border mb-3"
              >
                <VIcon icon="mdi-calendar-start" color="medium-emphasis" size="16" />
                <div class="text-caption text-medium-emphasis" style="font-size: 0.72rem !important;">
                  Dimulai sejak {{ formatDate(enrollment.started_at) }}
                </div>
              </div>

              <!-- Syllabus Roadmap Snapshot -->
              <div class="d-flex align-center justify-space-between mb-2">
                <span class="text-caption font-weight-bold text-uppercase text-medium-emphasis" style="font-size: 0.72rem !important; letter-spacing: 0.5px;">
                  Capaian Kursus
                </span>
                <span class="text-caption font-weight-bold text-high-emphasis" style="font-size: 0.72rem !important;">
                  {{ enrollment.completed_count || 0 }} / {{ enrollment.learning_path?.courses?.length || 0 }} Kursus
                </span>
              </div>

              <div class="d-flex flex-column ga-1-5 mb-2">
                <div
                  v-for="(course, idx) in (enrollment.learning_path?.courses || []).slice(0, 3)"
                  :key="course.id"
                  class="d-flex align-center ga-2 pa-1-5 px-2 rounded bg-surface-variant-subtle border"
                >
                  <VAvatar
                    size="18"
                    :color="idx < (enrollment.completed_count || 0) ? 'success' : 'primary'"
                    variant="tonal"
                    class="font-weight-bold text-caption"
                    style="font-size: 0.65rem !important; min-width: 18px;"
                  >
                    <VIcon
                      v-if="idx < (enrollment.completed_count || 0)"
                      icon="mdi-check"
                      size="12"
                    />
                    <span v-else>{{ idx + 1 }}</span>
                  </VAvatar>
                  <span class="text-caption font-weight-medium text-truncate flex-grow-1" style="font-size: 0.78rem !important;">
                    {{ course.title }}
                  </span>
                </div>

                <div
                  v-if="(enrollment.learning_path?.courses || []).length > 3"
                  class="text-caption text-medium-emphasis text-center py-1 bg-surface-variant-subtle rounded border border-dashed"
                  style="font-size: 0.7rem !important;"
                >
                  + {{ enrollment.learning_path.courses.length - 3 }} kursus lainnya
                </div>
              </div>
            </div>
          </VCardText>

          <VDivider />

          <!-- Card Actions Footer -->
          <VCardActions class="pa-2-5 px-3 bg-surface-variant-subtle">
            <VBtn
              color="primary"
              variant="elevated"
              size="small"
              block
              class="font-weight-medium"
              :prepend-icon="getProgress(enrollment) === 100 ? 'mdi-certificate-outline' : 'mdi-play-circle'"
              append-icon="mdi-arrow-right"
              :href="route('student.learning-paths.show', enrollment.learning_path?.id)"
            >
              {{ getProgress(enrollment) === 100 ? 'Review & Sertifikat' : 'Lanjutkan Belajar' }}
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>

      <!-- Empty State -->
      <VCol
        v-if="filteredEnrollments.length === 0"
        cols="12"
      >
        <VCard flat border class="pa-12 text-center rounded-xl bg-surface">
          <VAvatar size="72" color="primary" variant="tonal" class="mb-4">
            <VIcon icon="mdi-compass-off-outline" size="40" color="primary" />
          </VAvatar>
          <h5 class="text-h5 font-weight-bold mb-2">
            {{ searchQuery ? 'Tidak Ditemukan Jalur yang Sesuai' : 'Belum Ada Jalur yang Diikuti' }}
          </h5>
          <p class="text-body-2 text-medium-emphasis mx-auto mb-6" style="max-width: 480px;">
            {{ searchQuery
              ? `Tidak ditemukan jalur belajar aktif dengan pencarian "${searchQuery}". Silakan coba kata kunci lain.`
              : 'Anda belum mendaftar di jalur belajar mana pun. Pilih jalur belajar yang diminati untuk mulai mengembangkan keahlian Anda!'
            }}
          </p>
          <VBtn
            color="primary"
            variant="elevated"
            prepend-icon="mdi-compass-outline"
            :href="route('student.learning-paths.index')"
          >
            Jelajahi Semua Jalur Belajar
          </VBtn>
        </VCard>
      </VCol>
    </VRow>
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
