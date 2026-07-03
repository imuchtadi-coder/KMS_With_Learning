<script setup>
defineProps({
  course: { type: Object, required: true },
  enrollments: { type: Array, default: () => [] },
  totalLessons: { type: Number, default: 0 },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ course.title }}</h4>
        <p class="text-body-1 text-medium-emphasis">Student enrollment & progress tracking</p>
      </div>
      <VBtn variant="tonal" color="secondary" size="small" :href="route('instructor.courses.show', course.id)">Back to Detail</VBtn>
    </div>

    <VRow class="mb-6">
      <VCol cols="6" lg="3">
        <VCard flat border class="pa-4">
          <div class="text-caption text-medium-emphasis">Total Enrolled</div>
          <div class="text-h3 font-weight-bold">{{ enrollments.length }}</div>
        </VCard>
      </VCol>
      <VCol cols="6" lg="3">
        <VCard flat border class="pa-4">
          <div class="text-caption text-medium-emphasis">Total Lessons</div>
          <div class="text-h3 font-weight-bold">{{ totalLessons }}</div>
        </VCard>
      </VCol>
      <VCol cols="6" lg="3">
        <VCard flat border class="pa-4">
          <div class="text-caption text-medium-emphasis">Avg Progress</div>
          <div class="text-h3 font-weight-bold">{{ enrollments.length > 0 ? Math.round(enrollments.reduce((a, e) => a + (e.progress_percentage || 0), 0) / enrollments.length) : 0 }}%</div>
        </VCard>
      </VCol>
      <VCol cols="6" lg="3">
        <VCard flat border class="pa-4">
          <div class="text-caption text-medium-emphasis">Completed</div>
          <div class="text-h3 font-weight-bold">{{ enrollments.filter(e => e.progress_percentage === 100).length }}</div>
        </VCard>
      </VCol>
    </VRow>

    <VCard v-if="enrollments.length" flat border>
      <VDataTable
        :items="enrollments"
        :headers="[
          { title: 'Student', key: 'user.name' },
          { title: 'Email', key: 'user.email' },
          { title: 'Enrolled', key: 'created_at' },
          { title: 'Progress', key: 'progress_percentage' },
          { title: 'Completed', key: 'completed_lessons' },
        ]"
        disable-sort
        hide-default-footer
        class="text-no-wrap"
      >
        <template #item.user.name="{ item }">
          <div class="d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" size="32">{{ item.user?.name?.[0] }}</VAvatar>
            <span class="font-weight-medium">{{ item.user?.name }}</span>
          </div>
        </template>
        <template #item.created_at="{ value }">{{ value ? new Date(value).toLocaleDateString() : '-' }}</template>
        <template #item.progress_percentage="{ value }">
          <div class="d-flex align-center ga-3" style="min-width: 140px;">
            <VProgressLinear :model-value="value" color="primary" height="8" rounded class="flex-grow-1" />
            <span class="text-body-2 font-weight-semibold" :class="value === 100 ? 'text-success' : ''">{{ value || 0 }}%</span>
          </div>
        </template>
        <template #item.completed_lessons="{ item }">
          <span class="font-weight-semibold">{{ item.completed_lessons || 0 }}</span>
          <span class="text-medium-emphasis">/ {{ item.total_lessons || totalLessons }}</span>
        </template>
      </VDataTable>
    </VCard>
    <VCard v-else flat border class="pa-16 text-center">
      <VIcon icon="mdi-account-group" size="64" color="disabled" />
      <h5 class="text-h5 mt-4 mb-2">No students enrolled yet</h5>
      <p class="text-body-2 text-medium-emphasis">Students will appear here once they enroll in your course.</p>
    </VCard>
  </div>
</template>
