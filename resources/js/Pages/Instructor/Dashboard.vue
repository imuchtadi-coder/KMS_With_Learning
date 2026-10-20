<script setup>
import { ref, computed } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  totalCourses: { type: Number, default: 0 },
  publishedCourses: { type: Number, default: 0 },
  draftCourses: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  recentCourses: {
    type: Object,
    default: () => ({
      data: [],
      current_page: 1,
      last_page: 1,
      total: 0,
    }),
  },
  tenantName: {
    type: String,
    default: 'KMS Learning',
  },
})

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name || 'Instruktur')
const userAvatar = computed(() => page.props.auth?.user?.avatar_url)

// Search & Status filter state
const searchQuery = ref('')
const statusFilter = ref('all') // 'all' | 'published' | 'draft'

const filteredCourses = computed(() => {
  let list = props.recentCourses.data || []

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(c =>
      c.title?.toLowerCase().includes(q) ||
      c.description?.toLowerCase().includes(q) ||
      c.category?.name?.toLowerCase().includes(q)
    )
  }

  if (statusFilter.value === 'published') {
    list = list.filter(c => !!c.published)
  } else if (statusFilter.value === 'draft') {
    list = list.filter(c => !c.published)
  }

  return list
})

function getLessonCount(course) {
  if (!course.sections) return 0
  return course.sections.reduce((acc, s) => acc + (s.lessons?.length || 0), 0)
}

function formatLevel(lvl) {
  if (lvl === 'beginner') return 'Pemula'
  if (lvl === 'intermediate') return 'Menengah'
  if (lvl === 'advanced') return 'Lanjutan'
  return lvl ? lvl.charAt(0).toUpperCase() + lvl.slice(1) : 'Semua Level'
}

function destroyCourse(course) {
  if (!confirm(`Hapus kursus "${course.title}"? Seluruh modul dan materi terkait akan ikut dihapus.`)) return
  router.delete(route('instructor.courses.destroy', course.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="instructor-dashboard-page">
    <!-- 1. Hero Welcome Banner Card -->
    <VCard flat border class="hero-banner-card overflow-hidden mb-5 rounded-lg">
      <div class="hero-ambient-bg pa-5 pa-md-6 position-relative">
        <div class="d-flex align-center justify-space-between flex-wrap ga-4 position-relative z-1">
          <div class="d-flex align-center ga-4 flex-wrap">
            <VAvatar size="58" color="primary" variant="elevated" class="shadow-sm">
              <VImg v-if="userAvatar && !userAvatar.includes('ui-avatars')" :src="userAvatar" />
              <span v-else class="text-h4 font-weight-bold text-white">
                {{ userName.charAt(0).toUpperCase() }}
              </span>
            </VAvatar>

            <div>
              <div class="d-flex align-center ga-2 flex-wrap mb-1">
                <h4 class="text-h4 font-weight-bold mb-0">
                  Selamat Datang, {{ userName }} 👋🏻
                </h4>
                <VChip size="x-small" color="primary" variant="elevated" class="font-weight-semibold">
                  Instruktur
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mb-0" style="max-width: 620px;">
                Kelola kurikulum kursus, materi video/dokumen, evaluasi kuis, dan pantau progres peserta di platform <strong>{{ tenantName }}</strong>.
              </p>
            </div>
          </div>

          <div class="d-flex align-center ga-2 flex-wrap">
            <VBtn
              color="primary"
              variant="elevated"
              size="default"
              prepend-icon="mdi-plus-circle-outline"
              :href="route('instructor.courses.create')"
              class="font-weight-medium"
            >
              Buat Kursus Baru
            </VBtn>
            <VBtn
              variant="tonal"
              color="primary"
              size="default"
              prepend-icon="mdi-book-multiple-outline"
              :href="route('instructor.courses.index')"
              class="font-weight-medium"
            >
              Semua Kursus
            </VBtn>
          </div>
        </div>
      </div>
    </VCard>

    <!-- 2. Quick Metric Stats Cards -->
    <VRow class="mb-5">
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 stat-card rounded-lg transition-all">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block mb-1">Total Kursus</span>
              <div class="text-h4 font-weight-bold">{{ totalCourses }}</div>
              <span class="text-caption text-disabled mt-1 d-block">Kursus dibuat</span>
            </div>
            <VAvatar color="primary" variant="tonal" size="44" rounded="lg">
              <VIcon icon="mdi-book-open-page-variant-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 stat-card rounded-lg transition-all">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block mb-1">Diterbitkan</span>
              <div class="text-h4 font-weight-bold text-success">{{ publishedCourses }}</div>
              <span class="text-caption text-disabled mt-1 d-block">Dapat diakses siswa</span>
            </div>
            <VAvatar color="success" variant="tonal" size="44" rounded="lg">
              <VIcon icon="mdi-check-decagram-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 stat-card rounded-lg transition-all">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block mb-1">Draf Kursus</span>
              <div class="text-h4 font-weight-bold text-warning">{{ draftCourses }}</div>
              <span class="text-caption text-disabled mt-1 d-block">Sedang disiapkan</span>
            </div>
            <VAvatar color="warning" variant="tonal" size="44" rounded="lg">
              <VIcon icon="mdi-file-edit-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 stat-card rounded-lg transition-all">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block mb-1">Total Siswa Terdaftar</span>
              <div class="text-h4 font-weight-bold text-info">{{ totalStudents }}</div>
              <span class="text-caption text-disabled mt-1 d-block">Di seluruh kursus</span>
            </div>
            <VAvatar color="info" variant="tonal" size="44" rounded="lg">
              <VIcon icon="mdi-account-group-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- 3. Quick Action Shortcut Bar -->
    <VCard flat border class="pa-3 mb-5 rounded-lg bg-surface-variant-subtle">
      <div class="d-flex align-center justify-space-between flex-wrap ga-3">
        <div class="d-flex align-center ga-2">
          <VIcon icon="mdi-lightning-bolt-outline" color="primary" size="20" />
          <span class="text-caption font-weight-bold text-uppercase tracking-wider">Aksi Cepat</span>
        </div>
        <div class="d-flex align-center flex-wrap ga-2">
          <VBtn
            size="small"
            variant="tonal"
            color="primary"
            prepend-icon="mdi-plus"
            :href="route('instructor.courses.create')"
          >
            Kursus Baru
          </VBtn>
          <VBtn
            size="small"
            variant="outlined"
            prepend-icon="mdi-format-list-bulleted"
            :href="route('instructor.courses.index')"
          >
            Kelola Kurikulum
          </VBtn>
          <VBtn
            v-if="route().has('instructor.analytics.dashboard')"
            size="small"
            variant="outlined"
            prepend-icon="mdi-chart-line"
            :href="route('instructor.analytics.dashboard')"
          >
            Analitik Pembelajaran
          </VBtn>
          <VBtn
            v-if="route().has('articles.index')"
            size="small"
            variant="outlined"
            prepend-icon="mdi-book-open-outline"
            :href="route('articles.index')"
          >
            Knowledge Base
          </VBtn>
        </div>
      </div>
    </VCard>

    <!-- 4. Recent Courses Header & Filter Section -->
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
      <div>
        <h5 class="text-h5 font-weight-bold mb-0">Kursus Terbaru</h5>
        <p class="text-caption text-medium-emphasis mb-0">Kelola dan update materi kursus yang baru saja aktif</p>
      </div>

      <div class="d-flex align-center flex-wrap ga-2">
        <!-- Search Field -->
        <VTextField
          v-model="searchQuery"
          placeholder="Cari kursus..."
          density="compact"
          variant="outlined"
          hide-details
          prepend-inner-icon="mdi-magnify"
          clearable
          style="width: 220px;"
        />

        <!-- Status Filter Chips -->
        <VBtnToggle
          v-model="statusFilter"
          mandatory
          density="compact"
          color="primary"
          variant="outlined"
          class="status-toggle"
        >
          <VBtn value="all" size="small">Semua</VBtn>
          <VBtn value="published" size="small">Terbit</VBtn>
          <VBtn value="draft" size="small">Draf</VBtn>
        </VBtnToggle>
      </div>
    </div>

    <!-- 5. Course Cards Grid -->
    <!-- 5. Course Cards Grid (Compact & User-Friendly) -->
    <VRow v-if="filteredCourses.length" class="ga-y-2">
      <VCol
        v-for="course in filteredCourses"
        :key="course.id"
        cols="12"
        md="6"
        lg="4"
        class="pb-2"
      >
        <VCard flat border class="course-card h-100 d-flex flex-column rounded-lg overflow-hidden transition-all">
          <!-- Thumbnail & Badges (Compact Height) -->
          <div class="position-relative overflow-hidden" style="height: 108px;">
            <img
              v-if="course.thumbnail"
              :src="course.thumbnail"
              :alt="course.title"
              class="w-100 h-100"
              style="object-fit: cover; object-position: center;"
            />
            <div
              v-else
              class="w-100 h-100 d-flex align-center justify-center course-placeholder-bg"
            >
              <VIcon icon="mdi-school-outline" size="36" color="white" style="opacity: 0.4;" />
            </div>

            <!-- Gradient Shade -->
            <div
              class="position-absolute w-100 h-100"
              style="inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.05) 45%, rgba(0,0,0,0.65) 100%); pointer-events: none;"
            />

            <!-- Top Left: Level Badge -->
            <div class="position-absolute" style="top: 6px; left: 6px; z-index: 2;">
              <VChip
                size="x-small"
                density="compact"
                color="surface"
                variant="elevated"
                class="font-weight-semibold text-caption px-1-5"
                style="height: 20px; font-size: 0.68rem;"
              >
                <VIcon icon="mdi-signal" size="11" class="me-1" />
                {{ formatLevel(course.level) }}
              </VChip>
            </div>

            <!-- Top Right: Status Badge -->
            <div class="position-absolute" style="top: 6px; right: 6px; z-index: 2;">
              <VChip
                :color="course.published ? 'success' : 'warning'"
                size="x-small"
                density="compact"
                variant="elevated"
                class="font-weight-bold text-caption px-1-5"
                style="height: 20px; font-size: 0.68rem;"
              >
                <VIcon :icon="course.published ? 'mdi-check-circle' : 'mdi-clock-outline'" size="11" class="me-1" />
                {{ course.published ? 'Diterbitkan' : 'Draf' }}
              </VChip>
            </div>

            <!-- Bottom Left: Category (if any) -->
            <div v-if="course.category" class="position-absolute" style="bottom: 6px; left: 6px; z-index: 2;">
              <span class="text-caption text-white font-weight-medium bg-black-subtle px-1-5 py-0-5 rounded" style="font-size: 0.65rem;">
                {{ course.category.name }}
              </span>
            </div>
          </div>

          <!-- Card Content (Dense & Compact) -->
          <VCardText class="pa-3 flex-grow-1 d-flex flex-column">
            <!-- Title -->
            <h6 class="text-subtitle-2 font-weight-bold mb-1 line-clamp-2" :title="course.title" style="min-height: 34px; line-height: 1.25; font-size: 0.84rem;">
              {{ course.title }}
            </h6>

            <!-- Description -->
            <p class="text-caption text-medium-emphasis mb-2 line-clamp-2" style="font-size: 0.75rem; line-height: 1.35; min-height: 30px;">
              {{ course.description || 'Tidak ada deskripsi kursus.' }}
            </p>

            <!-- Metadata Row (Padat & Rapi) -->
            <div class="course-meta-row py-1-5 px-2 rounded bg-surface-variant-subtle mb-2-5 d-flex align-center justify-space-around">
              <div class="text-center px-1">
                <span class="font-weight-bold d-block" style="font-size: 0.8125rem; line-height: 1.1;">{{ course.sections?.length || 0 }}</span>
                <span class="text-disabled" style="font-size: 0.65rem; line-height: 1;">Bagian</span>
              </div>
              <VDivider vertical style="height: 14px;" />
              <div class="text-center px-1">
                <span class="font-weight-bold d-block" style="font-size: 0.8125rem; line-height: 1.1;">{{ getLessonCount(course) }}</span>
                <span class="text-disabled" style="font-size: 0.65rem; line-height: 1;">Materi</span>
              </div>
              <VDivider vertical style="height: 14px;" />
              <div class="text-center px-1">
                <span class="font-weight-bold d-block text-primary" style="font-size: 0.8125rem; line-height: 1.1;">{{ course.enrollments_count || 0 }}</span>
                <span class="text-disabled" style="font-size: 0.65rem; line-height: 1;">Siswa</span>
              </div>
              <VDivider vertical style="height: 14px;" />
              <div class="text-center px-1">
                <span class="font-weight-bold d-block" style="font-size: 0.8125rem; line-height: 1.1;">{{ course.quizzes_count || 0 }}</span>
                <span class="text-disabled" style="font-size: 0.65rem; line-height: 1;">Kuis</span>
              </div>
            </div>

            <VSpacer />

            <!-- Card Actions -->
            <div class="d-flex align-center ga-1-5 pt-2 border-top mt-auto">
              <VBtn
                variant="tonal"
                color="primary"
                size="small"
                density="compact"
                class="flex-grow-1 font-weight-medium text-caption"
                prepend-icon="mdi-cog-outline"
                style="height: 30px; font-size: 0.75rem;"
                :href="route('instructor.courses.show', course.id)"
              >
                Detail & Kelola
              </VBtn>

              <VBtn
                variant="elevated"
                color="primary"
                size="small"
                density="compact"
                icon="mdi-pencil-outline"
                style="width: 30px; height: 30px;"
                :href="route('instructor.courses.edit', course.id)"
                title="Edit Info Kursus"
              />

              <!-- More Options Menu -->
              <VMenu location="bottom end">
                <template #activator="{ props: menuProps }">
                  <VBtn
                    v-bind="menuProps"
                    variant="text"
                    icon="mdi-dots-vertical"
                    size="small"
                    density="compact"
                    style="width: 28px; height: 30px;"
                  />
                </template>
                <VList density="compact" class="py-1">
                  <VListItem
                    :href="route('instructor.courses.preview', course.id)"
                    prepend-icon="mdi-eye-outline"
                    title="Pratinjau Kursus"
                  />
                  <VListItem
                    :href="route('instructor.quizzes.index', course.id)"
                    prepend-icon="mdi-checkbox-marked-circle-outline"
                    title="Kelola Kuis"
                  />
                  <VDivider class="my-1" />
                  <VListItem
                    prepend-icon="mdi-trash-can-outline"
                    title="Hapus Kursus"
                    class="text-error"
                    @click="destroyCourse(course)"
                  />
                </VList>
              </VMenu>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Empty State -->
    <VCard v-else flat border class="pa-10 text-center rounded-lg">
      <VAvatar size="64" color="primary" variant="tonal" class="mb-3">
        <VIcon icon="mdi-book-open-blank-variant" size="32" color="primary" />
      </VAvatar>
      <h5 class="text-h5 font-weight-bold mb-1">
        {{ searchQuery || statusFilter !== 'all' ? 'Kursus tidak ditemukan' : 'Belum Ada Kursus Dibuat' }}
      </h5>
      <p class="text-body-2 text-medium-emphasis mb-4" style="max-width: 480px; margin-inline: auto;">
        {{ searchQuery || statusFilter !== 'all'
          ? 'Coba ubah kata kunci pencarian atau sesuaikan filter status kursus di atas.'
          : 'Mulai bangun kurikulum Anda dan bagikan pengetahuan kepada para siswa dengan membuat kursus pertama Anda.'
        }}
      </p>
      <div class="d-flex align-center justify-center ga-2">
        <VBtn
          v-if="searchQuery || statusFilter !== 'all'"
          variant="tonal"
          size="default"
          @click="searchQuery = ''; statusFilter = 'all'"
        >
          Reset Filter
        </VBtn>
        <VBtn
          color="primary"
          variant="elevated"
          size="default"
          prepend-icon="mdi-plus"
          :href="route('instructor.courses.create')"
        >
          Buat Kursus Pertama
        </VBtn>
      </div>
    </VCard>

    <!-- Pagination -->
    <div
      v-if="recentCourses.last_page > 1"
      class="d-flex justify-center mt-6"
    >
      <VPagination
        :model-value="recentCourses.current_page"
        :length="recentCourses.last_page"
        :total-visible="5"
        density="comfortable"
        @update:model-value="(page) => router.get(
          route('instructor.dashboard'),
          { page },
          { preserveState: true, preserveScroll: true }
        )"
      />
    </div>
  </div>
</template>

<style scoped>
.hero-ambient-bg {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08) 0%, rgba(var(--v-theme-surface), 0.95) 100%);
  border-bottom: 1px solid rgba(var(--v-border-color), 0.08);
}

.stat-card {
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

.course-card {
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.course-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
}

.course-placeholder-bg {
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgba(var(--v-theme-primary), 0.8) 100%);
}

.bg-surface-variant-subtle {
  background-color: rgba(var(--v-theme-on-surface), 0.03);
}

.bg-black-subtle {
  background-color: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.border-top {
  border-top: 1px solid rgba(var(--v-border-color), 0.08);
}

.px-1-5 {
  padding-left: 6px !important;
  padding-right: 6px !important;
}

.py-0-5 {
  padding-top: 2px !important;
  padding-bottom: 2px !important;
}

.py-1-5 {
  padding-top: 5px !important;
  padding-bottom: 5px !important;
}

.mb-2-5 {
  margin-bottom: 10px !important;
}

.ga-1-5 {
  gap: 6px !important;
}
</style>
