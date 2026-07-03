<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  courses: {
    type: Array,
    required: true,
  },
  searchQuery: {
    type: String,
    default: '',
  },
})

const itemsPerPage = ref(6)
const page = ref(1)
const hideCompleted = ref(true)
const label = ref('All Courses')

const filteredCourses = computed(() => {
  let result = props.courses

  if (props.searchQuery) {
    const q = props.searchQuery.toLowerCase()
    result = result.filter(c =>
      c.courseTitle?.toLowerCase().includes(q) ||
      c.instructorName?.toLowerCase().includes(q)
    )
  }

  if (label.value !== 'All Courses') {
    result = result.filter(c =>
      c.tags?.toLowerCase() === label.value.toLowerCase()
    )
  }

  if (hideCompleted.value) {
    result = result.filter(c => c.completedTasks !== c.totalTasks)
  }

  return result
})

const totalFiltered = computed(() => filteredCourses.value.length)

const paginatedCourses = computed(() => {
  const start = (page.value - 1) * itemsPerPage.value
  return filteredCourses.value.slice(start, start + itemsPerPage.value)
})

function goToCourse(course) {
  if (course.enrollment_id) {
    router.get(route('student.enrollments.show', course.enrollment_id))
  } else {
    router.get(route('student.courses.show', course.id))
  }
}

function continueCourse(course) {
  if (course.enrollment_id) {
    router.get(route('student.enrollments.show', course.enrollment_id))
  } else {
    router.get(route('student.courses.show', course.id))
  }
}

function startOver(course) {
  if (course.enrollment_id) {
    router.get(route('student.enrollments.show', course.enrollment_id))
  } else {
    router.get(route('student.courses.show', course.id))
  }
}

const resolveChipColor = tags => {
  if (tags === 'Web') return 'primary'
  if (tags === 'Art') return 'success'
  if (tags === 'UI/UX') return 'error'
  if (tags === 'Psychology') return 'warning'
  if (tags === 'Design') return 'info'
  return 'primary'
}
</script>

<template>
  <VCard class="mb-6">
    <VCardText>
      <div class="d-flex justify-space-between align-center flex-wrap gap-4 mb-6">
        <div>
          <h5 class="text-h5">
            My Courses
          </h5>
          <div class="text-body-1">
            Total {{ totalFiltered }} course{{ totalFiltered !== 1 ? 's' : '' }} you have purchased
          </div>
        </div>

        <div class="d-flex flex-wrap gap-x-6 gap-y-4 align-center">
          <VSelect
            v-model="label"
            :items="[
              { title: 'All Courses', value: 'All Courses' },
              { title: 'Web', value: 'web' },
              { title: 'Art', value: 'art' },
              { title: 'UI/UX', value: 'ui/ux' },
              { title: 'Psychology', value: 'psychology' },
              { title: 'Design', value: 'design' },
            ]"
            style="min-inline-size: 260px;"
            density="default"
            variant="outlined"
            hide-details
          />
          <VSwitch
            v-model="hideCompleted"
            label="Hide Completed"
            hide-details
          />
        </div>
      </div>

      <div
        v-if="paginatedCourses.length"
        class="mb-6"
      >
        <VRow>
          <template
            v-for="course in paginatedCourses"
            :key="course.id"
          >
            <VCol
              cols="12"
              md="4"
              sm="6"
            >
              <VCard
                flat
                border
              >
                <div class="px-2 pt-2">
                  <VImg
                    :src="course.tutorImg"
                    class="cursor-pointer"
                    @click="goToCourse(course)"
                  />
                </div>
                <VCardText>
                  <div class="d-flex justify-space-between align-center mb-4">
                    <VChip
                      variant="tonal"
                      :color="resolveChipColor(course.tags)"
                      size="small"
                    >
                      {{ course.tags }}
                    </VChip>
                    <div class="d-flex">
                      <h6 class="text-h6 text-medium-emphasis align-center me-1">
                        {{ course.rating }}
                      </h6>
                      <VIcon
                        icon="mdi-star"
                        color="warning"
                        size="24"
                        class="me-2"
                      />
                      <div class="text-body-1">
                        ({{ course.ratingCount }})
                      </div>
                    </div>
                  </div>
                  <h5 class="text-h5 mb-1">
                    <a
                      class="course-title cursor-pointer text-decoration-none"
                      @click="goToCourse(course)"
                    >
                      {{ course.courseTitle }}
                    </a>
                  </h5>
                  <p>
                    {{ course.desc }}
                  </p>
                  <div
                    v-if="course.completedTasks !== course.totalTasks"
                    class="d-flex align-center mb-1"
                  >
                    <VIcon
                      icon="mdi-clock-outline"
                      size="20"
                      class="me-1"
                    />
                    <span class="text-body-1 my-auto"> {{ course.time }}</span>
                  </div>
                  <div
                    v-else
                    class="mb-1"
                  >
                    <VIcon
                      icon="mdi-check-circle"
                      size="20"
                      color="success"
                      class="me-1"
                    />
                    <span class="text-success text-body-1">Completed</span>
                  </div>
                  <VProgressLinear
                    :model-value="course.totalTasks > 0 ? (course.completedTasks / course.totalTasks) * 100 : 0"
                    rounded
                    color="primary"
                    height="8"
                    class="mb-4"
                  />
                  <div class="d-flex flex-wrap gap-4">
                    <VBtn
                      variant="tonal"
                      color="secondary"
                      class="flex-grow-1"
                      @click="startOver(course)"
                    >
                      <template #prepend>
                        <VIcon
                          icon="mdi-restart"
                          class="flip-in-rtl"
                        />
                      </template>
                      Start Over
                    </VBtn>
                    <VBtn
                      v-if="course.completedTasks !== course.totalTasks"
                      variant="tonal"
                      class="flex-grow-1"
                      @click="continueCourse(course)"
                    >
                      <template #append>
                        <VIcon
                          icon="mdi-chevron-right"
                          class="flip-in-rtl"
                        />
                      </template>
                      Continue
                    </VBtn>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </template>
        </VRow>
      </div>

      <div v-else>
        <h4 class="text-h4 text-center mb-6">
          No Course Found
        </h4>
      </div>

      <VPagination
        v-model="page"
        active-color="primary"
        first-icon="mdi-chevron-double-left"
        last-icon="mdi-chevron-double-right"
        show-first-last-page
        :length="Math.ceil(totalFiltered / itemsPerPage)"
      />
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.course-title {
  &:not(:hover) {
    color: rgba(var(--v-theme-on-surface), var(--v-text-high-emphasis));
  }
}
</style>
