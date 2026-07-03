<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
})

const questionType = ref('multiple_choice')

const form = useForm({
  type: 'multiple_choice',
  question_text: '',
  points: 1,
  options: [{ text: '', is_correct: false }, { text: '', is_correct: false }],
})

function addOption() {
  form.options.push({ text: '', is_correct: false })
}

function removeOption(index) {
  if (form.options.length > 2) form.options.splice(index, 1)
}

function submit() {
  form.post(route('instructor.questions.store', props.course.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Create Question</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="720">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <VSelect v-model="form.type" label="Question Type" variant="outlined" density="default"
          :items="[
            { title: 'Multiple Choice', value: 'multiple_choice' },
            { title: 'True/False', value: 'true_false' },
            { title: 'Essay', value: 'essay' },
          ]"
          @update:model-value="(v) => {
            questionType = v
            if (v === 'true_false') form.options = [{ text: 'True', is_correct: false }, { text: 'False', is_correct: false }]
            if (v === 'essay') form.options = []
            if (v === 'multiple_choice' && form.options.length < 2) form.options = [{ text: '', is_correct: false }, { text: '', is_correct: false }]
          }"
        />

        <VTextarea v-model="form.question_text" label="Question Text" variant="outlined" density="default" rows="4"
          :rules="[v => !!v || 'Question text is required']" hide-details="auto" />

        <VTextField v-model="form.points" label="Points" variant="outlined" density="default" type="number" min="1" />

        <div v-if="form.type !== 'essay'">
          <div class="d-flex align-center justify-space-between mb-3">
            <label class="text-body-2 font-weight-bold">Answer Options</label>
            <VBtn v-if="form.type === 'multiple_choice'" size="x-small" variant="tonal" color="primary" @click="addOption">+ Add Option</VBtn>
          </div>
          <div v-for="(option, i) in form.options" :key="i" class="d-flex align-center ga-3 mb-2">
            <VRadio v-if="form.type === 'multiple_choice'" :model-value="option.is_correct" @update:model-value="(v) => form.options.forEach((o, j) => o.is_correct = j === i && v)" hide-details />
            <VRadioGroup v-if="form.type === 'true_false'" v-model="form.options" hide-details class="flex-row">
              <VRadio v-for="(opt, j) in form.options" :key="j" :label="opt.text" :value="j" @update:model-value="form.options.forEach((o, k) => o.is_correct = k === j)" />
            </VRadioGroup>
            <VTextField v-if="form.type === 'multiple_choice'" v-model="option.text" variant="outlined" density="default" hide-details placeholder="Option text" class="flex-grow-1" />
            <VBtn v-if="form.type === 'multiple_choice' && form.options.length > 2" variant="text" color="error" size="x-small" icon @click="removeOption(i)"><VIcon icon="mdi-close" /></VBtn>
          </div>
          <div v-if="!form.options.some(o => o.is_correct) && form.type !== 'true_false'" class="text-caption text-error mt-1">Select a correct answer</div>
        </div>

        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Create Question</VBtn>
          <VBtn variant="outlined" :href="route('instructor.questions.index', course.id)">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
