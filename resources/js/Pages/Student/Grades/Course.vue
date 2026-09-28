<script setup>
defineProps({
  course: { type: Object, required: true },
  gradebookItems: { type: Array, default: () => [] },
  grades: { type: Object, default: () => ({}) },
  finalPercentage: { type: Number, default: null },
  classAverages: { type: Object, default: () => ({}) },
  studentRank: { type: Number, default: null },
  totalRanked: { type: Number, default: 0 },
})

const letterGrade = (pct) => {
  if (pct >= 90) return 'A'
  if (pct >= 80) return 'B'
  if (pct >= 70) return 'C'
  if (pct >= 60) return 'D'
  return 'E'
}

const typeColor = (type) => ({
  quiz: 'info',
  assignment: 'purple',
  exam: 'error',
}[type] || 'secondary')
</script>

<template>
  <div>
    <VCard flat border class="pa-6 mb-6">
      <div class="d-flex justify-space-between align-center flex-wrap ga-4">
        <div>
          <h4 class="text-h4 font-weight-bold">{{ course.title }}</h4>
          <p class="text-body-1 text-medium-emphasis mt-1">Grade Breakdown</p>
        </div>
        <div class="d-flex align-center ga-6">
          <div v-if="studentRank !== null" class="text-center">
            <div class="text-caption text-medium-emphasis">Class Rank</div>
            <div class="text-h5 font-weight-bold text-primary">
              #{{ studentRank }} <span class="text-body-2 text-disabled">/ {{ totalRanked }}</span>
            </div>
          </div>
          <div v-if="finalPercentage !== null" class="text-center">
            <div class="text-h3 font-weight-bold" :class="finalPercentage >= 70 ? 'text-success' : 'text-error'">
              {{ letterGrade(finalPercentage) }}
            </div>
            <div class="text-body-2 text-medium-emphasis">{{ finalPercentage }}%</div>
          </div>
        </div>
      </div>
    </VCard>

    <VCard flat border>
      <VTable class="text-no-wrap">
        <thead>
          <tr>
            <th class="text-left font-weight-bold text-caption text-uppercase">Item</th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Type</th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Score</th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Percentage</th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Class Avg</th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Grade</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in gradebookItems" :key="item.id" class="hover-bg">
            <td class="font-weight-medium">{{ item.title }}</td>
            <td class="text-center">
              <VChip :color="typeColor(item.type)" size="x-small" variant="tonal" label>{{ item.type }}</VChip>
            </td>
            <td class="text-center">{{ grades[item.id]?.score ?? '-' }} / {{ item.max_score }}</td>
            <td class="text-center">
              <span
                v-if="grades[item.id]?.percentage != null"
                class="font-weight-semibold"
                :class="grades[item.id].percentage >= 70 ? 'text-success' : 'text-error'"
              >
                {{ grades[item.id].percentage }}%
              </span>
              <span v-else class="text-disabled">-</span>
            </td>
            <td class="text-center text-medium-emphasis">
              {{ classAverages[item.id] != null ? classAverages[item.id] + '%' : '-' }}
            </td>
            <td class="text-center">
              <span
                v-if="grades[item.id]?.letter_grade"
                class="text-h6 font-weight-bold"
                :class="(grades[item.id]?.percentage ?? 0) >= 70 ? 'text-success' : 'text-error'"
              >
                {{ grades[item.id].letter_grade }}
              </span>
              <span v-else class="text-disabled">-</span>
            </td>
          </tr>
          <tr v-if="!gradebookItems.length">
            <td colspan="6" class="text-center py-8 text-medium-emphasis">No grade items found for this course.</td>
          </tr>
        </tbody>
        <tfoot v-if="finalPercentage !== null">
          <tr class="bg-light-info">
            <td colspan="5" class="font-weight-semibold">Final Grade</td>
            <td class="text-center text-h6 font-weight-bold" :class="finalPercentage >= 70 ? 'text-success' : 'text-error'">
              {{ letterGrade(finalPercentage) }} ({{ finalPercentage }}%)
            </td>
          </tr>
        </tfoot>
      </VTable>
    </VCard>

    <div class="mt-6 d-flex ga-3">
      <VBtn
        color="primary"
        variant="tonal"
        prepend-icon="mdi-download"
        :href="route('student.grades.transcript', course.id)"
      >
        Download Transcript (PDF)
      </VBtn>
      <VBtn
        color="secondary"
        variant="tonal"
        prepend-icon="mdi-arrow-left"
        :href="route('student.grades.index')"
      >
        Back to Grades Overview
      </VBtn>
    </div>
  </div>
</template>

<style scoped>
.hover-bg:hover {
  background: rgb(var(--v-theme-surface-variant));
}
</style>
