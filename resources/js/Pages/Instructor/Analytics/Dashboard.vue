<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  totalCourses: { type: Number, default: 0 },
  totalEnrollments: { type: Number, default: 0 },
  totalQuizzes: { type: Number, default: 0 },
  totalAssignments: { type: Number, default: 0 },
  overallAvgScore: { type: Number, default: 0 },
  overallPassRate: { type: Number, default: 0 },
  coursePerformance: { type: Array, default: () => [] },
  scoreDistribution: { type: Object, default: () => ({ '85-100': 0, '70-84': 0, '55-69': 0, '0-54': 0 }) },
})

// Chart view metric toggle: 'avg_score' or 'pass_rate'
const activeChartMetric = ref('avg_score')

// Table filter and search
const searchQuery = ref('')
const statusFilter = ref('all') // 'all', 'passed', 'attention'

// Total assessed students from distribution
const totalDistributedStudents = computed(() => {
  return Object.values(props.scoreDistribution).reduce((a, b) => a + Number(b), 0)
})

// Calculate percentage for a bucket
const getBucketPct = (count) => {
  if (!totalDistributedStudents.value || totalDistributedStudents.value === 0) return 0
  return Math.round((Number(count) / totalDistributedStudents.value) * 100)
}

// Filtered course performance list
const filteredCourses = computed(() => {
  let list = props.coursePerformance || []
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(c => c.title && c.title.toLowerCase().includes(q))
  }
  if (statusFilter.value === 'passed') {
    list = list.filter(c => c.avg_score !== null && c.avg_score >= 70)
  } else if (statusFilter.value === 'attention') {
    list = list.filter(c => c.avg_score !== null && c.avg_score < 70)
  }
  return list
})

// Top performing course
const topCourse = computed(() => {
  const withScores = (props.coursePerformance || []).filter(c => c.avg_score !== null)
  if (!withScores.length) return null
  return [...withScores].sort((a, b) => b.avg_score - a.avg_score)[0]
})

// Courses needing attention (avg_score < 70)
const attentionCoursesCount = computed(() => {
  return (props.coursePerformance || []).filter(c => c.avg_score !== null && c.avg_score < 70).length
})

// Color helper
const getScoreColor = (val) => {
  if (val === null || val === undefined) return 'secondary'
  if (val >= 85) return 'success'
  if (val >= 70) return 'primary'
  if (val >= 55) return 'warning'
  return 'error'
}
</script>

<template>
  <div class="assessment-analytics-page">
    <!-- Header Section -->
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div>
        <div class="d-flex align-center ga-2 mb-1">
          <VIcon icon="mdi-chart-box-outline" color="primary" size="24" />
          <h4 class="text-h4 font-weight-bold mb-0">Analitik Penilaian Kursus</h4>
        </div>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Evaluasi capaian rata-rata nilai, tingkat kelulusan, dan sebaran kemampuan siswa di seluruh materi ajar.
        </p>
      </div>

      <div class="d-flex align-center ga-2">
        <VBtn
          color="primary"
          variant="tonal"
          size="small"
          prepend-icon="mdi-book-multiple-outline"
          :href="route('instructor.courses.index')"
        >
          Kelola Kursus Saya
        </VBtn>
      </div>
    </div>

    <!-- 4 KPI Metrics Card Grid -->
    <VRow class="mb-5">
      <!-- 1. Overall Avg Score -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Rata-rata Nilai Keseluruhan</span>
            <VAvatar color="primary" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-chart-line-variant" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold">
              {{ overallAvgScore !== null ? overallAvgScore : 0 }}%
            </span>
            <VChip
              size="x-small"
              :color="overallAvgScore >= 70 ? 'success' : 'warning'"
              variant="tonal"
              class="font-weight-bold"
            >
              KKM: 70%
            </VChip>
          </div>
          <VProgressLinear
            :model-value="overallAvgScore || 0"
            :color="getScoreColor(overallAvgScore)"
            height="6"
            rounded
          />
          <span class="text-caption text-medium-emphasis mt-2 d-block">
            {{ overallAvgScore >= 70 ? 'Target kelulusan tercapai dengan baik' : 'Perlu penguatan materi remedial' }}
          </span>
        </VCard>
      </VCol>

      <!-- 2. Overall Pass Rate -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Tingkat Kelulusan (Pass Rate)</span>
            <VAvatar color="success" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-school-outline" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold text-success">
              {{ overallPassRate !== null ? overallPassRate : 0 }}%
            </span>
            <span class="text-caption text-medium-emphasis">dari seluruh siswa</span>
          </div>
          <VProgressLinear
            :model-value="overallPassRate || 0"
            color="success"
            height="6"
            rounded
          />
          <span class="text-caption text-medium-emphasis mt-2 d-block">
            Siswa dengan nilai akhir ≥ 70
          </span>
        </VCard>
      </VCol>

      <!-- 3. Total Assessments -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Instrumen Penilaian</span>
            <VAvatar color="info" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-clipboard-check-outline" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold">
              {{ totalQuizzes + totalAssignments }}
            </span>
            <span class="text-caption text-medium-emphasis">Item Ujian</span>
          </div>
          <div class="d-flex align-center ga-2 mt-2">
            <VChip size="x-small" color="info" variant="tonal">
              {{ totalQuizzes }} Kuis
            </VChip>
            <VChip size="x-small" color="secondary" variant="tonal">
              {{ totalAssignments }} Tugas
            </VChip>
          </div>
        </VCard>
      </VCol>

      <!-- 4. Total Enrollments & Courses -->
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Siswa & Kursus Diampu</span>
            <VAvatar color="warning" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-account-group-outline" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold">
              {{ totalEnrollments }}
            </span>
            <span class="text-caption text-medium-emphasis">Partisipasi Siswa</span>
          </div>
          <span class="text-caption text-medium-emphasis mt-1 d-block">
            Tersebar di <strong>{{ totalCourses }}</strong> kelas aktif Anda
          </span>
        </VCard>
      </VCol>
    </VRow>

    <!-- Main Chart & Distribution Row -->
    <VRow class="mb-5">
      <!-- Left Column: Course Performance Interactive Visual Chart -->
      <VCol cols="12" lg="8">
        <VCard flat border class="pa-4 h-100">
          <div class="d-flex align-center justify-space-between mb-3 flex-wrap ga-2">
            <div>
              <h6 class="text-subtitle-1 font-weight-bold mb-0">
                Grafik Performa Nilai per Kursus
              </h6>
              <span class="text-caption text-medium-emphasis">
                Bandingkan hasil belajar antar kursus terhadap standar KKM (70%).
              </span>
            </div>

            <!-- Metric Toggle Buttons -->
            <VBtnToggle
              v-model="activeChartMetric"
              mandatory
              density="compact"
              color="primary"
              variant="outlined"
            >
              <VBtn value="avg_score" size="small">
                Rata-rata Nilai
              </VBtn>
              <VBtn value="pass_rate" size="small">
                Tingkat Kelulusan
              </VBtn>
            </VBtnToggle>
          </div>

          <!-- Interactive Chart Content -->
          <div v-if="coursePerformance.length" class="chart-container pt-2">
            <!-- Benchmark Target Line Indicator -->
            <div class="benchmark-indicator mb-3 d-flex align-center justify-space-between px-2 py-1 bg-surface-variant rounded">
              <span class="text-caption font-weight-medium text-medium-emphasis">
                <VIcon icon="mdi-flag-outline" size="14" class="me-1" />
                Standar Kelulusan Minimal (KKM):
              </span>
              <span class="text-caption font-weight-bold text-primary">70%</span>
            </div>

            <div class="course-bars-wrapper d-flex flex-column ga-3">
              <div
                v-for="cp in coursePerformance"
                :key="cp.id"
                class="course-bar-item pa-2 rounded border"
              >
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center ga-2 text-truncate me-2">
                    <span class="text-body-2 font-weight-semibold text-truncate">
                      {{ cp.title }}
                    </span>
                    <VChip
                      v-if="cp.published"
                      size="x-small"
                      color="success"
                      variant="tonal"
                      class="text-caption"
                    >
                      Aktif
                    </VChip>
                    <VChip
                      v-else
                      size="x-small"
                      color="secondary"
                      variant="tonal"
                      class="text-caption"
                    >
                      Draf
                    </VChip>
                  </div>

                  <div class="d-flex align-center ga-2 flex-shrink-0">
                    <span class="text-caption text-medium-emphasis">
                      {{ cp.enrollments }} siswa
                    </span>
                    <span
                      class="text-body-2 font-weight-bold"
                      :class="((activeChartMetric === 'avg_score' ? cp.avg_score : cp.pass_rate) || 0) >= 70 ? 'text-success' : 'text-error'"
                    >
                      {{ (activeChartMetric === 'avg_score' ? cp.avg_score : cp.pass_rate) !== null ? (activeChartMetric === 'avg_score' ? cp.avg_score : cp.pass_rate) + '%' : 'Belum Ada Nilai' }}
                    </span>
                  </div>
                </div>

                <!-- Custom Visual Gradient Bar -->
                <div class="custom-bar-track position-relative">
                  <!-- Threshold KKM 70% marker line -->
                  <div class="kkm-marker-line" style="left: 70%;" title="Batas KKM 70%" />

                  <div
                    class="custom-bar-fill"
                    :style="{
                      width: `${Math.min(100, Math.max(0, (activeChartMetric === 'avg_score' ? cp.avg_score : cp.pass_rate) || 0))}%`,
                      background: ((activeChartMetric === 'avg_score' ? cp.avg_score : cp.pass_rate) || 0) >= 70
                        ? 'linear-gradient(90deg, #10b981 0%, #059669 100%)'
                        : 'linear-gradient(90deg, #ef4444 0%, #dc2626 100%)'
                    }"
                  />
                </div>

                <div class="d-flex align-center justify-space-between mt-1 pt-1 text-caption text-medium-emphasis border-t">
                  <span>{{ cp.quiz_count }} Kuis • {{ cp.assignment_count }} Tugas</span>
                  <Link
                    :href="route('instructor.assessment-analytics.course', cp.id)"
                    class="text-primary text-decoration-none font-weight-medium d-inline-flex align-center"
                  >
                    Detail Analitik
                    <VIcon icon="mdi-chevron-right" size="14" />
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-center pa-8 text-medium-emphasis">
            <VIcon icon="mdi-chart-line" size="36" class="mb-2 opacity-50" />
            <p class="text-body-2 mb-0">Belum ada data performa penilaian yang tercatat.</p>
          </div>
        </VCard>
      </VCol>

      <!-- Right Column: Score Distribution & Insights -->
      <VCol cols="12" lg="4">
        <VCard flat border class="pa-4 h-100 d-flex flex-column justify-space-between">
          <div>
            <div class="d-flex align-center justify-space-between mb-3">
              <h6 class="text-subtitle-1 font-weight-bold mb-0">
                Sebaran Grade Siswa
              </h6>
              <VChip size="x-small" color="primary" variant="tonal">
                {{ totalDistributedStudents }} Total Nilai
              </VChip>
            </div>
            <p class="text-caption text-medium-emphasis mb-4">
              Klasifikasi mutu penilaian berdasarkan skor kumulatif yang diraih siswa.
            </p>

            <!-- Distribution Items -->
            <div class="d-flex flex-column ga-3">
              <!-- Grade A (85 - 100) -->
              <div class="pa-2 rounded border bg-surface">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center ga-2">
                    <VAvatar size="20" color="success" class="text-caption font-weight-bold text-white">
                      A
                    </VAvatar>
                    <span class="text-body-2 font-weight-medium">Sangat Baik (85-100)</span>
                  </div>
                  <span class="text-caption font-weight-bold text-success">
                    {{ scoreDistribution['85-100'] || 0 }} siswa ({{ getBucketPct(scoreDistribution['85-100']) }}%)
                  </span>
                </div>
                <VProgressLinear
                  :model-value="getBucketPct(scoreDistribution['85-100'])"
                  color="success"
                  height="6"
                  rounded
                />
              </div>

              <!-- Grade B (70 - 84) -->
              <div class="pa-2 rounded border bg-surface">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center ga-2">
                    <VAvatar size="20" color="primary" class="text-caption font-weight-bold text-white">
                      B
                    </VAvatar>
                    <span class="text-body-2 font-weight-medium">Baik / Lulus (70-84)</span>
                  </div>
                  <span class="text-caption font-weight-bold text-primary">
                    {{ scoreDistribution['70-84'] || 0 }} siswa ({{ getBucketPct(scoreDistribution['70-84']) }}%)
                  </span>
                </div>
                <VProgressLinear
                  :model-value="getBucketPct(scoreDistribution['70-84'])"
                  color="primary"
                  height="6"
                  rounded
                />
              </div>

              <!-- Grade C (55 - 69) -->
              <div class="pa-2 rounded border bg-surface">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center ga-2">
                    <VAvatar size="20" color="warning" class="text-caption font-weight-bold text-white">
                      C
                    </VAvatar>
                    <span class="text-body-2 font-weight-medium">Cukup (55-69)</span>
                  </div>
                  <span class="text-caption font-weight-bold text-warning">
                    {{ scoreDistribution['55-69'] || 0 }} siswa ({{ getBucketPct(scoreDistribution['55-69']) }}%)
                  </span>
                </div>
                <VProgressLinear
                  :model-value="getBucketPct(scoreDistribution['55-69'])"
                  color="warning"
                  height="6"
                  rounded
                />
              </div>

              <!-- Grade D/E (< 55) -->
              <div class="pa-2 rounded border bg-surface">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center ga-2">
                    <VAvatar size="20" color="error" class="text-caption font-weight-bold text-white">
                      D
                    </VAvatar>
                    <span class="text-body-2 font-weight-medium">Perlu Bimbingan (&lt;55)</span>
                  </div>
                  <span class="text-caption font-weight-bold text-error">
                    {{ scoreDistribution['0-54'] || 0 }} siswa ({{ getBucketPct(scoreDistribution['0-54']) }}%)
                  </span>
                </div>
                <VProgressLinear
                  :model-value="getBucketPct(scoreDistribution['0-54'])"
                  color="error"
                  height="6"
                  rounded
                />
              </div>
            </div>
          </div>

          <!-- Smart Insight Callout -->
          <div class="insight-box mt-4 pa-3 rounded border bg-light-primary">
            <div class="d-flex align-start ga-2">
              <VIcon icon="mdi-lightbulb-on-outline" color="primary" size="20" class="mt-0" />
              <div>
                <span class="text-caption font-weight-bold text-primary d-block">
                  Rekomendasi Instruktur
                </span>
                <p class="text-caption text-medium-emphasis mb-0">
                  <template v-if="topCourse">
                    Kursus unggulan saat ini adalah <strong>{{ topCourse.title }}</strong> dengan nilai rata-rata <strong>{{ topCourse.avg_score }}%</strong>.
                  </template>
                  <template v-if="attentionCoursesCount > 0">
                    Terdapat <strong>{{ attentionCoursesCount }} kursus</strong> dengan nilai di bawah KKM 70% yang disarankan untuk remedial.
                  </template>
                  <template v-else-if="!topCourse">
                    Data penilaian akan tampil setelah kuis atau tugas dinilai oleh instruktur.
                  </template>
                </p>
              </div>
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Comprehensive Course Table Section -->
    <VCard flat border class="pa-4">
      <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
        <div>
          <h6 class="text-subtitle-1 font-weight-bold mb-0">
            Daftar Lengkap Kursus & Performa Penilaian
          </h6>
          <span class="text-caption text-medium-emphasis">
            Rincian menyeluruh seluruh kursus beserta metrik rata-rata nilai dan tingkat kelulusan.
          </span>
        </div>

        <div class="d-flex align-center ga-2 flex-wrap">
          <VTextField
            v-model="searchQuery"
            placeholder="Cari kursus..."
            density="compact"
            variant="outlined"
            prepend-inner-icon="mdi-magnify"
            clearable
            style="min-width: 200px;"
            hide-details
          />

          <VSelect
            v-model="statusFilter"
            :items="[
              { title: 'Semua Status', value: 'all' },
              { title: 'Lulus KKM (≥ 70%)', value: 'passed' },
              { title: 'Butuh Perhatian (< 70%)', value: 'attention' }
            ]"
            item-title="title"
            item-value="value"
            density="compact"
            variant="outlined"
            style="min-width: 170px;"
            hide-details
          />
        </div>
      </div>

      <VTable class="border rounded" density="compact">
        <thead>
          <tr>
            <th class="text-start">Judul Kursus</th>
            <th class="text-center">Siswa Terdaftar</th>
            <th class="text-center">Kuis & Tugas</th>
            <th class="text-center">Rata-rata Nilai</th>
            <th class="text-center">Tingkat Kelulusan</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cp in filteredCourses" :key="cp.id">
            <!-- Title -->
            <td class="font-weight-medium" style="max-width: 280px;">
              <div class="d-flex align-center ga-2 text-truncate">
                <span class="text-truncate">{{ cp.title }}</span>
                <VChip
                  v-if="cp.published"
                  size="x-small"
                  color="success"
                  variant="tonal"
                >
                  Diterbitkan
                </VChip>
                <VChip
                  v-else
                  size="x-small"
                  color="secondary"
                  variant="tonal"
                >
                  Draf
                </VChip>
              </div>
            </td>

            <!-- Enrollments -->
            <td class="text-center">
              <span class="font-weight-semibold">{{ cp.enrollments }}</span>
              <span class="text-caption text-medium-emphasis ms-1">siswa</span>
            </td>

            <!-- Assessments -->
            <td class="text-center">
              <span class="text-caption">
                {{ cp.quiz_count }} Kuis • {{ cp.assignment_count }} Tugas
              </span>
            </td>

            <!-- Avg Score -->
            <td class="text-center">
              <VChip
                v-if="cp.avg_score !== null"
                size="small"
                :color="getScoreColor(cp.avg_score)"
                variant="tonal"
                class="font-weight-bold"
              >
                {{ cp.avg_score }}%
              </VChip>
              <span v-else class="text-caption text-medium-emphasis">—</span>
            </td>

            <!-- Pass Rate -->
            <td class="text-center">
              <div v-if="cp.pass_rate !== null" class="d-inline-flex align-center ga-1">
                <VIcon
                  :icon="cp.pass_rate >= 70 ? 'mdi-check-circle' : 'mdi-alert-circle'"
                  :color="cp.pass_rate >= 70 ? 'success' : 'error'"
                  size="16"
                />
                <span class="text-body-2 font-weight-medium">
                  {{ cp.pass_rate }}%
                </span>
              </div>
              <span v-else class="text-caption text-medium-emphasis">—</span>
            </td>

            <!-- Action -->
            <td class="text-end">
              <Link :href="route('instructor.assessment-analytics.course', cp.id)" class="text-decoration-none">
                <VBtn
                  variant="tonal"
                  color="primary"
                  size="small"
                  density="compact"
                  prepend-icon="mdi-chart-line"
                >
                  Detail
                </VBtn>
              </Link>
            </td>
          </tr>

          <tr v-if="filteredCourses.length === 0">
            <td colspan="6" class="text-center pa-6 text-medium-emphasis">
              Tidak ada data kursus yang sesuai kriteria pencarian.
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>

<style scoped>
.kpi-card {
  transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

.custom-bar-track {
  width: 100%;
  height: 10px;
  background-color: rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 6px;
  overflow: hidden;
}

.custom-bar-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.kkm-marker-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  background-color: rgba(var(--v-theme-primary), 0.85);
  z-index: 2;
  box-shadow: 0 0 4px rgba(var(--v-theme-primary), 0.5);
}

.course-bar-item {
  transition: background-color 0.15s ease-in-out;
}
.course-bar-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.03);
}

.border-t {
  border-top: 1px dashed rgba(var(--v-border-color), 0.12);
}
</style>
