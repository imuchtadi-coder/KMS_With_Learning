<script setup>
const props = defineProps({
  grades: { type: Array, default: () => [] },
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-6">My Grades</h4>

    <div v-if="grades.length" class="grid-list">
      <VRow>
        <VCol v-for="enrollment in grades" :key="enrollment.id" cols="12" sm="6" lg="4" class="d-flex">
          <VCard flat border hover class="w-100 d-flex flex-column">
            <div class="bg-gradient-to-r from-primary to-primary-darken-1 pa-4 d-flex align-center" style="min-height: 72px;">
              <h5 class="text-h5 font-weight-bold text-black">{{ enrollment.course?.title }}</h5>
            </div>
            <VCardText class="text-center pb-4 flex-grow-1 d-flex flex-column">
              <div v-if="enrollment.final_percentage !== null">
                <div class="text-h3 font-weight-bold" :class="enrollment.final_percentage >= 70 ? 'text-success' : 'text-error'">
                  {{ enrollment.final_grade }}
                </div>
                <div class="text-body-2 text-medium-emphasis">{{ enrollment.final_percentage }}%</div>
                <VProgressLinear
                  :model-value="enrollment.final_percentage"
                  :color="enrollment.final_percentage >= 70 ? 'success' : 'error'"
                  height="8"
                  rounded
                  class="mt-3 mb-4"
                />
              </div>
              <div v-else class="mb-4">
                <div class="text-h3 text-disabled font-weight-bold">-</div>
                <div class="text-body-2 text-medium-emphasis">No grades yet</div>
              </div>
              <VBtn
                variant="tonal"
                color="secondary"
                block
                class="mt-auto"
                :href="route('student.grades.course', enrollment.course?.id)"
              >
                View Details
              </VBtn>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <VCard v-else flat border class="pa-16 text-center">
      <VIcon icon="mdi-chart-bar" size="48" color="disabled" class="mb-4" />
      <p class="text-h6 text-medium-emphasis mb-2">No grades available</p>
      <p class="text-body-2 text-disabled mb-6">Enroll in courses to start tracking your grades.</p>
      <VBtn color="primary" variant="elevated" :href="route('student.courses.list')">Browse Courses</VBtn>
    </VCard>
  </div>
</template>
