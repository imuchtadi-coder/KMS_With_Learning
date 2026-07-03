<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  question: { type: Object, required: true },
})

const form = useForm({
  question_type: props.question.question_type,
  question_text: props.question.question_text,
  points: props.question.points,
  difficulty_level: props.question.difficulty_level || 'medium',
  correct_answer: props.question.correct_answer || '',
  options: props.question.options || [],
})

function submit() {
  form.put(route('instructor.questions.update', [props.course.id, props.question.id]), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Edit Question</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="720">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <VSelect v-model="form.question_type" label="Question Type" variant="outlined" density="default"
          :items="[
            { title: 'Multiple Choice', value: 'multiple_choice' },
            { title: 'True/False', value: 'true_false' },
            { title: 'Essay', value: 'essay' },
          ]" />

        <VTextarea v-model="form.question_text" label="Question Text" variant="outlined" density="default" rows="4" />

        <VTextField v-model="form.points" label="Points" variant="outlined" density="default" type="number" min="1" />

        <VSelect v-model="form.difficulty_level" label="Difficulty Level" variant="outlined" density="default"
          :items="['easy', 'medium', 'hard'].map(v => ({ title: v, value: v }))" />

        <div v-if="form.question_type === 'multiple_choice'">
          <label class="text-body-2 font-weight-bold d-block mb-2">Options</label>
          <div v-for="(opt, i) in form.options" :key="i" class="d-flex align-center ga-2 mb-2">
            <VRadio :model-value="form.correct_answer === opt" @update:model-value="form.correct_answer = opt" hide-details />
            <VTextField v-model="form.options[i]" variant="outlined" density="default" hide-details placeholder="Option" class="flex-grow-1" />
          </div>
        </div>
        <VTextField v-else-if="form.question_type === 'true_false'" v-model="form.correct_answer" label="Correct Answer" variant="outlined" density="default"
          :items="['True', 'False']" />

        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Update Question</VBtn>
          <VBtn variant="outlined" :href="route('instructor.questions.index', course.id)">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
