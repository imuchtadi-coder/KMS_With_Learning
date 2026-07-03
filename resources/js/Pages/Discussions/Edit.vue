<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  discussion: { type: Object, required: true },
  course: { type: Object, default: null },
})

const form = useForm({
  title: props.discussion.title,
  content: props.discussion.content,
})

const submit = () => {
  form.put(route('discussions.update', props.discussion.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-2">Edit Discussion</h4>
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
            Update Discussion
          </VBtn>
          <VBtn
            variant="outlined"
            :href="route('discussions.show', discussion.id)"
          >
            Cancel
          </VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
