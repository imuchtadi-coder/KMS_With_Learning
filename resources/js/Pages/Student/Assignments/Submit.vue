<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  assignment: { type: Object, required: true },
})

const form = useForm({
  notes: '',
  file: null,
})

const submit = () => {
  form.post(route('student.assignments.submit', props.assignment.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <nav class="d-flex align-center text-body-2 text-medium-emphasis mb-6 ga-1">
      <a class="text-link text-decoration-none" :href="route('student.dashboard')">Dashboard</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <a class="text-link text-decoration-none" :href="route('student.enrollments')">My Learning</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <a class="text-link text-decoration-none" :href="route('student.assignments.show', assignment.id)">{{ assignment.title }}</a>
      <VIcon icon="mdi-chevron-right" size="16" />
      <span class="text-high-emphasis font-weight-medium">Submit</span>
    </nav>

    <h4 class="text-h4 font-weight-bold mb-2">Submit Assignment</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ assignment.title }}</p>

    <VCard flat border class="pa-8">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-6">
        <div>
          <VLabel class="text-body-2 font-weight-bold mb-2 d-block">Upload File</VLabel>
          <VFileInput
            v-model="form.file"
            :rules="[v => !!v || 'File is required']"
            :accept="assignment.file_types || undefined"
            :hint="'Max size: ' + (assignment.max_file_size ?? 10) + 'MB'"
            persistent-hint
            variant="outlined"
            density="default"
            prepend-icon="mdi-file-upload-outline"
          />
          <p v-if="assignment.file_types" class="text-caption text-medium-emphasis mt-1">
            Allowed types: {{ assignment.file_types }}.
          </p>
        </div>

        <div>
          <VLabel class="text-body-2 font-weight-bold mb-2 d-block">Notes (optional)</VLabel>
          <VTextarea
            v-model="form.notes"
            variant="outlined"
            density="default"
            maxlength="1000"
            placeholder="Add any notes for your instructor..."
            rows="4"
            counter
          />
        </div>

        <div class="d-flex ga-3 pt-2">
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            :loading="form.processing"
            :disabled="form.processing"
          >
            Submit Assignment
          </VBtn>
          <VBtn
            variant="outlined"
            :href="route('student.assignments.show', assignment.id)"
          >
            Cancel
          </VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
