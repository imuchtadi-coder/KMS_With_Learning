<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  learningPath: { type: Object, required: true },
})

const form = useForm({
  title: props.learningPath.title || '',
  description: props.learningPath.description || '',
})

const submit = () => {
  form.put(route('admin.learning-paths.update', props.learningPath.id))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Edit Learning Path</h4>
      <p class="text-body-2 text-medium-emphasis">{{ learningPath.title }}</p>
    </div>

    <VCard flat border max-width="700">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="form.title"
                label="Title"
                :error-messages="form.errors.title"
                required
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="form.description"
                label="Description"
                :error-messages="form.errors.description"
                rows="4"
              />
            </VCol>
            <VCol cols="12" class="d-flex ga-2">
              <VBtn type="submit" color="primary" :loading="form.processing">Update Path</VBtn>
              <VBtn variant="outlined" :href="route('admin.learning-paths.show', learningPath.id)">Cancel</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
