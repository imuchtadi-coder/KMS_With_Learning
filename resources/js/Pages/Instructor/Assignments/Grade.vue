<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  course: { type: Object, required: true },
  assignment: { type: Object, required: true },
  submission: { type: Object, required: true },
})

const form = useForm({
  grade: '',
  grader_notes: '',
})

const showPreview = ref(false) 

const downloadUrl = computed(() => route('instructor.assignments.submissions.download', [ 
    props.course.id, 
    props.assignment.id, 
    props.submission.id, 
  ]) 
)

const previewUrl = computed(() =>
  route('instructor.assignments.submissions.preview', [
    props.course.id,
    props.assignment.id,
    props.submission.id,
  ])
)

const isPdf = computed(() => { 
    const filePath = props.submission.file_path || '' 
    const fileName = props.submission.file_name || '' 
    return ( filePath.toLowerCase().endsWith('.pdf') || fileName.toLowerCase().endsWith('.pdf') 
  ) 
})

const togglePreview = () => { 
  showPreview.value = !showPreview.value
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-2">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Grade Submission</h4>
        <p class="text-body-1 text-medium-emphasis mb-2">{{ assignment.title }}</p>
      </div>
      <VBtn
          variant="tonal"
          color="primary"
          prepend-icon="mdi-arrow-left"
          :href="route('instructor.assignments.update', [
            course.id,
            assignment.id,
          ])"
        >
          Back
        </VBtn>
      </div>
    <p class="text-body-2 text-medium-emphasis mb-6">Student: {{ submission.user?.name }} &mdash; Submitted: {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '-' }}</p>

    <VRow>
      <VCol cols="12" lg="6">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-3">Submission Details</h5>
          <div class="d-flex flex-column ga-3 text-body-2">
            <div><span class="text-medium-emphasis">Student:</span> <span class="font-weight-medium">{{ submission.user?.name }}</span></div>
            <div><span class="text-medium-emphasis">Email:</span> {{ submission.user?.email }}</div>
            <div><span class="text-medium-emphasis">Submitted:</span> {{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleString() : '-' }}</div>
            <div v-if="submission.notes"><span class="text-medium-emphasis">Notes:</span> {{ submission.notes }}</div>
            <div class="d-flex flex-wrap ga-2 mt-2">
              <VBtn variant="tonal" color="primary" size="small" :href="route('instructor.assignments.submissions.download', [course.id, assignment.id, submission.id])" prepend-icon="mdi-download">
                Download Submission
              </VBtn>
              <VBtn v-if="isPdf" variant="tonal" color="secondary" size="small" :prepend-icon="showPreview ? 'mdi-eye-off' : 'mdi-file-eye'" @click="togglePreview" > 
                {{ showPreview ? 'Hide Preview' : 'Preview PDF' }} 
              </VBtn>
            </div>
          </div>
        </VCard>
        <VCard v-if="showPreview && isPdf" flat border class="mb-6" > 
          <VCardTitle class="d-flex align-center"> 
            <VIcon icon="mdi-file-pdf-box" color="error" class="me-2" /> PDF Preview 
            <VSpacer /> 
            <VBtn icon="mdi-close" variant="text" size="small" @click="showPreview = false" /> 
          </VCardTitle> 
          <VDivider /> 
          <div class="pdf-preview"> 
            <iframe :src="previewUrl" title="PDF Submission Preview" class="pdf-frame" /> 
          </div> 
        </VCard>
      </VCol>

      <VCol cols="12" lg="6">
        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-3">Grade</h5>
          <VForm @submit.prevent="form.post(route('instructor.assignments.submissions.grade.store', [course.id, assignment.id, submission.id]))" class="d-flex flex-column ga-4">
            <VTextField v-model="form.grade" :label="`Grade (${assignment.max_score} max)`" variant="outlined" density="default" type="number" :min="0" :max="assignment.max_score" :rules="[v => v !== '' || 'Grade is required']" hide-details="auto" />
            <VTextarea v-model="form.grader_notes" label="Feedback (Optional)" variant="outlined" density="default" rows="4" hide-details="auto" placeholder="Provide feedback to the student..." />
            <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Submit Grade</VBtn>
          </VForm>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped> 
.pdf-preview { width: 100%; height: 700px; background: #f5f5f5; } 
.pdf-frame { width: 100%; height: 100%; border: 0; display: block; } 
</style>