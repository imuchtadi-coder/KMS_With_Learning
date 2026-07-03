<script setup>
defineProps({
  analytics: { type: Object, required: true },
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Learning Path Analytics</h4>
      <p class="text-body-2 text-medium-emphasis">Overview of all learning paths</p>
    </div>

    <VRow class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" rounded>
              <VIcon icon="mdi-road-variant" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ analytics.overview?.total_paths || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Total Paths</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="info" variant="tonal" rounded>
              <VIcon icon="mdi-account-group" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ analytics.overview?.total_enrollments || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Total Enrollments</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="success" variant="tonal" rounded>
              <VIcon icon="mdi-check-circle" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ analytics.overview?.completed_enrollments || 0 }}</div>
              <div class="text-caption text-medium-emphasis">Completed</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="warning" variant="tonal" rounded>
              <VIcon icon="mdi-chart-line" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ analytics.overview?.average_completion_rate || 0 }}%</div>
              <div class="text-caption text-medium-emphasis">Avg Completion</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12" md="6">
        <VCard flat border title="Top Performing Paths">
          <VCardText v-if="!analytics.topPaths?.length" class="text-center pa-6 text-medium-emphasis">
            No data yet
          </VCardText>
          <VList v-else lines="two">
            <VListItem v-for="(path, i) in analytics.topPaths" :key="path.id">
              <template #prepend>
                <VAvatar color="primary" variant="tonal" size="32">
                  {{ i + 1 }}
                </VAvatar>
              </template>
              <VListItemTitle class="font-weight-medium">{{ path.title }}</VListItemTitle>
              <VListItemSubtitle>
                {{ path.enrollments }} enrollments, {{ path.completion_rate }}% completion
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCard>
      </VCol>

      <VCol cols="12" md="6">
        <VCard flat border title="Course Performance">
          <VCardText v-if="!analytics.coursePerformance?.length" class="text-center pa-6 text-medium-emphasis">
            No data yet
          </VCardText>
          <VList v-else lines="two">
            <VListItem v-for="course in analytics.coursePerformance" :key="course.course_id">
              <VListItemTitle class="font-weight-medium">{{ course.title }}</VListItemTitle>
              <VListItemSubtitle>
                {{ course.times_in_paths }} paths, {{ course.completion_rate }}% completion
              </VListItemSubtitle>
            </VListItem>
          </VList>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
