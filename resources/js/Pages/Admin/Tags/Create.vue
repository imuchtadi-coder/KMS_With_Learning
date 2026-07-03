<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ name: '' })

const submit = () => {
  form.post(route('admin.tags.store'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Create Tag</h4>
      <p class="text-body-2 text-medium-emphasis">Add a new course tag</p>
    </div>

    <VCard flat border max-width="500">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VTextField
            v-model="form.name"
            label="Tag Name"
            :error-messages="form.errors.name"
            required
            class="mb-4"
          />
          <div class="d-flex ga-2">
            <VBtn type="submit" color="primary" :loading="form.processing">Create</VBtn>
            <VBtn variant="outlined" :href="route('admin.tags.index')">Cancel</VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
