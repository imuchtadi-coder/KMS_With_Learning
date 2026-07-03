<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, default: null },
  lesson: { type: Object, default: null },
})

const form = useForm({
  course_id: props.course?.id ?? '',
  lesson_id: props.lesson?.id ?? '',
  title: '',
  content: '',
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
    <p v-if="course" class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
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
            :href="route('discussions.index', { course_id: course?.id })"
          >
            Cancel
          </VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
