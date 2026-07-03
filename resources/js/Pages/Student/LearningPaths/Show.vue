<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  learningPath: { type: Object, required: true },
  enrollment: { type: Object, default: null },
  progress: { type: Number, default: 0 },
  nextCourse: { type: Object, default: null },
  completedCourseIds: { type: Array, default: () => [] },
  isLocked: { type: Boolean, default: false },
})

function enroll() {
  router.post(route('student.learning-paths.enroll', props.learningPath.id), {}, {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <VBtn
      variant="text"
      color="primary"
      size="small"
      class="mb-4"
      :href="route('student.learning-paths.index')"
    >
      <VIcon start icon="mdi-arrow-left" />
      Back to Learning Paths
    </VBtn>

    <h4 class="text-h4 font-weight-bold mb-2">{{ learningPath.title }}</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ learningPath.description }}</p>

    <!-- Locked / Not Enrolled -->
    <VCard v-if="isLocked || !enrollment" flat border class="pa-8 text-center mb-8 bg-warning-light">
      <VIcon icon="mdi-lock" size="48" color="warning" />
      <h5 class="text-h5 mt-3 mb-2">Enroll to Get Started</h5>
      <p class="text-body-2 text-medium-emphasis mb-4">Enroll in this learning path to start learning!</p>

      <div class="d-flex justify-center ga-6 mb-4">
        <div class="text-center">
          <div class="text-h3 font-weight-bold text-primary">{{ learningPath.courses?.length || 0 }}</div>
          <div class="text-caption text-medium-emphasis">Courses</div>
        </div>
        <div class="text-center">
          <div class="text-h3 font-weight-bold text-purple">~{{ (learningPath.courses?.length || 0) * 4 }}h</div>
          <div class="text-caption text-medium-emphasis">Duration</div>
        </div>
      </div>

      <VBtn color="primary" size="large" @click="enroll">
        Enroll Now
      </VBtn>
    </VCard>

    <!-- Enrolled View -->
    <template v-else>
      <VCard flat border class="pa-6 mb-6">
        <div class="d-flex justify-space-between align-center mb-4">
          <h5 class="text-h5 font-weight-bold">Your Progress</h5>
          <span class="text-h5 font-weight-bold text-primary">{{ progress }}%</span>
        </div>
        <VProgressLinear
          :model-value="progress"
          color="primary"
          height="8"
          rounded
        />
        <p class="text-body-2 text-medium-emphasis mt-4">
          <span v-if="enrollment.completed_at" class="text-success font-weight-bold">
            ✓ Completed on {{ new Date(enrollment.completed_at).toLocaleDateString() }}
          </span>
          <span v-else>
            Started {{ new Date(enrollment.started_at).toLocaleDateString() }}
          </span>
        </p>
      </VCard>

      <VCard v-if="nextCourse" flat border class="pa-6 mb-6 bg-success-light">
        <h6 class="text-h6 font-weight-bold text-success mb-2">Next Course</h6>
        <p class="text-body-1 mb-3">{{ nextCourse.title }}</p>
        <VBtn
          color="success"
          variant="elevated"
          :href="route('student.courses.show', nextCourse.slug || nextCourse.id)"
        >
          Start Course
          <VIcon end icon="mdi-arrow-right" />
        </VBtn>
      </VCard>
    </template>

    <!-- Courses List -->
    <VCard flat border>
      <VCardTitle>Courses in This Path</VCardTitle>
      <VList class="pa-0">
        <VListItem
          v-for="(course, index) in (learningPath.courses || [])"
          :key="course.id"
          class="border-t"
        >
          <div class="d-flex align-center ga-4 w-100 py-2">
            <VAvatar
              :color="completedCourseIds.includes(course.id) ? 'success' : (!enrollment ? 'grey' : 'primary')"
              variant="tonal"
              size="40"
              rounded
            >
              <VIcon
                :icon="completedCourseIds.includes(course.id) ? 'mdi-check' : (!enrollment ? 'mdi-lock' : String(index + 1))"
                size="20"
              />
            </VAvatar>
            <div class="flex-grow-1">
              <div class="font-weight-medium">{{ course.title }}</div>
              <div class="text-caption text-medium-emphasis">
                {{ course.lessons_count || 0 }} lessons
              </div>
            </div>
            <div>
              <VChip
                v-if="completedCourseIds.includes(course.id)"
                color="success"
                size="small"
                variant="tonal"
                label
              >
                Completed
              </VChip>
              <VBtn
                v-else-if="enrollment && (index === 0 || completedCourseIds.includes(learningPath.courses[index - 1]?.id))"
                variant="tonal"
                color="primary"
                size="small"
                :href="route('student.courses.show', course.id)"
              >
                {{ enrollment ? 'Continue' : 'Start' }}
              </VBtn>
              <VChip v-else color="grey" size="small" variant="tonal" label>
                Locked
              </VChip>
            </div>
          </div>
        </VListItem>
      </VList>
    </VCard>
  </div>
</template>
