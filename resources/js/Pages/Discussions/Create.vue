<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    default: null,
  },

  lesson: {
    type: Object,
    default: null,
  },

  courses: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  course_id: props.course?.id ?? '',
  lesson_id: props.lesson?.id ?? '',
  title: '',
  content: '',
})

const selectedCourse = computed(() => {
  return props.courses.find(
    course => Number(course.id) === Number(form.course_id)
  ) ?? null
})

const lessonItems = computed(() => {
  if (!selectedCourse.value?.sections) {
    return []
  }

  return selectedCourse.value.sections.flatMap(section =>
    (section.lessons ?? []).map(lesson => ({
      id: lesson.id,
      title: lesson.title,
    }))
  )
})

const submit = () => {
  form.post(route('discussions.store'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-2">New Discussion</h4>
    <!-- <p v-if="course" class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p> -->

    <VCard flat border class="pa-8">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <VSelect
          v-model="form.course_id"
          :items="courses"
          item-title="title"
          item-value="id"
          label="Course"
          variant="outlined"
          :rules="[v => !!v || 'Course is required']"
          hide-details="auto"
        />

        <VSelect
          v-model="form.lesson_id"
          :items="lessonItems"
          item-title="title"
          item-value="id"
          label="Lesson (Optional)"
          variant="outlined"
          clearable
          :disabled="!form.course_id || lessonItems.length === 0"
          hide-details="auto"
        />

        <VTextField
          v-model="form.title"
          label="Title"
          variant="outlined"
          density="default"
          :rules="[v => !!v || 'Title is required']"
          hide-details="auto"
        />

        <VTextarea
          v-model="form.content"
          label="Content"
          variant="outlined"
          density="default"
          rows="6"
          :rules="[v => !!v || 'Content is required']"
          hide-details="auto"
        />

        <div class="d-flex ga-3 pt-2">
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            :loading="form.processing"
            :disabled="form.processing"
          >
            Create Discussion
          </VBtn>
          <VBtn
            variant="outlined"
            :href="route('discussions.index', {
              course_id: form.course_id || undefined,
            })"
          >
            Cancel
          </VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
