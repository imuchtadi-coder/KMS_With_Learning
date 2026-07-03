<script setup>
defineProps({
  course: { type: Object, required: true },
  quizzes: { type: Object, required: true },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Quizzes</h4>
        <p class="text-body-1 text-medium-emphasis">{{ course.title }}</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('instructor.quizzes.create', course.id)">New Quiz</VBtn>
    </div>

    <VRow>
      <VCol v-for="quiz in (quizzes.data || quizzes)" :key="quiz.id" cols="12" md="6" lg="4">
        <VCard flat border hover>
          <VCardText>
            <div class="d-flex align-center justify-space-between mb-3">
              <VChip :color="quiz.published ? 'success' : 'warning'" size="x-small" variant="tonal" label>{{ quiz.published ? 'Published' : 'Draft' }}</VChip>
              <span class="text-caption text-medium-emphasis">{{ quiz.section?.title || 'No section' }}</span>
            </div>
            <h6 class="text-h6 font-weight-bold mb-2">{{ quiz.title }}</h6>
            <p class="text-body-2 text-medium-emphasis text-truncate mb-4">{{ quiz.description }}</p>
            <div class="d-flex ga-3 text-caption text-medium-emphasis mb-4">
              <span>{{ quiz.questions?.length || 0 }} questions</span>
              <span>{{ quiz.time_limit ? quiz.time_limit + ' min' : 'No limit' }}</span>
              <span>Pass: {{ quiz.passing_grade }}%</span>
            </div>
            <div class="d-flex ga-2">
              <VBtn variant="tonal" color="primary" size="small" block :href="route('instructor.quizzes.show', [course.id, quiz.id])">Detail</VBtn>
              <VBtn variant="outlined" color="secondary" size="small" block :href="route('instructor.quizzes.edit', [course.id, quiz.id])">Edit</VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard v-if="!(quizzes.data || quizzes).length" flat border class="pa-12 text-center mt-4">
      <VIcon icon="mdi-order-bool-descending-variant" size="64" color="disabled" />
      <h5 class="text-h5 mt-4 mb-2">No quizzes yet</h5>
      <p class="text-body-2 text-medium-emphasis mb-4">Create a quiz to assess student understanding</p>
      <VBtn color="primary" variant="elevated" :href="route('instructor.quizzes.create', course.id)">Create Your First Quiz</VBtn>
    </VCard>
  </div>
</template>
