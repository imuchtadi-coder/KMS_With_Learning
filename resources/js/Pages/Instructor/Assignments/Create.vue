<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
})

const form = useForm({
  title: '',
  description: '',
  instructions: '',
  section_id: '',
  max_score: 100,
  passing_grade: 70,
  deadline: '',
  file_types: '',
  max_file_size: 10,
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Create Assignment</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="780">
      <VForm @submit.prevent="form.post(route('instructor.assignments.store', course.id))" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Assignment Title" variant="outlined" density="default" :rules="[v => !!v || 'Title is required']" hide-details="auto" />
        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="3" hide-details="auto" />
        <VTextarea v-model="form.instructions" label="Instructions" variant="outlined" density="default" rows="4" hint="Detailed instructions for students" persistent-hint />
        <VSelect v-model="form.section_id" label="Section" variant="outlined" density="default" :items="(course.sections || []).map(s => ({ title: s.title, value: s.id }))" :rules="[v => !!v || 'Section is required']" hide-details="auto" />
        <VRow>
          <VCol cols="3"><VTextField v-model="form.max_score" label="Max Score" variant="outlined" density="default" type="number" min="1" /></VCol>
          <VCol cols="3"><VTextField v-model="form.passing_grade" label="Passing Grade (%)" variant="outlined" density="default" type="number" min="0" max="100" /></VCol>
          <VCol cols="4"><VTextField v-model="form.deadline" label="Deadline" variant="outlined" density="default" type="datetime-local"/></VCol>
        </VRow>
        <VTextField v-model="form.file_types" label="Allowed File Types" variant="outlined" density="default" hint="e.g., pdf,doc,docx,zip" persistent-hint />
        <VTextField v-model="form.max_file_size" label="Max File Size (MB)" variant="outlined" density="default" type="number" min="1" />
        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Create Assignment</VBtn>
          <VBtn variant="outlined" :href="route('instructor.assignments.index', course.id)">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
