<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  courses: { type: Object, required: true },
})

const search = ref('')
const statusFilter = ref('')

const filtered = computed(() => {
  let items = props.courses.data || []
  if (search.value) {
    const q = search.value.toLowerCase()
    items = items.filter(c => c.title?.toLowerCase().includes(q))
  }
  if (statusFilter.value) {
    items = items.filter(c => statusFilter.value === 'published' ? c.published : !c.published)
  }
  return items
})

function destroy(course) {
  if (!confirm(`Delete course "${course.title}"?`)) return
  router.delete(route('instructor.courses.destroy', course.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">My Courses</h4>
        <p class="text-body-1 text-medium-emphasis">Manage and create courses</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('instructor.courses.create')">
        Create Course
      </VBtn>
    </div>

    <VCard flat border class="pa-4 mb-6">
      <VRow align="center">
        <VCol cols="12" md="5">
          <VTextField v-model="search" placeholder="Search courses..." prepend-inner-icon="mdi-magnify" variant="outlined" density="default" hide-details />
        </VCol>
        <VCol cols="12" md="3">
          <VSelect v-model="statusFilter" :items="[{ title: 'All Status', value: '' }, { title: 'Published', value: 'published' }, { title: 'Draft', value: 'draft' }]" variant="outlined" density="default" hide-details />
        </VCol>
        <VCol cols="12" md="4" class="text-right">
          <VBtn variant="tonal" color="secondary" size="small" @click="router.get(route('instructor.courses.index'), { search: search.value, status: statusFilter.value }, { preserveState: true })">Filter</VBtn>
        </VCol>
      </VRow>
    </VCard>

    <VRow>
      <VCol v-for="course in filtered" :key="course.id" cols="12" md="6" lg="4">
        <VCard flat border hover>
          <div class="position-relative overflow-hidden" style="height: 160px;">
            <img v-if="course.thumbnail" :src="course.thumbnail" class="w-100 h-100" style="object-fit: cover; object-position: center;" />
            <div v-if="course.thumbnail" class="position-absolute w-100 h-100" style="inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.3), transparent);" />
            <div v-else class="w-100 h-100" style="background: linear-gradient(135deg, rgb(115, 103, 240), rgb(94, 75, 227));" />
            <div class="pa-4 d-flex justify-end position-absolute" style="inset: 0;">
              <VChip :color="course.published ? 'success' : 'warning'" size="small" variant="tonal" label class="align-self-start">
                {{ course.published ? 'Published' : 'Draft' }}
              </VChip>
            </div>
          </div>
          <VCardText>
            <h6 class="text-h6 font-weight-bold mb-1 line-clamp-2">{{ course.title }}</h6>
            <p class="text-body-2 text-medium-emphasis text-truncate mb-4">{{ course.description }}</p>
            <div class="d-flex ga-2 mb-4 text-center">
              <div class="flex-grow-1 pa-2 rounded-lg" style="background: rgb(210, 207, 241);">
                <div class="text-h6 font-weight-bold">{{ course.sections?.length || 0 }}</div>
                <div class="text-caption text-medium-emphasis">Sections</div>
              </div>
              <div class="flex-grow-1 pa-2 rounded-lg" style="background: rgb(210, 207, 241);">
                <div class="text-h6 font-weight-bold">{{ course.sections?.reduce((a, s) => a + (s.lessons?.length || 0), 0) || 0 }}</div>
                <div class="text-caption text-medium-emphasis">Lessons</div>
              </div>
              <div class="flex-grow-1 pa-2 rounded-lg" style="background: rgb(210, 207, 241);">
                <div class="text-h6 font-weight-bold">{{ course.enrollments?.length || 0 }}</div>
                <div class="text-caption text-medium-emphasis">Students</div>
              </div>
            </div>
            <div class="d-flex ga-2" style="flex-wrap: nowrap;">
              <div class="flex-grow-1">
                <VBtn variant="tonal" color="primary" block :href="route('instructor.courses.show', course.id)">Detail</VBtn>
              </div>
              <div class="flex-grow-1">
                <VBtn variant="elevated" color="primary" block :href="route('instructor.courses.edit', course.id)">Edit</VBtn>
              </div>
              <VBtn icon variant="text" color="error" size="small" class="flex-shrink-0" @click="destroy(course)">
                <VIcon icon="mdi-delete" size="20" />
              </VBtn>
            </div>
            <div class="d-flex ga-2" style="flex-wrap: nowrap; margin-top: 4%;">
              <VBtn variant="tonal" color="info" block :href="route('instructor.courses.quiz-grading', course.id)">Quiz Grading</VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard v-if="!filtered.length" flat border class="pa-12 text-center mt-4">
      <VIcon icon="mdi-book-open-blank-variant" size="64" color="disabled" />
      <h5 class="text-h5 mt-4 mb-2">No courses found</h5>
      <p class="text-body-2 text-medium-emphasis mb-4">Try adjusting your filters or create a new course</p>
      <VBtn color="primary" variant="elevated" :href="route('instructor.courses.create')">Create First Course</VBtn>
    </VCard>

    <div v-if="courses.last_page > 1" class="d-flex justify-center mt-6">
      <VPagination :model-value="courses.current_page" :length="courses.last_page" :total-visible="5"
        @update:model-value="(p) => router.get(route('instructor.courses.index'), { page: p }, { preserveState: true })" />
    </div>
  </div>
</template>
