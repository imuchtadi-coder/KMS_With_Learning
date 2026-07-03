<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  assignment: { type: Object, required: true },
})

const form = useForm({
  title: props.assignment.title,
  description: props.assignment.description || '',
  instructions: props.assignment.instructions || '',
  section_id: props.assignment.section_id,
  max_score: props.assignment.max_score,
  passing_grade: props.assignment.passing_grade,
  deadline: props.assignment.deadline ? new Date(props.assignment.deadline).toISOString().slice(0, 16) : '',
  file_types: props.assignment.file_types || '',
  max_file_size: props.assignment.max_file_size || 10,
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Edit Assignment</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="640">
      <VForm @submit.prevent="form.put(route('instructor.assignments.update', [course.id, assignment.id]))" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Assignment Title" variant="outlined" density="default" />
        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="3" />
        <VTextarea v-model="form.instructions" label="Instructions" variant="outlined" density="default" rows="4" />
        <VSelect v-model="form.section_id" label="Section" variant="outlined" density="default" :items="(course.sections || []).map(s => ({ title: s.title, value: s.id }))" />
        <VRow>
          <VCol cols="4"><VTextField v-model="form.max_score" label="Max Score" variant="outlined" density="default" type="number" /></VCol>
          <VCol cols="4"><VTextField v-model="form.passing_grade" label="Passing Grade (%)" variant="outlined" density="default" type="number" /></VCol>
          <VCol cols="4"><VTextField v-model="form.deadline" label="Deadline" variant="outlined" density="default" type="datetime-local" /></VCol>
        </VRow>
        <VTextField v-model="form.file_types" label="Allowed File Types" variant="outlined" density="default" hint="e.g., pdf,doc,docx" persistent-hint />
        <VTextField v-model="form.max_file_size" label="Max File Size (MB)" variant="outlined" density="default" type="number" />
        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Update Assignment</VBtn>
          <VBtn variant="outlined" :href="route('instructor.assignments.show', [course.id, assignment.id])">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
