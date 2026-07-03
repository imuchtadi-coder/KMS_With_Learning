<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  courses: {
    type: Array,
    required: true,
  },
})

const searchQuery = ref('')
const itemsPerPage = ref(5)
const page = ref(1)

const headers = [
  { title: 'Course Name', key: 'courseName' },
  { title: 'Time', key: 'time', sortable: false },
  { title: 'Progress', key: 'progress' },
  { title: 'Status', key: 'status', sortable: false },
]

const filteredCourses = computed(() => {
  if (!searchQuery.value) return props.courses
  const q = searchQuery.value.toLowerCase()
  return props.courses.filter(c =>
    c.courseTitle?.toLowerCase().includes(q) ||
    c.instructorName?.toLowerCase().includes(q)
  )
})

function goToCourse(course) {
  router.get(route('student.courses.show', course.id))
}
</script>

<template>
  <VCard>
    <VCardText>
      <div class="d-flex flex-wrap justify-space-between align-center gap-4">
        <h5 class="text-h5 font-weight-medium">
          Courses you are taking
        </h5>
        <div>
          <VTextField
            v-model="searchQuery"
            placeholder="Search Course"
            density="default"
            variant="outlined"
            hide-details
            style="max-inline-size: 300px;min-inline-size: 300px;"
          />
        </div>
      </div>
    </VCardText>

    <VDivider />
    <VDataTable
      v-model:items-per-page="itemsPerPage"
      v-model:page="page"
      :items-per-page-options="[
        { value: 5, title: '5' },
        { value: 10, title: '10' },
        { value: 20, title: '20' },
        { value: -1, title: '$vuetify.dataFooter.itemsPerPageAll' },
      ]"
      :headers="headers"
      :items="filteredCourses"
      :items-length="filteredCourses.length"
      show-select
      class="text-no-wrap"
    >
      <template #item.courseName="{ item }">
        <div
          class="d-flex align-center gap-x-4 py-2 cursor-pointer"
          @click="goToCourse(item)"
        >
          <VAvatar
            variant="tonal"
            size="40"
            rounded
            :color="item.color"
          >
            <VIcon
              :icon="item.logo"
              size="28"
            />
          </VAvatar>

          <div>
            <div class="text-base font-weight-medium mb-1 text-link">
              {{ item.courseTitle }}
            </div>
            <div class="d-flex align-center">
              <VAvatar
                size="22"
                :image="item.image"
              />
              <div class="text-body-2 text-high-emphasis ms-2">
                {{ item.instructorName }}
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #item.time="{ item }">
        <h6 class="text-h6">
          {{ item.time }}
        </h6>
      </template>

      <template #item.progress="{ item }">
        <div
          class="d-flex align-center gap-x-4"
          style="inline-size: 15.625rem;"
        >
          <div class="text-no-wrap font-weight-medium text-high-emphasis">
            {{ item.totalTasks > 0 ? Math.floor((item.completedTasks / item.totalTasks) * 100) : 0 }}%
          </div>
          <div class="w-100">
            <VProgressLinear
              color="primary"
              height="8"
              :model-value="item.totalTasks > 0 ? Math.floor((item.completedTasks / item.totalTasks) * 100) : 0"
              rounded
            />
          </div>
          <div class="text-body-2">
            {{ item.completedTasks }}/{{ item.totalTasks }}
          </div>
        </div>
      </template>

      <template #item.status="{ item }">
        <div class="d-flex gap-x-5">
          <div class="d-flex gap-x-2 align-center">
            <VIcon
              icon="mdi-account-group"
              color="primary"
              size="24"
            />
            <div class="text-body-1">
              {{ item.enrollmentCount }}
            </div>
          </div>
          <div class="d-flex gap-x-2 align-center">
            <VIcon
              icon="mdi-book-open"
              color="info"
              size="24"
            />
            <span class="text-body-1">{{ item.totalTasks }}</span>
          </div>
          <div class="d-flex gap-x-2 align-center">
            <VIcon
              icon="mdi-play-circle"
              color="error"
              size="24"
            />
            <span class="text-body-1">{{ item.view }}</span>
          </div>
        </div>
      </template>
    </VDataTable>
  </VCard>
</template>
