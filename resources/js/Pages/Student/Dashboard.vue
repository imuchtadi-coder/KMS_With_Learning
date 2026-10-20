<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  recentEnrollments: {
    type: Array,
    default: () => [],
  },
  totalEnrolled: {
    type: Number,
    default: 0,
  },
  inProgress: {
    type: Number,
    default: 0,
  },
  completed: {
    type: Number,
    default: 0,
  },
  certificates: {
    type: Array,
    default: () => [],
  },
  tenantName: {
    type: String,
    default: 'KMS Learning',
  },
})

const page = usePage()
const userName = computed(() => page.props.auth?.user?.name || 'Siswa')
const userAvatar = computed(() => page.props.auth?.user?.avatar_url)

const searchQuery = ref('')

const filteredEnrollments = computed(() => {
  if (!searchQuery.value.trim()) return props.recentEnrollments
  const q = searchQuery.value.toLowerCase()
  return props.recentEnrollments.filter(c =>
    c.courseTitle?.toLowerCase().includes(q) ||
    c.instructorName?.toLowerCase().includes(q)
  )
})

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function goToCourse(course) {
  router.get(route('student.courses.show', course.id))
}
</script>

<template>
  <div class="student-dashboard-page">
    <!-- 1. Hero Welcome Banner Card -->
    <VCard flat border class="hero-banner-card overflow-hidden mb-5 rounded-lg">
      <div class="hero-ambient-bg pa-5 pa-md-6 position-relative">
        <div class="d-flex align-center justify-space-between flex-wrap ga-4 position-relative z-1">
          <div class="d-flex align-center ga-4 flex-wrap">
            <VAvatar size="64" color="primary" variant="elevated" class="shadow-md">
              <VImg v-if="userAvatar && !userAvatar.includes('ui-avatars')" :src="userAvatar" />
              <span v-else class="text-h4 font-weight-bold text-white">
                {{ userName.charAt(0).toUpperCase() }}
              </span>
            </VAvatar>

            <div>
              <div class="d-flex align-center ga-2 flex-wrap mb-1">
                <h4 class="text-h4 font-weight-bold mb-0">
                  Selamat Datang Kembali, {{ userName }} 👋🏻
                </h4>
                <VChip size="x-small" color="primary" variant="elevated" class="font-weight-semibold">
                  Siswa Aktif
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mb-0" style="max-width: 580px;">
                Lanjutkan progres belajar Anda di <strong>{{ tenantName }}</strong>. Selesaikan modul, raih pemahaman baru, dan koleksi sertifikat kompetensi Anda!
              </p>
            </div>
          </div>

          <div class="d-flex align-center ga-2 flex-wrap">
            <VBtn
              color="primary"
              variant="elevated"
              size="default"
              prepend-icon="mdi-compass-outline"
              :href="route('student.courses.list')"
              class="font-weight-medium"
            >
              Katalog Kursus
            </VBtn>
            <VBtn
              variant="tonal"
              color="secondary"
              size="default"
              prepend-icon="mdi-map-marker-path"
              :href="route('student.learning-paths.index')"
            >
              Jalur Pembelajaran
            </VBtn>
          </div>
        </div>
      </div>
    </VCard>

    <!-- 2. Four Metrics KPI Cards -->
    <VRow class="mb-5">
      <!-- 1. Enrolled Courses -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Kursus Terdaftar</span>
              <span class="text-h4 font-weight-bold text-primary mt-1 d-block">{{ totalEnrolled }}</span>
              <span class="text-caption text-medium-emphasis">Kelas aktif diikuti</span>
            </div>
            <VAvatar color="primary" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-book-multiple-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <!-- 2. In Progress -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Sedang Berjalan</span>
              <span class="text-h4 font-weight-bold text-warning mt-1 d-block">{{ inProgress }}</span>
              <span class="text-caption text-medium-emphasis">Modul belum tuntas</span>
            </div>
            <VAvatar color="warning" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-clock-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <!-- 3. Completed -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Kursus Selesai</span>
              <span class="text-h4 font-weight-bold text-success mt-1 d-block">{{ completed }}</span>
              <span class="text-caption text-medium-emphasis">100% materi selesai</span>
            </div>
            <VAvatar color="success" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-check-decagram-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <!-- 4. Certificates -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Sertifikat Kelulusan</span>
              <span class="text-h4 font-weight-bold text-info mt-1 d-block">{{ certificates.length }}</span>
              <span class="text-caption text-medium-emphasis">Sertifikat resmi terbit</span>
            </div>
            <VAvatar color="info" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-certificate-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- 3. Main Content: Course Progress & Certificates Grid -->
    <VRow class="mb-5">
      <!-- Left Column: Active Learning Courses (8 cols) -->
      <VCol cols="12" lg="8">
        <VCard flat border class="pa-4 h-100 rounded-lg d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
              <div>
                <h6 class="text-subtitle-1 font-weight-bold mb-0">
                  Kursus yang Sedang Anda Ikuti
                </h6>
                <span class="text-caption text-medium-emphasis">
                  Pantau progres materi dan lanjutkan pembelajaran Anda.
                </span>
              </div>

              <!-- Search input -->
              <VTextField
                v-model="searchQuery"
                placeholder="Cari kursus saya..."
                density="compact"
                variant="outlined"
                prepend-inner-icon="mdi-magnify"
                clearable
                hide-details
                style="max-width: 240px; min-width: 180px;"
              />
            </div>

            <!-- Enrolled Courses Table / List -->
            <div v-if="filteredEnrollments.length" class="d-flex flex-column ga-3">
              <div
                v-for="course in filteredEnrollments"
                :key="course.id"
                class="course-progress-item pa-3 rounded border bg-surface transition-all"
              >
                <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-2">
                  <div class="d-flex align-center ga-3 text-truncate flex-grow-1" style="min-width: 220px;">
                    <!-- Thumbnail / Icon -->
                    <VAvatar
                      rounded
                      size="44"
                      :color="course.color || 'primary'"
                      variant="tonal"
                      class="flex-shrink-0"
                    >
                      <img
                        v-if="course.thumbnail"
                        :src="course.thumbnail"
                        class="w-100 h-100"
                        style="object-fit: cover;"
                      >
                      <VIcon v-else :icon="course.logo || 'mdi-book-open-page-variant'" size="24" />
                    </VAvatar>

                    <!-- Title & Instructor -->
                    <div class="text-truncate">
                      <span
                        class="text-body-2 font-weight-bold text-truncate d-block cursor-pointer course-title-link"
                        :title="course.courseTitle"
                        @click="goToCourse(course)"
                      >
                        {{ course.courseTitle }}
                      </span>
                      <span class="text-caption text-medium-emphasis d-block">
                        Pengajar: {{ course.instructorName || 'Instruktur' }}
                      </span>
                    </div>
                  </div>

                  <!-- Action Button -->
                  <div class="d-flex align-center ga-2 flex-shrink-0">
                    <VChip
                      v-if="course.progress === 100"
                      size="x-small"
                      color="success"
                      variant="tonal"
                      class="font-weight-bold"
                    >
                      Selesai
                    </VChip>
                    <VBtn
                      variant="elevated"
                      color="primary"
                      size="small"
                      prepend-icon="mdi-play-circle-outline"
                      @click="goToCourse(course)"
                    >
                      Lanjutkan
                    </VBtn>
                  </div>
                </div>

                <!-- Progress Bar Row -->
                <div class="mt-2 pt-2 border-t d-flex align-center justify-space-between ga-3">
                  <div class="flex-grow-1 me-3">
                    <div class="d-flex align-center justify-space-between mb-1 text-caption">
                      <span class="text-medium-emphasis">
                        {{ course.completedTasks }} dari {{ course.totalTasks }} Materi Tuntas
                      </span>
                      <span class="font-weight-bold text-primary">
                        {{ course.progress }}%
                      </span>
                    </div>
                    <VProgressLinear
                      :model-value="course.progress"
                      :color="course.progress === 100 ? 'success' : 'primary'"
                      height="6"
                      rounded
                    />
                  </div>

                  <span v-if="course.time" class="text-caption text-medium-emphasis flex-shrink-0">
                    <VIcon icon="mdi-clock-outline" size="14" class="me-1" />
                    {{ course.time }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Empty State for Enrolled Courses -->
            <div v-else class="text-center pa-10 text-medium-emphasis">
              <VIcon icon="mdi-book-open-blank-variant" size="48" class="mb-2 opacity-50" />
              <h6 class="text-subtitle-1 font-weight-bold mb-1">
                {{ searchQuery ? 'Tidak Ada Kursus Sesuai Pencarian' : 'Belum Ada Kursus yang Diikuti' }}
              </h6>
              <p class="text-caption text-medium-emphasis mb-3">
                {{ searchQuery ? 'Coba gunakan kata kunci pencarian yang lain.' : 'Jelajahi berbagai materi kurikulum yang tersedia untuk mulai belajar.' }}
              </p>
              <VBtn
                color="primary"
                variant="tonal"
                size="small"
                prepend-icon="mdi-compass-outline"
                :href="route('student.courses.list')"
              >
                Jelajahi Katalog Kursus
              </VBtn>
            </div>
          </div>

          <div v-if="recentEnrollments.length > 5" class="pt-3 text-center border-t mt-3">
            <Link :href="route('student.enrollments')" class="text-primary text-caption font-weight-bold text-decoration-none">
              Lihat Seluruh Kursus Saya ({{ totalEnrolled }})
              <VIcon icon="mdi-chevron-right" size="16" />
            </Link>
          </div>
        </VCard>
      </VCol>

      <!-- Right Column: Certificates & Achievements (4 cols) -->
      <VCol cols="12" lg="4">
        <VCard flat border class="pa-4 h-100 rounded-lg d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <h6 class="text-subtitle-1 font-weight-bold mb-0">
                Sertifikat Saya
              </h6>
              <VChip size="x-small" color="info" variant="tonal" class="font-weight-bold">
                {{ certificates.length }} Sertifikat
              </VChip>
            </div>
            <p class="text-caption text-medium-emphasis mb-3">
              Bukti resmi kelulusan kompetensi yang telah Anda selesaikan.
            </p>

            <!-- Certificates List -->
            <div v-if="certificates.length > 0" class="d-flex flex-column ga-2">
              <div
                v-for="cert in certificates"
                :key="cert.id"
                class="pa-3 rounded border bg-surface d-flex align-center justify-space-between ga-2 cert-item"
              >
                <div class="d-flex align-center ga-3 text-truncate">
                  <VAvatar color="info" variant="tonal" size="36" rounded class="flex-shrink-0">
                    <VIcon icon="mdi-certificate" size="20" />
                  </VAvatar>
                  <div class="text-truncate">
                    <span class="text-body-2 font-weight-bold text-truncate d-block" :title="cert.course?.title">
                      {{ cert.course?.title || 'Sertifikat Kelulusan' }}
                    </span>
                    <span class="text-caption text-medium-emphasis d-block">
                      Terbit: {{ formatDate(cert.issued_at) }}
                    </span>
                  </div>
                </div>

                <div class="d-flex align-center ga-1 flex-shrink-0">
                  <VBtn
                    icon="mdi-eye-outline"
                    variant="tonal"
                    color="secondary"
                    size="small"
                    density="compact"
                    title="Lihat Sertifikat"
                    :href="route('student.certificates.view', cert.id)"
                    target="_blank"
                  />
                  <VBtn
                    icon="mdi-download-outline"
                    variant="tonal"
                    color="primary"
                    size="small"
                    density="compact"
                    title="Unduh PDF"
                    :href="route('student.certificates.download', cert.id)"
                    target="_blank"
                  />
                </div>
              </div>
            </div>

            <!-- Empty State Certificates -->
            <div v-else class="text-center pa-6 border border-dashed rounded bg-surface">
              <VIcon icon="mdi-certificate-outline" size="40" class="mb-2 opacity-50 text-medium-emphasis" />
              <p class="text-body-2 font-weight-medium mb-1">Belum Ada Sertifikat</p>
              <p class="text-caption text-medium-emphasis mb-3">
                Selesaikan 100% materi dan ujian pada kursus untuk mendapatkan sertifikat resmi pertama Anda.
              </p>
              <VBtn
                color="primary"
                variant="tonal"
                size="small"
                :href="route('student.courses.list')"
              >
                Mulai Belajar Sekarang
              </VBtn>
            </div>
          </div>

          <!-- Motivational Info Box -->
          <div class="mt-4 pa-3 rounded border bg-light-primary">
            <div class="d-flex align-start ga-2">
              <VIcon icon="mdi-trophy-outline" color="primary" size="20" class="mt-0" />
              <div>
                <span class="text-caption font-weight-bold text-primary d-block">
                  Tips Produktivitas
                </span>
                <span class="text-caption text-medium-emphasis d-block">
                  Luangkan waktu 15–30 menit setiap hari untuk menyelesaikan kuis dan menonton modul video interaktif.
                </span>
              </div>
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- 4. Quick Learning Shortcuts Cards -->
    <VRow>
      <VCol cols="12" sm="6" md="3">
        <Link :href="route('student.learning-paths.index')" class="text-decoration-none">
          <VCard flat border hover class="pa-3 shortcut-card">
            <div class="d-flex align-center ga-3">
              <VAvatar color="primary" variant="tonal" size="40" rounded>
                <VIcon icon="mdi-map-marker-path" size="22" />
              </VAvatar>
              <div>
                <span class="text-body-2 font-weight-bold d-block text-high-emphasis">Jalur Pembelajaran</span>
                <span class="text-caption text-medium-emphasis">Kurikulum berjenjang terstruktur</span>
              </div>
            </div>
          </VCard>
        </Link>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <Link :href="route('discussions.index')" class="text-decoration-none">
          <VCard flat border hover class="pa-3 shortcut-card">
            <div class="d-flex align-center ga-3">
              <VAvatar color="teal" variant="tonal" size="40" rounded>
                <VIcon icon="mdi-forum-outline" size="22" />
              </VAvatar>
              <div>
                <span class="text-body-2 font-weight-bold d-block text-high-emphasis">Forum Q&A Terbuka</span>
                <span class="text-caption text-medium-emphasis">Tanya jawab & bantuan instruktur</span>
              </div>
            </div>
          </VCard>
        </Link>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <Link :href="route('student.live-classes.index')" class="text-decoration-none">
          <VCard flat border hover class="pa-3 shortcut-card">
            <div class="d-flex align-center ga-3">
              <VAvatar color="error" variant="tonal" size="40" rounded>
                <VIcon icon="mdi-video-outline" size="22" />
              </VAvatar>
              <div>
                <span class="text-body-2 font-weight-bold d-block text-high-emphasis">Kelas Langsung</span>
                <span class="text-caption text-medium-emphasis">Jadwal webinar & sesi interaktif</span>
              </div>
            </div>
          </VCard>
        </Link>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <Link :href="route('student.grades.index')" class="text-decoration-none">
          <VCard flat border hover class="pa-3 shortcut-card">
            <div class="d-flex align-center ga-3">
              <VAvatar color="warning" variant="tonal" size="40" rounded>
                <VIcon icon="mdi-school-outline" size="22" />
              </VAvatar>
              <div>
                <span class="text-body-2 font-weight-bold d-block text-high-emphasis">Transkrip & Nilai</span>
                <span class="text-caption text-medium-emphasis">Rekap nilai kuis dan tugas</span>
              </div>
            </div>
          </VCard>
        </Link>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.hero-ambient-bg {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08) 0%, rgba(var(--v-theme-primary), 0.02) 100%);
}

.metric-card,
.shortcut-card {
  transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
}
.metric-card:hover,
.shortcut-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

.course-progress-item {
  transition: all 0.15s ease-in-out;
}
.course-progress-item:hover {
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
  background-color: rgba(var(--v-theme-primary), 0.02) !important;
}

.course-title-link:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

.border-t {
  border-top: 1px solid rgba(var(--v-border-color), 0.1);
}

.cursor-pointer {
  cursor: pointer;
}
</style>
