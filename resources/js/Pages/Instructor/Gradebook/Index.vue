<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  gradebookItems: { type: Array, default: () => [] },
  students: { type: Array, default: () => [] },
})

function updateGrade(studentId, itemId, value) {
  router.put(route('instructor.gradebook.update-grade', props.course.id), {
    student_id: studentId,
    gradebook_item_id: itemId,
    percentage: value,
  }, { preserveScroll: true })
}
</script>

<template>
  <div class="d-flex align-center justify-space-between mb-6">
    <div>
      <h4 class="text-h4 font-weight-bold mb-1">Gradebook</h4>
      <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>
    </div>
    <div>
      <VBtn
        color="secondary"
        variant="elevated"
        prepend-icon="mdi-Arrow-left"
        :href="route('instructor.courses.update', course.id)"
      >
        Back to Course
      </VBtn>
    </div>
  </div>
  <div>
    <VCard flat border class="overflow-auto">
      <VTable class="text-no-wrap">
        <thead>
          <tr>
            <th class="text-left font-weight-bold text-caption text-uppercase sticky-col bg-white">Student</th>
            <th v-for="item in gradebookItems" :key="item.id" class="text-center font-weight-bold text-caption text-uppercase" :title="`${item.title} (${item.weight}%)`">
              {{ item.title }}<br><span class="text-disabled font-weight-regular">({{ item.weight }}%)</span>
            </th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Total</th>
            <th class="text-center font-weight-bold text-caption text-uppercase">Grade</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.id" class="hover-bg">
            <td class="font-weight-medium sticky-col bg-white">{{ student.user?.name || student.name }}</td>
            <td v-for="item in gradebookItems" :key="item.id" class="text-center">
              <span v-if="student.item_grades?.[item.id]?.percentage != null" :class="student.item_grades[item.id].percentage >= 70 ? 'text-success' : 'text-error'" class="font-weight-medium">
                {{ student.item_grades[item.id].percentage }}%
              </span>
              <VTextField
                v-else
                density="default"
                variant="plain"
                type="number"
                min="0"
                max="100"
                hide-details
                placeholder="-"
                class="grade-input"
                @change="(e) => updateGrade(student.id, item.id, e.target.value)"
                style="max-width: 72px; margin: 0 auto;"
              />
            </td>
            <td class="text-center font-weight-bold">{{ student.final_percentage != null ? student.final_percentage + '%' : '-' }}</td>
            <td class="text-center">
              <span :class="student.final_percentage >= 70 ? 'text-success' : 'text-error'" class="font-weight-bold text-h6">
                {{ student.final_grade || '-' }}
              </span>
            </td>
          </tr>
          <tr v-if="!students.length">
            <td :colspan="gradebookItems.length + 3" class="text-center py-8 text-medium-emphasis">No students enrolled.</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <div class="mt-4 d-flex ga-2">
      <VBtn variant="tonal" color="primary" prepend-icon="mdi-download" :href="route('instructor.gradebook.export-csv', course.id)">Export CSV</VBtn>
    </div>
  </div>
</template>

<style scoped>
.sticky-col { position: sticky; left: 0; z-index: 1; }
.hover-bg:hover { background: rgb(var(--v-theme-surface-variant)); }
.grade-input :deep(input) { text-align: center; font-size: 0.875rem; }
</style>
