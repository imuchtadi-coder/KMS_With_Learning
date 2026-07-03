<template>
  <div>
    <h4 class="text-h4 font-weight-medium mb-2">
      Explore Courses
    </h4>
    <p class="text-body-1 text-medium-emphasis mb-6">
      Discover and enroll in courses in <strong>{{ tenantName }}</strong>
    </p>

    <v-card flat border class="mb-6 pa-4">
      <v-row dense align="end">
        <v-col cols="12" sm="6" md="4">
          <v-text-field
            v-model="filters.search"
            label="Search courses..."
            variant="outlined"
            density="default"
            prepend-inner-icon="mdi-magnify"
            hide-details
            clearable
            @update:model-value="debouncedSearch"
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-select
            v-model="filters.level"
            :items="levelOptions"
            label="Level"
            variant="outlined"
            density="default"
            hide-details
            clearable
            @update:model-value="applyFilters"
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-select
            v-model="filters.category_id"
            :items="categories"
            item-title="name"
            item-value="id"
            label="Category"
            variant="outlined"
            density="default"
            hide-details
            clearable
            @update:model-value="applyFilters"
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-select
            v-model="filters.tag_id"
            :items="tags"
            item-title="name"
            item-value="id"
            label="Tag"
            variant="outlined"
            density="default"
            hide-details
            clearable
            @update:model-value="applyFilters"
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-btn
            variant="tonal"
            color="secondary"
            block
            @click="clearFilters"
          >
            Clear
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <v-row v-if="courses.data.length === 0">
      <v-col cols="12">
        <v-card flat border class="text-center pa-8">
          <v-icon icon="mdi-book-open-blank-variant" size="64" color="disabled" />
          <p class="text-body-1 mt-4">No courses found matching your criteria.</p>
        </v-card>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col
        v-for="course in courses.data"
        :key="course.id"
        cols="12"
        sm="6"
        lg="4"
      >
        <v-card flat border class="d-flex flex-column h-100">
          <div class="position-relative overflow-hidden" style="height: 140px; cursor: pointer;">
            <a :href="route('student.courses.show', course.id)" class="text-decoration-none d-block w-100 h-100 position-relative">
              <img v-if="course.thumbnail" :src="course.thumbnail" class="w-100 h-100" style="object-fit: cover; object-position: center;" />
              <div v-if="course.thumbnail" class="position-absolute w-100 h-100" style="inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.3), transparent);" />
              <div v-else class="w-100 h-100" style="background: linear-gradient(135deg, rgb(115, 103, 240), rgb(94, 75, 227));" />
            </a>
            <div class="pa-3 d-flex justify-end position-absolute" style="inset: 0; pointer-events: none;">
              <v-chip :color="levelColor(course.level)" size="x-small" variant="tonal" label class="align-self-start">
                {{ course.level }}
              </v-chip>
            </div>
          </div>
          <v-card-text class="flex-grow-1">
            <h5 class="text-h5 mb-1">
              {{ course.courseTitle ?? course.title }}
            </h5>
            <p class="text-body-2 text-medium-emphasis text-truncate mb-3">
              {{ course.desc ?? course.description }}
            </p>
            <div class="d-flex align-center text-body-2 mb-1">
              <v-icon icon="mdi-account" size="16" class="me-1" />
              {{ course.instructorName ?? course.instructor?.name ?? 'Unknown' }}
            </div>
            <div class="d-flex align-center text-body-2 mb-1">
              <v-icon icon="mdi-book-open" size="16" class="me-1" />
              {{ course.totalTasks ?? course.sections?.length ?? 0 }} sections
            </div>
            <div class="d-flex align-center text-body-2 mb-3">
              <v-icon icon="mdi-account-group" size="16" class="me-1" />
              {{ course.enrollmentCount ?? course.enrollments_count ?? 0 }} students
            </div>
            <div class="d-flex align-center mb-1">
              <v-rating
                :model-value="course.rating ?? 0"
                color="warning"
                density="compact"
                size="small"
                readonly
              />
              <span class="text-body-2 ms-1">({{ course.reviews_count ?? 0 }})</span>
            </div>
          </v-card-text>
          <v-divider />
          <v-card-actions class="pa-4">
            <div class="text-h6 font-weight-bold me-auto">
              {{ course.price > 0 ? `Rp ${formatPrice(course.price)}` : 'Free' }}
            </div>
            <v-btn
              v-if="course.isEnrolled ?? course.enrolled"
              color="primary"
              variant="tonal"
              size="small"
              :href="route('student.enrollments.show', course.enrollment_id ?? course.id)"
            >
              Continue Learning
            </v-btn>
            <v-btn
              v-else
              color="primary"
              variant="elevated"
              size="small"
              @click="enroll(course.id)"
            >
              Enroll Now
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <div v-if="courses.last_page > 1" class="mt-6 d-flex justify-center">
      <v-pagination
        v-model="page"
        :length="courses.last_page"
        :total-visible="5"
        @update:model-value="changePage"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  courses: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
  tenantName: { type: String, default: '' },
  filters: { type: Object, default: () => ({}) },
})

const levelOptions = [
  { title: 'All Levels', value: '' },
  { title: 'Beginner', value: 'beginner' },
  { title: 'Intermediate', value: 'intermediate' },
  { title: 'Advanced', value: 'advanced' },
]

const page = ref(props.courses.current_page ?? 1)

const filters = reactive({
  search: props.filters.search ?? '',
  level: props.filters.level ?? '',
  category_id: props.filters.category_id ?? '',
  tag_id: props.filters.tag_id ?? '',
})

let debounceTimer = null
function debouncedSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(applyFilters, 400)
}

function applyFilters() {
  page.value = 1
  router.get(route('student.courses.list'), filters, {
    preserveState: true,
    replace: true,
  })
}

function clearFilters() {
  filters.search = ''
  filters.level = ''
  filters.category_id = ''
  filters.tag_id = ''
  applyFilters()
}

function changePage(newPage) {
  router.get(route('student.courses.list'), { ...filters, page: newPage }, {
    preserveState: true,
    replace: true,
  })
}

function enroll(courseId) {
  router.post(route('student.courses.enroll', courseId), {}, {
    preserveScroll: true,
  })
}

function levelColor(level) {
  const map = { beginner: 'success', intermediate: 'warning', advanced: 'error' }
  return map[level] ?? 'primary'
}

function formatPrice(price) {
  return Number(price).toLocaleString('id-ID')
}
</script>
