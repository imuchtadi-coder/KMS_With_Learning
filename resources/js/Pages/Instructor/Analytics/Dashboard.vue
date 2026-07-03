<script setup>
import { ref } from 'vue'

defineProps({
  totalCourses: { type: Number, default: 0 },
  totalEnrollments: { type: Number, default: 0 },
  totalQuizzes: { type: Number, default: 0 },
  totalAssignments: { type: Number, default: 0 },
  overallAvgScore: { type: Number, default: 0 },
  overallPassRate: { type: Number, default: 0 },
  coursePerformance: { type: Array, default: () => [] },
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-6">Assessment Analytics</h4>

    <VRow class="mb-6">
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Avg Score</div><div class="text-h3 font-weight-bold">{{ overallAvgScore }}%</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Pass Rate</div><div class="text-h3 font-weight-bold">{{ overallPassRate }}%</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Total Quizzes</div><div class="text-h3 font-weight-bold">{{ totalQuizzes }}</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Total Assignments</div><div class="text-h3 font-weight-bold">{{ totalAssignments }}</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Courses</div><div class="text-h3 font-weight-bold">{{ totalCourses }}</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Enrollments</div><div class="text-h3 font-weight-bold">{{ totalEnrollments }}</div></VCard></VCol>
    </VRow>

    <VCard v-if="coursePerformance.length" flat border class="pa-6">
      <h5 class="text-h5 font-weight-bold mb-4">Course Performance</h5>
      <div v-for="cp in coursePerformance" :key="cp.id" class="mb-4">
        <div class="d-flex justify-space-between mb-1">
          <span class="font-weight-medium">{{ cp.title }}</span>
          <span class="font-weight-bold">{{ cp.avg_score }}%</span>
        </div>
        <VProgressLinear :model-value="cp.avg_score" :color="cp.avg_score >= 70 ? 'success' : 'error'" height="8" rounded />
      </div>
    </VCard>
    <VCard v-else flat border class="pa-12 text-center"><p class="text-medium-emphasis">No data available yet.</p></VCard>
  </div>
</template>
