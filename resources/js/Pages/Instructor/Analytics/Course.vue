<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  totalQuizzes: { type: Number, default: 0 },
  totalAssignments: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  overallAvgScore: { type: Number, default: 0 },
  overallPassingRate: { type: Number, default: 0 },
  scoreDistribution: { type: Object, default: () => ({}) },
  assessmentPerformance: { type: Array, default: () => [] },
  studentRankings: { type: Array, default: () => [] },
  completionRate: { type: Number, default: 0 },
})

const maxDistributionCount = computed(() => {
  return Math.max(
    0,
    ...Object.values(props.scoreDistribution).map(Number)
  )
})

const getScoreColor = (val) => {
  if (val === null || val === undefined) return 'secondary'
  if (val >= 85) return 'success'
  if (val >= 70) return 'primary'
  if (val >= 55) return 'warning'
  return 'error'
}
</script>

<template>
  <div class="course-analytics-page">
    <!-- Header Section -->
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div>
        <div class="d-flex align-center ga-2 mb-1">
          <Link :href="route('instructor.assessment-analytics.dashboard')" class="text-decoration-none">
            <VBtn variant="tonal" size="small" prepend-icon="mdi-arrow-left">
              Semua Kursus
            </VBtn>
          </Link>
          <h4 class="text-h4 font-weight-bold mb-0">Analitik Penilaian: {{ course.title }}</h4>
        </div>
        <p class="text-body-2 text-medium-emphasis mb-0 ms-1">
          Rincian capaian kuis, tugas, distribusi nilai, dan peringkat prestasi siswa pada kursus ini.
        </p>
      </div>

      <div class="d-flex align-center ga-2">
        <VBtn
          color="secondary"
          variant="outlined"
          size="small"
          prepend-icon="mdi-book-open-outline"
          :href="route('instructor.courses.update', course.id)"
        >
          Kelola Materi
        </VBtn>
      </div>
    </div>

    <!-- 4 KPI Metrics -->
    <VRow class="mb-5">
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Rata-rata Nilai Kursus</span>
            <VAvatar color="primary" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-chart-line" size="18" />
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
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Tingkat Kelulusan</span>
            <VAvatar color="success" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-school-outline" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold text-success">
              {{ overallPassingRate !== null ? overallPassingRate : 0 }}%
            </span>
            <span class="text-caption text-medium-emphasis">lulus batas KKM</span>
          </div>
          <VProgressLinear
            :model-value="overallPassingRate || 0"
            color="success"
            height="6"
            rounded
          />
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Tingkat Penyelesaian</span>
            <VAvatar color="info" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-check-all" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold text-info">
              {{ completionRate !== null ? completionRate : 0 }}%
            </span>
            <span class="text-caption text-medium-emphasis">selesai ujian</span>
          </div>
          <VProgressLinear
            :model-value="completionRate || 0"
            color="info"
            height="6"
            rounded
          />
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" lg="3">
        <VCard flat border class="pa-4 h-100 kpi-card">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium text-medium-emphasis">Siswa Terdaftar</span>
            <VAvatar color="warning" variant="tonal" size="32" rounded>
              <VIcon icon="mdi-account-group-outline" size="18" />
            </VAvatar>
          </div>
          <div class="d-flex align-baseline ga-2 mb-2">
            <span class="text-h4 font-weight-bold">
              {{ totalStudents }}
            </span>
            <span class="text-caption text-medium-emphasis">Siswa</span>
          </div>
          <span class="text-caption text-medium-emphasis mt-1 d-block">
            {{ totalQuizzes }} Kuis • {{ totalAssignments }} Tugas aktif
          </span>
        </VCard>
      </VCol>
    </VRow>

    <!-- Score Distribution and Assessment Performance -->
    <VRow class="mb-5">
      <!-- Score Distribution Bar Chart -->
      <VCol cols="12" lg="6">
        <VCard flat border class="pa-4 h-100">
          <div class="d-flex align-center justify-space-between mb-3">
            <h6 class="text-subtitle-1 font-weight-bold mb-0">
              Distribusi Rentang Nilai Siswa
            </h6>
            <VChip size="x-small" color="primary" variant="tonal">
              Sebaran Nilai
            </VChip>
          </div>

          <div v-if="Object.keys(scoreDistribution).length" class="d-flex flex-column ga-3 pt-2">
            <div
              v-for="(count, range) in scoreDistribution"
              :key="range"
              class="pa-2 px-3 rounded border bg-surface"
            >
              <div class="d-flex align-center justify-space-between mb-1">
                <span class="text-body-2 font-weight-medium">
                  Rentang Skor: {{ range }}
                </span>
                <span class="text-caption font-weight-bold text-primary">
                  {{ count }} siswa
                </span>
              </div>

              <VProgressLinear
                :model-value="maxDistributionCount > 0 ? (count / maxDistributionCount) * 100 : 0"
                height="8"
                rounded
                :color="range === '90-100' || range === '85-100' ? 'success' : range === '70-89' || range === '70-84' ? 'primary' : range === '50-69' || range === '55-69' ? 'warning' : 'error'"
              />
            </div>
          </div>

          <div v-else class="text-center pa-6 text-medium-emphasis">
            Belum ada data distribusi nilai.
          </div>
        </VCard>
      </VCol>

      <!-- Assessment Performance Breakdown -->
      <VCol cols="12" lg="6">
        <VCard flat border class="pa-4 h-100">
          <div class="d-flex align-center justify-space-between mb-3">
            <h6 class="text-subtitle-1 font-weight-bold mb-0">
              Performa per Instrumen Penilaian
            </h6>
            <VChip size="x-small" color="secondary" variant="tonal">
              {{ assessmentPerformance.length }} Instrumen
            </VChip>
          </div>

          <div v-if="assessmentPerformance.length" class="d-flex flex-column ga-3 pt-2">
            <div
              v-for="ap in assessmentPerformance"
              :key="ap.title"
              class="pa-2 px-3 rounded border"
            >
              <div class="d-flex align-center justify-space-between mb-1">
                <span class="text-body-2 font-weight-medium text-truncate me-2">
                  {{ ap.title }}
                </span>
                <span
                  class="text-caption font-weight-bold"
                  :class="ap.avg_score >= 70 ? 'text-success' : 'text-error'"
                >
                  Rata-rata: {{ ap.avg_score }}%
                </span>
              </div>

              <VProgressLinear
                :model-value="ap.avg_score || 0"
                :color="ap.avg_score >= 70 ? 'success' : 'error'"
                height="6"
                rounded
              />

              <div class="d-flex align-center justify-space-between mt-1 text-caption text-medium-emphasis">
                <span>{{ ap.submission_count }} / {{ ap.student_count }} Siswa Mengumpulkan</span>
                <span>Tingkat Lulus: {{ ap.passing_rate }}%</span>
              </div>
            </div>
          </div>

          <div v-else class="text-center pa-6 text-medium-emphasis">
            Belum ada tugas atau kuis yang dinilai.
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Student Rankings Table -->
    <VCard flat border class="pa-4">
      <div class="d-flex align-center justify-space-between mb-3">
        <div>
          <h6 class="text-subtitle-1 font-weight-bold mb-0">
            Peringkat Prestasi Siswa
          </h6>
          <span class="text-caption text-medium-emphasis">
            Urutan capaian hasil akhir siswa pada kursus ini.
          </span>
        </div>
      </div>

      <VTable v-if="studentRankings.length" class="border rounded" density="compact">
        <thead>
          <tr>
            <th class="text-center" style="width: 70px;">Peringkat</th>
            <th class="text-start">Nama Siswa</th>
            <th class="text-center">Skor Akhir</th>
            <th class="text-center">Grade Predikat</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(sr, idx) in studentRankings" :key="sr.name">
            <td class="text-center">
              <VChip
                :color="idx === 0 ? 'warning' : idx < 3 ? 'success' : 'secondary'"
                size="x-small"
                variant="tonal"
                class="font-weight-bold"
              >
                #{{ idx + 1 }}
              </VChip>
            </td>
            <td class="font-weight-medium">{{ sr.name }}</td>
            <td class="text-center">
              <span class="font-weight-bold">{{ sr.score }}%</span>
            </td>
            <td class="text-center">
              <VChip
                :color="sr.grade === 'A' ? 'success' : sr.grade === 'B' ? 'primary' : sr.grade === 'C' ? 'warning' : 'error'"
                size="x-small"
                variant="tonal"
                class="font-weight-bold"
              >
                Grade {{ sr.grade }}
              </VChip>
            </td>
          </tr>
        </tbody>
      </VTable>

      <div v-else class="text-center pa-8 text-medium-emphasis">
        Belum ada peringkat siswa yang dapat ditampilkan.
      </div>
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
</style>
