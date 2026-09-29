<script setup>
defineProps({
  analytics: { type: Object, required: true },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ analytics.path?.title }}</h4>
        <p class="text-body-2 text-medium-emphasis">Path Analytics Details</p>
      </div>
      <VBtn variant="outlined" :href="route('admin.learning-paths.show', analytics.path?.id)">Back</VBtn>
    </div>

    <VRow class="mb-6">
      <VCol cols="12" sm="4">
        <VCard flat border>
          <VCardText class="text-center">
            <div class="text-h5 font-weight-bold">{{ analytics.overview?.total_enrollments || 0 }}</div>
            <div class="text-caption text-medium-emphasis">Enrollments</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="4">
        <VCard flat border>
          <VCardText class="text-center">
            <div class="text-h5 font-weight-bold">{{ analytics.overview?.completed || 0 }}</div>
            <div class="text-caption text-medium-emphasis">Completed</div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="4">
        <VCard flat border>
          <VCardText class="text-center">
            <div class="text-h5 font-weight-bold">{{ analytics.overview?.completion_rate || 0 }}%</div>
            <div class="text-caption text-medium-emphasis">Completion Rate</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12" md="6">
        <VCard flat border title="Course Metrics">
          <VCardText v-if="!analytics.courseMetrics?.length" class="text-center pa-6 text-medium-emphasis">
            No course data
          </VCardText>
          <VList v-else lines="two">
            <VListItem v-for="(cm, i) in analytics.courseMetrics" :key="cm.course_id">
              <template #prepend>
                <VAvatar color="primary" variant="tonal" size="32">{{ i + 1 }}</VAvatar>
              </template>
              <VListItemTitle>{{ cm.title }}</VListItemTitle>
              <VListItemSubtitle>
                {{ cm.total_students_reached }} students, {{ cm.completion_rate }}% completed
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCard>
      </VCol>

      <VCol cols="12" md="6">
        <VCard flat border title="Student Progress">
          <VCardText v-if="!analytics.studentProgress?.data?.length" class="text-center pa-6 text-medium-emphasis">
            No students enrolled
          </VCardText>
          <VList v-else lines="two">
            <VListItem v-for="enrollment in analytics.studentProgress.data" :key="enrollment.id">
              <template #prepend>
                <VAvatar color="primary" size="32">
                  {{ enrollment.user?.name?.charAt(0) }}
                </VAvatar>
              </template>
              <VListItemTitle>{{ enrollment.user?.name }}</VListItemTitle>
              <VListItemSubtitle>{{ enrollment.user?.email }}</VListItemSubtitle>
            </VListItem>
          </VList>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
