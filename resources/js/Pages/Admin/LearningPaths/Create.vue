<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({
  courses: { type: Array, default: () => [] },
})

const form = useForm({
  title: '',
  description: '',
})

const submit = () => {
  form.post(route('admin.learning-paths.store'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Create Learning Path</h4>
      <p class="text-body-2 text-medium-emphasis">Define a structured learning journey</p>
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
              <VBtn type="submit" color="primary" :loading="form.processing">Create Path</VBtn>
              <VBtn variant="outlined" :href="route('admin.learning-paths.index')">Cancel</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
