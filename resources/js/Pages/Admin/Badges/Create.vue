<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  description: '',
  image_url: '',
  criteria_type: '',
  criteria_value: '',
  active: true,
})

const criteriaTypes = [
  { title: 'Lesson Complete', value: 'lesson_complete' },
  { title: 'Course Complete', value: 'course_complete' },
  { title: 'Quiz Perfect Score', value: 'quiz_perfect_score' },
  { title: 'Points Milestone', value: 'points_milestone' },
  { title: 'Consecutive Days', value: 'consecutive_days' },
]

const submit = () => {
  form.post(route('admin.badges.store'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Create Badge</h4>
      <p class="text-body-2 text-medium-emphasis">Define a new achievement badge</p>
    </div>

    <VCard flat border max-width="700">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField v-model="form.name" label="Name" :error-messages="form.errors.name" required />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.image_url" label="Image URL (optional)" :error-messages="form.errors.image_url" />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="form.description" label="Description" :error-messages="form.errors.description" rows="3" />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect
                v-model="form.criteria_type"
                :items="criteriaTypes"
                label="Criteria Type"
                :error-messages="form.errors.criteria_type"
                required
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model.number="form.criteria_value"
                label="Criteria Value"
                type="number"
                min="1"
                :error-messages="form.errors.criteria_value"
                required
              />
            </VCol>
            <VCol cols="12">
              <VSwitch v-model="form.active" label="Active" color="success" />
            </VCol>
            <VCol cols="12" class="d-flex ga-2">
              <VBtn type="submit" color="primary" :loading="form.processing">Create Badge</VBtn>
              <VBtn variant="outlined" :href="route('admin.badges.index')">Cancel</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
