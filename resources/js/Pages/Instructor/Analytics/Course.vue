<script setup>
import { ref } from 'vue'

defineProps({
  course: { type: Object, required: true },
  totalQuizzes: { type: Number, default: 0 },
  totalAssignments: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  overallAvgScore: { type: Number, default: 0 },
  overallPassingRate: { type: Number, default: 0 },
  scoreDistribution: { type: Array, default: () => [] },
  assessmentPerformance: { type: Array, default: () => [] },
  studentRankings: { type: Array, default: () => [] },
  completionRate: { type: Number, default: 0 },
})
</script>

<template>
  <div>
    <div div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Course Analytics</h4>
        <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>
      </div>
      <div>
        <VBtn
          color="secondary"
          variant="elevated"
          prepend-icon="mdi-arrow-left"
          :href="route('instructor.courses.update', course.id)"
        >
          Back to Course
        </VBtn>
      </div>
    </div>

    <VRow class="mb-6">
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Avg Score</div><div class="text-h3 font-weight-bold">{{ overallAvgScore }}%</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Pass Rate</div><div class="text-h3 font-weight-bold">{{ overallPassingRate }}%</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Completion</div><div class="text-h3 font-weight-bold">{{ completionRate }}%</div></VCard></VCol>
      <VCol cols="6" lg="3"><VCard flat border class="pa-4"><div class="text-caption text-medium-emphasis">Students</div><div class="text-h3 font-weight-bold">{{ totalStudents }}</div></VCard></VCol>
    </VRow>

    <VRow>
      <VCol cols="12" lg="6">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-4">Score Distribution</h5>
          <div v-if="scoreDistribution.length">
            <div v-for="(count, range) in scoreDistribution" :key="range" class="d-flex align-center ga-3 mb-2">
              <span class="text-body-2" style="min-width: 60px;">{{ range }}</span>
              <VProgressLinear :model-value="(count / Math.max(...Object.values(scoreDistribution))) * 100" height="20" rounded color="primary" class="flex-grow-1">
                <template #default><span class="text-caption text-white font-weight-bold px-2">{{ count }}</span></template>
              </VProgressLinear>
            </div>
          </div>
          <p v-else class="text-medium-emphasis text-body-2">No data.</p>
        </VCard>
      </VCol>
      <VCol cols="12" lg="6">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-4">Assessment Performance</h5>
          <div v-if="assessmentPerformance.length">
            <div v-for="ap in assessmentPerformance" :key="ap.title" class="mb-3">
              <div class="d-flex justify-space-between text-body-2 mb-1">
                <span class="font-weight-medium">{{ ap.title }}</span>
                <span class="font-weight-bold">{{ ap.avg_score }}%</span>
              </div>
              <VProgressLinear :model-value="ap.avg_score" :color="ap.avg_score >= 70 ? 'success' : 'error'" height="6" rounded />
            </div>
          </div>
          <p v-else class="text-medium-emphasis text-body-2">No data.</p>
        </VCard>
      </VCol>
    </VRow>

    <VCard flat border class="pa-6">
      <h5 class="text-h5 font-weight-bold mb-4">Student Rankings</h5>
      <VDataTable v-if="studentRankings.length" :items="studentRankings" :headers="[
        { title: '#', key: 'rank' },
        { title: 'Student', key: 'name' },
        { title: 'Score', key: 'score' },
        { title: 'Grade', key: 'grade' },
      ]" disable-sort hide-default-footer class="text-no-wrap">
        <template #item.rank="{ item }">
          <VChip :color="item.rank <= 3 ? 'success' : 'secondary'" size="small" variant="tonal" label>#{{ item.rank }}</VChip>
        </template>
        <template #item.score="{ value }">{{ value }}%</template>
      </VDataTable>
      <p v-else class="text-medium-emphasis text-body-2 text-center">No data.</p>
    </VCard>
  </div>
</template>
