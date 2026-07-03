<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  learningPaths: { type: Object, required: true },
  recommendations: { type: Array, default: () => [] },
})

function enroll(pathId) {
  router.post(route('student.learning-paths.enroll', pathId), {}, {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h4 class="text-h4 font-weight-bold">Learning Paths</h4>
      <p class="text-body-1 text-medium-emphasis">Structured curriculum to master new skills</p>
    </div>

    <VTabs
      color="primary"
      class="mb-6"
    >
      <VTab
        :href="route('student.learning-paths.index')"
        :active="true"
      >
        All Paths
      </VTab>
      <VTab :href="route('student.learning-paths.my-paths')">
        My Learning
      </VTab>
    </VTabs>

    <VRow>
      <VCol
        v-for="path in learningPaths.data"
        :key="path.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard flat border>
          <div class="pa-6 bg-primary text-white rounded-t">
            <h5 class="text-h5 text-white font-weight-bold">{{ path.title }}</h5>
            <p class="text-body-2 text-white opacity-75 mt-1">{{ path.courses?.length || 0 }} courses</p>
          </div>
          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-4">{{ path.description?.substring(0, 100) }}{{ path.description?.length > 100 ? '...' : '' }}</p>

            <div class="mb-4 ga-2 d-flex flex-column">
              <div
                v-for="course in (path.courses || []).slice(0, 3)"
                :key="course.id"
                class="d-flex align-center ga-2 text-body-2"
              >
                <VIcon icon="mdi-check-circle" color="success" size="18" />
                <span class="text-truncate">{{ course.title }}</span>
              </div>
              <div v-if="(path.courses || []).length > 3" class="text-caption text-medium-emphasis">
                + {{ path.courses.length - 3 }} more courses
              </div>
            </div>

            <div v-if="path.isEnrolled" class="mb-4">
              <div class="d-flex justify-space-between mb-1">
                <span class="text-caption font-weight-medium">Progress</span>
                <span class="text-caption font-weight-bold text-primary">{{ path.progress }}%</span>
              </div>
              <VProgressLinear
                :model-value="path.progress"
                color="primary"
                height="6"
                rounded
              />
            </div>

            <VBtn
              block
              :color="path.isEnrolled ? 'primary' : 'success'"
              variant="elevated"
              :href="path.isEnrolled ? route('student.learning-paths.show', path.id) : undefined"
              @click="!path.isEnrolled ? enroll(path.id) : undefined"
            >
              {{ path.isEnrolled ? 'Continue Learning' : 'Start Path' }}
            </VBtn>

            <VBtn
              block
              variant="text"
              color="primary"
              class="mt-2"
              :href="route('student.learning-paths.show', path.id)"
            >
              View Details
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        v-if="learningPaths.data?.length === 0"
        cols="12"
        class="text-center pa-8"
      >
        <VIcon icon="mdi-map-marker-path" size="64" color="disabled" />
        <h5 class="text-h5 mt-4">No Learning Paths Available</h5>
        <p class="text-body-2 text-medium-emphasis">Check back soon for new learning paths!</p>
      </VCol>
    </VRow>
  </div>
</template>
