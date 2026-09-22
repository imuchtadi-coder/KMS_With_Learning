<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  course: { type: Object, required: true },
  // questions: { type: Array, default: () => [] },
  questions: {type: Object, default: () => ({ data:[],}),
},
})

function destroy(question) {
  if (!confirm('Delete this question?')) return
  router.delete(route('instructor.questions.destroy', [question.course_id, question.id]), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Question Bank</h4>
        <p class="text-body-1 text-medium-emphasis">{{ course.title }}</p>
      </div>
      <div class="d-flex align-center ga-2">
        <VBtn
          color="secondary"
          variant="tonal"
          prepend-icon="mdi-arrow-left"
          :href="route('instructor.courses.update', course.id)"
        >
          Back to Course
        </VBtn>

        <VBtn
          color="primary"
          variant="elevated"
          prepend-icon="mdi-plus"
          :href="route('instructor.questions.create', course.id)"
        >
          Add Question
        </VBtn>
      </div>
    </div>

    <!-- <VCard v-if="questions.length" flat border> -->
    <VCard v-if="questions.data?.length" flat border>
      <VDataTable
        :items="questions.data"
        :headers="[ 
          { title: 'Question', key: 'question_text', }, 
          { title: 'Type', key: 'question_type', }, 
          { title: 'Actions', key: 'actions', sortable: false, }, 
        ]" 
        disable-sort
        hide-default-footer
        class="text-no-wrap"
      >
        <template #item.question_text="{ value }">
          <span class="text-truncate d-inline-block" style="max-width: 400px;">{{ value }}</span>
        </template>
        <template #item.question_type="{ value }">
          <VChip :color="{ multiple_choice: 'primary', true_false: 'success', essay: 'warning' }[value] || 'secondary'" size="x-small" variant="tonal" label>
            {{ value?.replace(/_/g, ' ') }}
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <div class="d-flex ga-1">
            <VBtn variant="text" color="primary" size="small" :href="route('instructor.questions.edit', [item.course_id, item.id])">Edit</VBtn>
            <VBtn variant="text" color="error" size="small" @click="destroy(item)">Delete</VBtn>
          </div>
        </template>
      </VDataTable>
    </VCard>
    <VCard v-else flat border class="pa-12 text-center">
      <VIcon icon="mdi-help-circle-outline" size="64" color="disabled" />
      <h5 class="text-h5 mt-4 mb-2">No questions yet</h5>
      <p class="text-body-2 text-medium-emphasis mb-4">Create questions for quizzes and exams</p>
      <VBtn color="primary" variant="elevated" :href="route('instructor.questions.create', course.id)">Add Question</VBtn>
    </VCard>
  </div>
</template>
