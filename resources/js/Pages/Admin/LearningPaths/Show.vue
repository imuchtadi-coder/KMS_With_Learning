<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const { learningPath, availableCourses } = defineProps({
  learningPath: { type: Object, required: true },
  availableCourses: { type: Array, default: () => [] },
})

const form = useForm({ course_id: '' })
const reordering = ref(false)

const addCourse = () => {
  form.post(route('admin.learning-paths.add-course', learningPath.id), {
    preserveScroll: true,
    onSuccess: () => { form.course_id = '' },
  })
}

const removeCourse = (courseId) => {
  router.delete(route('admin.learning-paths.remove-course', [learningPath.id, courseId]), {
    preserveScroll: true,
  })
}

const reorderCourse = (courseId, direction) => {
  if (reordering.value) return

  reordering.value = true
  router.post(route('admin.learning-paths.courses.reorder', [learningPath.id, courseId]), {
    direction,
  }, {
    preserveScroll: true,
    onFinish: () => { reordering.value = false },
  })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ learningPath.title }}</h4>
        <p class="text-body-2 text-medium-emphasis">
          <VChip
            :color="learningPath.published_at ? 'success' : 'warning'"
            size="x-small"
            variant="tonal"
            label
          >
            {{ learningPath.published_at ? 'Published' : 'Draft' }}
          </VChip>
        </p>
      </div>
      <div class="d-flex ga-2">
        <VBtn
          v-if="!learningPath.published_at"
          color="success"
          variant="tonal"
          prepend-icon="mdi-check"
          @click="router.post(route('admin.learning-paths.publish', learningPath.id))"
        >
          Publish
        </VBtn>
        <VBtn
          v-else
          color="warning"
          variant="tonal"
          prepend-icon="mdi-close"
          @click="router.post(route('admin.learning-paths.unpublish', learningPath.id))"
        >
          Unpublish
        </VBtn>
        <VBtn variant="outlined" :href="route('admin.learning-paths.edit', learningPath.id)">Edit</VBtn>
        <VBtn variant="outlined" :href="route('admin.learning-paths.analytics.path', learningPath.id)">Analytics</VBtn>
        <VBtn variant="outlined" :href="route('admin.learning-paths.index')">Back</VBtn>
      </div>
    </div>

    <VRow>
      <VCol cols="12" md="8">
        <VCard flat border :title="'Courses (' + (learningPath.courses?.length || 0) + ')'">
          <VCardText v-if="!learningPath.courses?.length" class="text-center pa-8">
            <VIcon icon="mdi-book-off" size="48" color="disabled" class="mb-2" />
            <div class="text-medium-emphasis">No courses added yet</div>
          </VCardText>

          <VList v-else lines="two">
            <VListItem
              v-for="(course, idx) in learningPath.courses"
              :key="course.id"
            >
              <template #prepend>
                <VAvatar color="primary" variant="tonal" size="36">
                  <span class="font-weight-bold">{{ idx + 1 }}</span>
                </VAvatar>
              </template>
              <VListItemTitle class="font-weight-medium">{{ course.title }}</VListItemTitle>
              <VListItemSubtitle>
                {{ course.sections?.length || 0 }} sections
              </VListItemSubtitle>

              <template #append>
                <div class="d-flex ga-1">
                  <VBtn icon size="x-small" variant="tonal" :disabled="reordering || idx === 0" @click="reorderCourse(course.id, 'up')">
                    <VIcon icon="mdi-arrow-up" size="16" />
                  </VBtn>
                  <VBtn icon size="x-small" variant="tonal" :disabled="reordering || idx === learningPath.courses.length - 1" @click="reorderCourse(course.id, 'down')">
                    <VIcon icon="mdi-arrow-down" size="16" />
                  </VBtn>
                  <VBtn icon size="x-small" color="error" variant="tonal" @click="removeCourse(course.id)">
                    <VIcon icon="mdi-close" size="16" />
                  </VBtn>
                </div>
              </template>
            </VListItem>
          </VList>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard flat border title="Add Course">
          <VCardText>
            <p class="text-caption text-medium-emphasis mb-4">Select a course to add to this path</p>
            <VForm @submit.prevent="addCourse">
              <VSelect
                v-model="form.course_id"
                :items="availableCourses.map(c => ({ title: c.title, value: c.id }))"
                label="Course"
                :error-messages="form.errors.course_id"
                required
              />
              <VBtn type="submit" color="primary" block :loading="form.processing" class="mt-2">
                Add Course
              </VBtn>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
