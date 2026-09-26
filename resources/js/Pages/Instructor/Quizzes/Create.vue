<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  questions: { type: Array, default: () => [] },
})

const form = useForm({
  title: '',
  description: '',
  section_id: '',
  passing_grade: '70',
  time_limit: '',
  max_attempts: '1',
  shuffle_questions: false,
  show_result: true,
  is_exam: false,
  start_date: '',
  end_date: '',
  grace_period: '0',
  randomize_options: false,
  prevent_copy_paste: false,
  require_webcam: false,
  question_ids: [],
})

const isExam = ref(false)

function toggleExam(v) {
  isExam.value = v
  form.is_exam = v

  if (!v) {
    form.start_date = ''
    form.end_date = ''
    form.grace_period = '0'
    form.randomize_options = false
    form.prevent_copy_paste = false
    form.require_webcam = false
  }
}

function toggleQuestion(id) {
  const idx = form.question_ids.indexOf(id)
  if (idx >= 0) form.question_ids.splice(idx, 1)
  else form.question_ids.push(id)
}

function submit() {
  console.log('CREATE QUIZ: SUBMIT CLICKED')
  console.log('CREATE QUIZ PAYLOAD:', form.data())

  form.post(
    route('instructor.quizzes.store', props.course.id),
    {
      onStart: () => {
        console.log('CREATE QUIZ: REQUEST STARTED')
      },
      onSuccess: (page) => {
        console.log('CREATE QUIZ: SUCCESS')
        console.log('REDIRECT URL:', page.url)
      },
      onError: (errors) => {
        console.error('CREATE QUIZ: VALIDATION ERRORS:', errors)
      },
      onFinish: () => {
        console.log('CREATE QUIZ: REQUEST FINISHED')
      },
    }
  )
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Create New Quiz</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="720">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Quiz Title" variant="outlined" density="default" :rules="[v => !!v || 'Title is required']" hide-details="auto" />
        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="3" hide-details="auto" />

        <VSelect v-model="form.section_id" label="Section" variant="outlined" density="default" :items="(course.sections || []).map(s => ({ title: s.title, value: s.id }))" :rules="[v => !!v || 'Section is required']" hide-details="auto" />

        <VRow>
          <VCol cols="4"><VTextField v-model="form.passing_grade" label="Passing Grade (%)" variant="outlined" density="default" type="number" min="0" max="100" /></VCol>
          <VCol cols="4"><VTextField v-model="form.time_limit" label="Time Limit (minutes)" variant="outlined" density="default" type="number" min="1" hint="Leave empty for no limit" persistent-hint /></VCol>
          <VCol cols="4"><VTextField v-model="form.max_attempts" label="Max Attempts" variant="outlined" density="default" type="number" min="1" max="10" /></VCol>
        </VRow>

        <div class="d-flex ga-4">
          <VCheckbox v-model="form.shuffle_questions" label="Shuffle Questions" hide-details density="default" />
          <VCheckbox v-model="form.show_result" label="Show Result Immediately" hide-details density="default" />
          <VCheckbox :model-value="isExam" @update:model-value="toggleExam" label="Scheduled Exam" hide-details density="default" color="error" />
        </div>

        <VCard v-if="isExam" color="error" variant="tonal" class="pa-4">
          <h6 class="text-h6 font-weight-bold mb-3 text-error">Exam Settings</h6>
          <VRow>
            <VCol cols="6"><VTextField v-model="form.start_date" label="Start Date" variant="outlined" density="default" type="datetime-local" /></VCol>
            <VCol cols="6"><VTextField v-model="form.end_date" label="End Date" variant="outlined" density="default" type="datetime-local" /></VCol>
          </VRow>
          <VTextField v-model="form.grace_period" label="Entry Grace Period (minutes)" variant="outlined" density="default" type="number" min="0" hint="Additional time allowed to start the exam after the start time" persistent-hint/>
          <div class="d-flex ga-4 mt-2">
            <VCheckbox v-model="form.randomize_options" label="Randomize Options" hide-details density="default" />
            <VCheckbox v-model="form.prevent_copy_paste" label="Prevent Copy & Paste" hide-details density="default" />
            <!-- <VCheckbox v-model="form.require_webcam" label="Require Webcam" hide-details density="default" /> -->
          </div>
        </VCard>

        <div>
          <label class="text-body-2 font-weight-bold d-block mb-2">Select Questions</label>
          <VCard v-if="questions.length" variant="outlined" class="overflow-y-auto" style="max-height: 320px;">
            <VListItem v-for="q in questions" :key="q.id" class="border-t" @click="toggleQuestion(q.id)">
              <template #prepend>
                <VCheckbox :model-value="form.question_ids.includes(q.id)" hide-details density="default" @click.stop ="toggleQuestion(q.id)" />
              </template>
              <VListItemTitle>{{ q.question_text }}</VListItemTitle>
              <template #append>
                <div class="d-flex ga-1">
                  <VChip size="x-small" variant="tonal" :color="{ multiple_choice: 'primary', true_false: 'success', essay: 'warning' }[q.question_type] || 'secondary'" label>{{ q.question_type?.replace(/_/g, ' ') }}</VChip>
                  <VChip size="x-small" variant="tonal" label>{{ q.points }} pts</VChip>
                </div>
              </template>
            </VListItem>
          </VCard>
          <VAlert v-else color="warning" variant="tonal" density="compact">
            No questions available. <a :href="route('instructor.questions.create', course.id)" class="text-link font-weight-semibold">Create questions first</a>.
          </VAlert>
        </div>

        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Create Quiz</VBtn>
          <VBtn variant="outlined" :href="route('instructor.quizzes.index', course.id)">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
