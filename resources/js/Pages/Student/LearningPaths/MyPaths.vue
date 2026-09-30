<script setup>
import { ref } from 'vue'

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => [],
  },
})

const activeTab = ref('my-learning')

const getProgress = enrollment => {
  const totalCourses =
    enrollment?.learning_path?.courses?.length || 0

  const completedCourses =
    Number(enrollment?.completed_count) || 0

  if (
    typeof enrollment?.progress === 'number' ||
    typeof enrollment?.progress === 'string'
  ) {
    const progress = Number(enrollment.progress)

    if (Number.isFinite(progress)) {
      return Math.min(
        100,
        Math.max(0, progress),
      )
    }
  }

  if (totalCourses > 0) {
    return Math.min(
      100,
      Math.round(
        (completedCourses / totalCourses) * 100,
      ),
    )
  }

  return 0
}
</script>

<template>
  <div>
    <div class="mb-8">
      <h4 class="text-h4 font-weight-bold">My Learning Paths</h4>
      <p class="text-body-1 text-medium-emphasis">Track your progress and continue learning</p>
    </div>

    <VTabs
      v-model="activeTab"
      color="primary"
      class="mb-6"
    >
      <VTab
        value="all-paths"
        :href="route('student.learning-paths.index')"
      >
        All Paths
      </VTab>

      <VTab
        value="my-learning"
        :href="route('student.learning-paths.my-paths')"
      >
        My Learning
      </VTab>
    </VTabs>

    <VRow>
      <VCol
        v-for="enrollment in enrollments"
        :key="enrollment.id"
        cols="12"
        md="6"
        lg="4"
      >
        <VCard flat border>
          <div class="pa-6 bg-primary text-white rounded-t"
          >
            <h5
              class="text-h5 font-weight-bold"
              style="color: white !important;"
            >
              {{ enrollment.learning_path?.title }}
            </h5>

            <p style="color: white !important;">
              {{ enrollment.learning_path?.courses?.length || 0 }} courses
            </p>
          </div>
          <VCardText>
            <div class="mb-4">
              <div class="d-flex justify-space-between mb-1">
                <span class="text-body-2 font-weight-medium">Overall Progress</span>
                <span class="text-h6 font-weight-bold text-purple">{{ getProgress(enrollment) }}%</span>
              </div>
              <VProgressLinear
                :model-value="getProgress(enrollment)"
                color="purple"
                height="8"
                rounded
              />
            </div>

            <div v-if="enrollment.completed_at" class="pa-3 bg-success rounded text-center mb-4">
              <div class="text-body-2 font-weight-bold text-white">✓ Completed</div>
              <div class="text-caption text-white opacity-75">{{ new Date(enrollment.completed_at).toLocaleDateString() }}</div>
            </div>
            <p v-else class="text-caption text-medium-emphasis mb-4">
              Started {{ new Date(enrollment.started_at).toLocaleDateString() }}
            </p>

            <div class="d-flex justify-space-between text-body-2 text-medium-emphasis mb-4 border-t pt-3">
              <span>Courses Completed</span>
              <span class="font-weight-bold">{{ enrollment.completed_count }}/{{ enrollment.learning_path?.courses?.length || 0 }}</span>
            </div>

            <VBtn
              block
              color="purple"
              variant="elevated"
              :href="route('student.learning-paths.show', enrollment.learning_path?.id)"
            >
              {{ enrollment.completed_at ? 'View Certificate' : 'Continue Learning' }}
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
      <VCol
        v-if="enrollments.length === 0"
        cols="12"
        class="text-center pa-8"
      >
        <VIcon icon="mdi-lightning-bolt-outline" size="64" color="disabled" />
        <h5 class="text-h5 mt-4">No Active Learning Paths</h5>
        <p class="text-body-2 text-medium-emphasis mb-4">Start a learning path to track your progress</p>
        <VBtn
          color="primary"
          variant="elevated"
          :href="route('student.learning-paths.index')"
        >
          Explore Learning Paths
        </VBtn>
      </VCol>
    </VRow>
  </div>
</template>
