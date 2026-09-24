<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  quiz: { type: Object, required: true },
  questions: { type: Array, default: () => [] },
})

const form = useForm({
  title: props.quiz.title,
  description: props.quiz.description || '',
  section_id: props.quiz.section_id,
  passing_grade: props.quiz.passing_grade,
  time_limit: props.quiz.time_limit,
  max_attempts: props.quiz.max_attempts,
  shuffle_questions: props.quiz.shuffle_questions || false,
  show_result: props.quiz.show_result ?? true,
  is_exam: props.quiz.is_exam || false,
  start_date: props.quiz.start_date || '',
  end_date: props.quiz.end_date || '',
  grace_period: props.quiz.grace_period || '0',
  randomize_options: props.quiz.randomize_options || false,
  prevent_copy_paste: props.quiz.prevent_copy_paste || false,
  require_webcam: props.quiz.require_webcam || false,
  question_ids: props.quiz.questions?.map(q => q.id) || [],
})

const selectedIds = ref([...form.question_ids])

function toggleQuestion(id) {
  const idx = selectedIds.value.indexOf(id)

  if (idx >= 0) {
    selectedIds.value.splice(idx, 1)
  } else {
    selectedIds.value.push(id)
  }

  form.question_ids = [...selectedIds.value]

  console.log('SELECTED QUESTION IDS:', form.question_ids)
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Edit Quiz</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }} &mdash; {{ quiz.title }}</p>

    <VCard flat border class="pa-8" max-width="720">
      <VForm @submit.prevent="form.put(route('instructor.quizzes.update', [course.id, quiz.id]))" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Quiz Title" variant="outlined" density="default" />
        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="3" />
        <VSelect v-model="form.section_id" label="Section" variant="outlined" density="default" :items="(course.sections || []).map(s => ({ title: s.title, value: s.id }))" />
        <VRow>
          <VCol cols="4"><VTextField v-model="form.passing_grade" label="Passing Grade (%)" variant="outlined" density="default" type="number" min="0" max="100" /></VCol>
          <VCol cols="4"><VTextField v-model="form.time_limit" label="Time Limit (min)" variant="outlined" density="default" type="number" min="1" hint="Empty = no limit" persistent-hint /></VCol>
          <VCol cols="4"><VTextField v-model="form.max_attempts" label="Max Attempts" variant="outlined" density="default" type="number" min="1" max="10" /></VCol>
        </VRow>
        <div class="d-flex ga-4">
          <VCheckbox v-model="form.shuffle_questions" label="Shuffle Questions" hide-details density="default" />
          <VCheckbox v-model="form.show_result" label="Show Result Immediately" hide-details density="default" />
          <VCheckbox v-model="form.is_exam" label="Scheduled Exam" hide-details density="default" color="error" />
        </div>

        <VCard v-if="form.is_exam" color="error" variant="tonal" class="pa-5">
          <h6 class="text-h6 font-weight-bold mb-3 text-error">Exam Settings</h6>
          <VRow class="ga-4">
            <VCol cols="10" md="5"><VTextField v-model="form.start_date" label="Start Date" variant="outlined" density="default" type="datetime-local" /></VCol>
            <VCol cols="10" md="5"><VTextField v-model="form.end_date" label="End Date" variant="outlined" density="default" type="datetime-local" /></VCol>
          </VRow>
          <VTextField v-model="form.grace_period" label="Grace Period (min)" variant="outlined" density="default" type="number" min="0" hint="Additional time allowed for entering the exam" persistent-hint class="mt-5" />
          <!-- <div class="d-flex ga-4 mt-2">
            <VCheckbox v-model="form.randomize_options" label="Randomize Options" hide-details density="default" />
            <VCheckbox v-model="form.prevent_copy_paste" label="Prevent Copy & Paste" hide-details density="default" />
            <VCheckbox v-model="form.require_webcam" label="Require Webcam" hide-details density="default" />
          </div> -->
        </VCard>

        <div>
          <label class="text-body-2 font-weight-bold d-block mb-2">Select Questions</label>
          <VCard v-if="questions.length" variant="outlined" class="overflow-y-auto" style="max-height: 320px;">
            <VListItem v-for="q in questions" :key="q.id" class="border-t" @click="toggleQuestion(q.id)">
              <template #prepend><VCheckbox :model-value="selectedIds.includes(q.id)" hide-details density="default" @click.stop="toggleQuestion(q.id)" /></template>
              <VListItemTitle>{{ q.question_text }}</VListItemTitle>
              <template #append>
                <div class="d-flex ga-1">
                  <VChip size="x-small" variant="tonal" :color="{ multiple_choice: 'primary', true_false: 'success', essay: 'warning' }[q.question_type] || 'secondary'" label>{{ q.question_type?.replace(/_/g, ' ') }}</VChip>
                  <VChip size="x-small" variant="tonal" label>{{ q.points }} pts</VChip>
                </div>
              </template>
            </VListItem>
          </VCard>
          <VAlert v-else color="warning" variant="tonal" density="compact">No questions available.</VAlert>
        </div>

        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Update Quiz</VBtn>
          <VBtn variant="outlined" :href="route('instructor.quizzes.show', [course.id, quiz.id])">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
