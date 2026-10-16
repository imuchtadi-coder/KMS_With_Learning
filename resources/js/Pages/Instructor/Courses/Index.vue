<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  courses: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      published: 0,
      draft: 0,
      total_students: 0,
    }),
  },
})

// Search & Filter state
const search = ref('')
const statusFilter = ref('')
const viewMode = ref('grid') // 'grid' | 'table'

// Client-side quick filter on current page items
const filteredCourses = computed(() => {
  let items = props.courses.data || []
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    items = items.filter(c => c.title?.toLowerCase().includes(q) || c.description?.toLowerCase().includes(q))
  }
  if (statusFilter.value) {
    items = items.filter(c => statusFilter.value === 'published' ? c.published : !c.published)
  }
  return items
})

const getLessonCount = (course) => {
  if (!course.sections) return 0
  return course.sections.reduce((acc, s) => acc + (s.lessons?.length || 0), 0)
}

function destroy(course) {
  if (!confirm(`Hapus kursus "${course.title}"? Seluruh materi yang berkaitan akan dihapus.`)) return
  router.delete(route('instructor.courses.destroy', course.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="instructor-courses-page">
    <!-- Header Section -->
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div>
        <div class="d-flex align-center ga-2 mb-1">
          <VIcon icon="mdi-book-multiple-outline" color="primary" size="24" />
          <h4 class="text-h4 font-weight-bold mb-0">Kursus Saya</h4>
        </div>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Kelola kurikulum, materi ajar, penilaian tugas/kuis, dan pantau peserta kursus Anda.
        </p>
      </div>

      <div class="d-flex align-center ga-2">
        <VBtn
          color="primary"
          variant="elevated"
          size="default"
          prepend-icon="mdi-plus"
          :href="route('instructor.courses.create')"
        >
          Buat Kursus Baru
        </VBtn>
      </div>
    </div>

    <!-- Quick Stats Metric Cards -->
    <VRow class="mb-5">
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Total Kursus</span>
              <span class="text-h5 font-weight-bold">{{ stats.total || courses.total || 0 }}</span>
            </div>
            <VAvatar color="primary" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-book-multiple" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Kursus Diterbitkan</span>
              <span class="text-h5 font-weight-bold text-success">{{ stats.published || 0 }}</span>
            </div>
            <VAvatar color="success" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-check-decagram-outline" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Kursus Draf</span>
              <span class="text-h5 font-weight-bold text-warning">{{ stats.draft || 0 }}</span>
            </div>
            <VAvatar color="warning" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-file-document-edit-outline" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Total Siswa Terdaftar</span>
              <span class="text-h5 font-weight-bold text-info">{{ stats.total_students || 0 }}</span>
            </div>
            <VAvatar color="info" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-account-group-outline" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filter & Control Toolbar -->
    <VCard flat border class="pa-3 mb-5">
      <div class="d-flex align-center justify-space-between flex-wrap ga-3">
        <div class="d-flex align-center ga-2 flex-grow-1 flex-wrap" style="max-width: 650px;">
          <!-- Search input -->
          <VTextField
            v-model="search"
            placeholder="Cari kursus berdasarkan judul atau deskripsi..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            class="flex-grow-1"
            style="min-width: 240px;"
          />

          <!-- Status select -->
          <VSelect
            v-model="statusFilter"
            :items="[
              { title: 'Semua Status', value: '' },
              { title: 'Diterbitkan (Aktif)', value: 'published' },
              { title: 'Draf (Belum Terbit)', value: 'draft' },
            ]"
            item-title="title"
            item-value="value"
            variant="outlined"
            density="compact"
            hide-details
            style="min-width: 170px;"
          />
        </div>

        <!-- View mode toggle -->
        <div class="d-flex align-center ga-1">
          <VBtnToggle
            v-model="viewMode"
            mandatory
            density="compact"
            color="primary"
            variant="outlined"
          >
            <VBtn value="grid" size="small" icon="mdi-view-grid-outline" title="Tampilan Grid" />
            <VBtn value="table" size="small" icon="mdi-view-list-outline" title="Tampilan Tabel" />
          </VBtnToggle>
        </div>
      </div>
    </VCard>

    <!-- Grid View Mode -->
    <VRow v-if="viewMode === 'grid'">
      <VCol
        v-for="course in filteredCourses"
        :key="course.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard flat border hover class="course-card h-100 d-flex flex-column rounded-lg">
          <!-- Thumbnail header -->
          <div class="course-thumbnail-wrapper position-relative overflow-hidden">
            <img
              v-if="course.thumbnail"
              :src="course.thumbnail"
              :alt="course.title"
              class="w-100 h-100 course-img"
            >
            <div
              v-else
              class="w-100 h-100 d-flex flex-column align-center justify-center course-fallback-bg text-white"
            >
              <VIcon icon="mdi-book-open-page-variant" size="42" class="opacity-80 mb-1" />
              <span class="text-caption font-weight-bold opacity-90 letter-spacing-1">KITSULUH COURSE</span>
            </div>

            <!-- Gradient overlay -->
            <div class="thumbnail-overlay" />

            <!-- Status & Level Chips -->
            <div class="position-absolute top-0 start-0 w-100 d-flex justify-space-between align-center pa-3">
              <VChip
                v-if="course.level"
                size="x-small"
                variant="elevated"
                color="surface"
                class="font-weight-medium text-capitalize shadow-sm"
              >
                {{ course.level }}
              </VChip>
              <div v-else />

              <VChip
                :color="course.published ? 'success' : 'warning'"
                size="x-small"
                variant="elevated"
                class="font-weight-bold shadow-sm"
              >
                {{ course.published ? 'Diterbitkan' : 'Draf' }}
              </VChip>
            </div>
          </div>

          <!-- Body Content -->
          <VCardText class="pa-4 flex-grow-1 d-flex flex-column justify-space-between">
            <div>
              <h6 class="text-subtitle-1 font-weight-bold mb-1 line-clamp-1" :title="course.title">
                {{ course.title }}
              </h6>
              <p class="text-caption text-medium-emphasis line-clamp-2 mb-3" style="min-height: 36px;">
                {{ course.description || 'Tidak ada deskripsi kursus.' }}
              </p>

              <!-- Compact Statistics Bar -->
              <div class="stats-row d-flex align-center justify-space-between py-2 px-3 rounded border mb-3">
                <div class="d-flex align-center ga-1 text-caption">
                  <VIcon icon="mdi-folder-outline" size="16" class="text-primary" />
                  <span><strong>{{ course.sections?.length || 0 }}</strong> Bab</span>
                </div>
                <div class="d-flex align-center ga-1 text-caption">
                  <VIcon icon="mdi-book-open-outline" size="16" class="text-info" />
                  <span><strong>{{ getLessonCount(course) }}</strong> Materi</span>
                </div>
                <div class="d-flex align-center ga-1 text-caption">
                  <VIcon icon="mdi-account-group-outline" size="16" class="text-success" />
                  <span><strong>{{ course.enrollments_count ?? course.enrollments?.length ?? 0 }}</strong> Siswa</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons Footer -->
            <div>
              <div class="d-flex align-center ga-2 pt-1">
                <!-- Primary Action: Kelola / Edit -->
                <VBtn
                  variant="elevated"
                  color="primary"
                  size="small"
                  prepend-icon="mdi-pencil-outline"
                  class="flex-grow-1 font-weight-medium"
                  :href="route('instructor.courses.edit', course.id)"
                >
                  Kelola Materi
                </VBtn>

                <!-- Analitik Penilaian -->
                <Link
                  :href="route('instructor.assessment-analytics.course', course.id)"
                  class="text-decoration-none"
                  title="Analitik Penilaian Siswa"
                >
                  <VBtn
                    variant="tonal"
                    color="primary"
                    size="small"
                    icon="mdi-chart-box-outline"
                  />
                </Link>

                <!-- More Options Menu -->
                <VMenu location="bottom end" transition="slide-y-transition">
                  <template #activator="{ props: menuProps }">
                    <VBtn
                      v-bind="menuProps"
                      icon="mdi-dots-vertical"
                      variant="tonal"
                      color="secondary"
                      size="small"
                    />
                  </template>

                  <VList density="compact" class="py-1" style="min-width: 190px;">
                    <VListItem
                      prepend-icon="mdi-eye-outline"
                      title="Lihat Pratinjau (Preview)"
                      :href="route('instructor.courses.preview', course.id)"
                      target="_blank"
                    />
                    <VListItem
                      prepend-icon="mdi-clipboard-check-outline"
                      title="Penilaian Kuis"
                      :href="route('instructor.courses.quiz-grading', course.id)"
                    />
                    <VListItem
                      prepend-icon="mdi-file-document-check-outline"
                      title="Penilaian Tugas"
                      :href="route('instructor.assignments.index', course.id)"
                    />
                    <VListItem
                      prepend-icon="mdi-book-check-outline"
                      title="Buku Nilai (Gradebook)"
                      :href="route('instructor.gradebook.index', course.id)"
                    />
                    <VDivider class="my-1" />
                    <VListItem
                      prepend-icon="mdi-delete-outline"
                      title="Hapus Kursus"
                      class="text-error"
                      @click="destroy(course)"
                    />
                  </VList>
                </VMenu>
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Table View Mode -->
    <VCard v-else-if="viewMode === 'table' && filteredCourses.length" flat border class="pa-2">
      <VTable density="compact" class="table-compact">
        <thead>
          <tr>
            <th class="text-start">Kursus</th>
            <th class="text-center">Status</th>
            <th class="text-center">Tingkat</th>
            <th class="text-center">Kurikulum</th>
            <th class="text-center">Siswa</th>
            <th class="text-end">Tindakan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in filteredCourses" :key="course.id">
            <!-- Title & Thumbnail -->
            <td style="max-width: 320px;">
              <div class="d-flex align-center ga-3 py-1">
                <div class="table-thumb rounded overflow-hidden flex-shrink-0">
                  <img
                    v-if="course.thumbnail"
                    :src="course.thumbnail"
                    :alt="course.title"
                    class="w-100 h-100"
                    style="object-fit: cover;"
                  >
                  <div v-else class="w-100 h-100 course-fallback-bg d-flex align-center justify-center text-white">
                    <VIcon icon="mdi-book-open-variant" size="18" />
                  </div>
                </div>
                <div class="text-truncate">
                  <span class="text-body-2 font-weight-bold text-truncate d-block" :title="course.title">
                    {{ course.title }}
                  </span>
                  <span class="text-caption text-medium-emphasis text-truncate d-block">
                    {{ course.description || 'Tanpa deskripsi' }}
                  </span>
                </div>
              </div>
            </td>

            <!-- Status -->
            <td class="text-center">
              <VChip
                :color="course.published ? 'success' : 'warning'"
                size="x-small"
                variant="tonal"
                class="font-weight-bold"
              >
                {{ course.published ? 'Diterbitkan' : 'Draf' }}
              </VChip>
            </td>

            <!-- Level -->
            <td class="text-center">
              <span class="text-caption text-capitalize text-medium-emphasis">
                {{ course.level || 'General' }}
              </span>
            </td>

            <!-- Structure -->
            <td class="text-center">
              <span class="text-caption">
                <strong>{{ course.sections?.length || 0 }}</strong> Bab • <strong>{{ getLessonCount(course) }}</strong> Materi
              </span>
            </td>

            <!-- Students -->
            <td class="text-center">
              <span class="text-body-2 font-weight-semibold">
                {{ course.enrollments_count ?? course.enrollments?.length ?? 0 }}
              </span>
            </td>

            <!-- Actions -->
            <td class="text-end">
              <div class="d-inline-flex align-center ga-1">
                <VBtn
                  size="small"
                  variant="tonal"
                  color="primary"
                  density="compact"
                  prepend-icon="mdi-pencil-outline"
                  :href="route('instructor.courses.edit', course.id)"
                >
                  Kelola
                </VBtn>

                <Link :href="route('instructor.assessment-analytics.course', course.id)" class="text-decoration-none">
                  <VBtn
                    size="small"
                    variant="text"
                    color="primary"
                    density="compact"
                    icon="mdi-chart-box-outline"
                    title="Analitik Penilaian"
                  />
                </Link>

                <VMenu location="bottom end">
                  <template #activator="{ props: menuProps }">
                    <VBtn
                      v-bind="menuProps"
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      density="compact"
                    />
                  </template>
                  <VList density="compact" class="py-1">
                    <VListItem
                      prepend-icon="mdi-eye-outline"
                      title="Pratinjau"
                      :href="route('instructor.courses.preview', course.id)"
                      target="_blank"
                    />
                    <VListItem
                      prepend-icon="mdi-clipboard-check-outline"
                      title="Kuis"
                      :href="route('instructor.courses.quiz-grading', course.id)"
                    />
                    <VListItem
                      prepend-icon="mdi-file-document-check-outline"
                      title="Tugas"
                      :href="route('instructor.assignments.index', course.id)"
                    />
                    <VDivider class="my-1" />
                    <VListItem
                      prepend-icon="mdi-delete-outline"
                      title="Hapus"
                      class="text-error"
                      @click="destroy(course)"
                    />
                  </VList>
                </VMenu>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Empty State -->
    <VCard v-if="!filteredCourses.length" flat border class="pa-10 text-center mt-3">
      <VAvatar size="64" color="primary" variant="tonal" class="mb-3">
        <VIcon icon="mdi-book-open-page-variant-outline" size="36" />
      </VAvatar>
      <h6 class="text-subtitle-1 font-weight-bold mb-1">Tidak Ada Kursus Ditemukan</h6>
      <p class="text-caption text-medium-emphasis mb-4">
        Silakan ubah kata kunci pencarian Anda atau mulai buat kursus pertama untuk siswa Anda.
      </p>
      <VBtn
        color="primary"
        variant="elevated"
        size="small"
        prepend-icon="mdi-plus"
        :href="route('instructor.courses.create')"
      >
        Buat Kursus Baru
      </VBtn>
    </VCard>

    <!-- Pagination -->
    <div v-if="courses.last_page > 1" class="d-flex justify-center mt-6">
      <VPagination
        :model-value="courses.current_page"
        :length="courses.last_page"
        :total-visible="5"
        density="compact"
        size="small"
        @update:model-value="(p) => router.get(route('instructor.courses.index'), { page: p, search: search, status: statusFilter }, { preserveState: true })"
      />
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.course-card {
  transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.course-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.course-thumbnail-wrapper {
  height: 155px;
  background-color: rgb(var(--v-theme-surface-variant));
}

.course-img {
  object-fit: cover;
  object-position: center;
  transition: transform 0.3s ease;
}
.course-card:hover .course-img {
  transform: scale(1.03);
}

.course-fallback-bg {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.9) 0%, rgba(var(--v-theme-primary), 0.65) 100%);
}

.thumbnail-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.35) 0%, transparent 40%, rgba(0, 0, 0, 0.2) 100%);
  pointer-events: none;
}

.stats-row {
  background-color: rgba(var(--v-theme-on-surface), 0.03);
}

.table-thumb {
  width: 44px;
  height: 34px;
}

.table-compact th {
  font-size: 0.75rem !important;
  font-weight: 600 !important;
}
.table-compact td {
  font-size: 0.8125rem !important;
}
</style>
