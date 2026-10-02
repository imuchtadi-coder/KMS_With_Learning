<script setup>
defineProps({
  course: { type: Object, required: true },
})

const totalLessons = (sections) => sections?.reduce((a, s) => a + (s.lessons?.length || 0), 0) || 0
const hasExams = (quizzes) => quizzes?.some(q => q.is_exam) || false
</script>

<template>
  <div>
    <VCard flat border class="pa-6 mb-6">
      <div class="d-flex justify-space-between align-start flex-wrap ga-4">
        <div>
          <h4 class="text-h4 font-weight-bold mb-2">{{ course.title }}</h4>
          <div class="d-flex align-center ga-3 flex-wrap">
            <VChip :color="course.published ? 'success' : 'warning'" size="small" variant="tonal" label>
              {{ course.published ? 'Published' : 'Draft' }}
            </VChip>
            <VChip v-if="course.level" color="info" size="small" variant="tonal" label>{{ course.level }}</VChip>
            <span class="text-body-2 text-medium-emphasis">{{ course.sections?.length || 0 }} sections &bull; {{ totalLessons(course.sections) }} lessons</span>
            <span class="text-body-2 text-medium-emphasis">{{ course.enrollments?.length || 0 }} enrolled students</span>
          </div>
        </div>
        <div class="d-flex ga-2">
          <VBtn color="primary" variant="elevated" size="small" :href="route('instructor.courses.edit', course.id)">Edit</VBtn>
          <VBtn variant="tonal" color="secondary" size="small" :href="route('instructor.courses.preview', course.id)">Preview</VBtn>
          <VBtn variant="tonal" color="secondary" size="small" :href="route('instructor.courses.progress', course.id)">Progress</VBtn>
        </div>
      </div>
    </VCard>

    <VRow>
      <VCol cols="12" lg="8">
        <VCard flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-3">Description</h5>
          <p class="text-body-1">{{ course.description || 'No description provided.' }}</p>
        </VCard>

        <VCard v-if="course.learning_outcomes" flat border class="pa-6 mb-6">
          <h5 class="text-h5 font-weight-bold mb-4">Learning Outcomes</h5>
          <VList class="pa-0">
            <VListItem v-for="(outcome, i) in course.learning_outcomes?.split('\n').filter(Boolean)" :key="i" class="px-0">
              <template #prepend><VIcon icon="mdi-check-circle" color="success" size="20" class="mr-2" /></template>
              <VListItemTitle>{{ outcome }}</VListItemTitle>
            </VListItem>
          </VList>
        </VCard>

        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-4">Course Content</h5>
          <div class="d-flex flex-column ga-3">
            <VCard v-for="section in (course.sections || [])" :key="section.id" variant="outlined">
              <div class="pa-3 bg-light-primary d-flex align-center border-b">
                <h6 class="text-h6 font-weight-bold">{{ section.title }}</h6>
                <VChip size="x-small" variant="tonal" class="ml-3">{{ section.lessons?.length || 0 }} lessons</VChip>
              </div>
              <VListItem v-for="lesson in (section.lessons || [])" :key="lesson.id" class="border-t" density="compact">
                <template #prepend><VIcon icon="mdi-play-box-outline" size="20" class="text-disabled mr-2" /></template>
                <VListItemTitle>{{ lesson.title }}</VListItemTitle>
              </VListItem>
            </VCard>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" lg="4">
        <VCard flat border class="pa-6 mb-6">
          <h6 class="text-h6 font-weight-semibold mb-3">Course Info</h6>
          <div class="d-flex flex-column ga-2 text-body-2">
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Status</span><span class="font-weight-semibold">{{ course.published ? 'Published' : 'Draft' }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Level</span><span class="font-weight-semibold">{{ course.level }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Sections</span><span class="font-weight-semibold">{{ course.sections?.length || 0 }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Lessons</span><span class="font-weight-semibold">{{ totalLessons(course.sections) }}</span></div>
            <div class="d-flex justify-space-between"><span class="text-medium-emphasis">Enrolled</span><span class="font-weight-semibold">{{ course.enrollments?.length || 0 }}</span></div>
          </div>
        </VCard>

        <VCard v-if="course.instructor" flat border class="pa-6 mb-6">
          <h6 class="text-h6 font-weight-semibold mb-3">Instructor</h6>
          <div class="d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" size="40">{{ course.instructor.name?.[0] }}</VAvatar>
            <div>
              <div class="font-weight-semibold">{{ course.instructor.name }}</div>
              <div class="text-caption text-medium-emphasis">{{ course.instructor.email }}</div>
            </div>
          </div>
        </VCard>

        <VCard flat border class="pa-6">
          <h6 class="text-h6 font-weight-semibold mb-3">Assessment</h6>
          <VList class="pa-0">
            <VListItem :href="route('instructor.questions.index', course.id)" class="assessment-link px-0" density="compact">
              <template #prepend><VIcon icon="mdi-help-circle-outline" size="20" class="mr-2" /></template>
              <VListItemTitle>Question Bank</VListItemTitle>
            </VListItem>
            <VListItem :href="route('instructor.quizzes.index', course.id)" class="assessment-link px-0" density="compact">
              <template #prepend><VIcon icon="mdi-order-bool-descending-variant" size="20" class="mr-2" /></template>
              <VListItemTitle>Quizzes</VListItemTitle>
            </VListItem>
            <VListItem :href="route('instructor.assignments.index', course.id)" class="assessment-link px-0" density="compact">
              <template #prepend><VIcon icon="mdi-file-document-outline" size="20" class="mr-2" /></template>
              <VListItemTitle>Assignments</VListItemTitle>
            </VListItem>
            <VListItem :href="route('instructor.gradebook.index', course.id)" class="assessment-link px-0" density="compact">
              <template #prepend><VIcon icon="mdi-chart-box-outline" size="20" class="mr-2" /></template>
              <VListItemTitle>Gradebook</VListItemTitle>
            </VListItem>
            <VListItem :href="route('discussions.index', { course_id: course.id })" class="assessment-link px-0" density="compact">
              <template #prepend><VIcon icon="mdi-forum-outline" size="20" class="mr-2" /></template>
              <VListItemTitle>Discussions</VListItemTitle>
            </VListItem>
            <VListItem :href="route('instructor.live-classes.index', course.id)" class="assessment-link px-0" density="compact">
              <template #prepend><VIcon icon="mdi-video-outline" size="20" class="mr-2" /></template>
              <VListItemTitle>Live Classes</VListItemTitle>
            </VListItem>
            <VListItem :href="route('instructor.assessment-analytics.course', course.id)" class="assessment-link px-0 border-t mt-2 pt-2" density="compact">
              <template #prepend><VIcon icon="mdi-chart-bar" size="20" class="mr-2" /></template>
              <VListItemTitle>Assessment Analytics</VListItemTitle>
            </VListItem>
            <template v-if="hasExams(course.quizzes)">
              <VListSubheader class="px-0 text-caption font-weight-bold pb-0">Exam Monitoring</VListSubheader>
              <VListItem
                v-for="exam in (course.quizzes || []).filter(q => q.is_exam)"
                :key="exam.id"
                :href="route('instructor.exams.monitor', [course.id, exam.id])"
                class="assessment-link px-0 ml-3"
                density="compact"
              >
                <template #prepend><VIcon icon="mdi-monitor-eye" size="20" class="mr-2" /></template>
                <VListItemTitle>{{ exam.title }}</VListItemTitle>
              </VListItem>
            </template>
          </VList>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
  .assessment-link {
  cursor: pointer;
  transition:
    background-color 0.15s ease,
    color 0.15s ease;
  }

  .assessment-link:hover {
    background-color: rgba(var(--v-theme-primary), 0.06);
  }

  .assessment-link:hover .v-list-item-title {
    color: rgb(var(--v-theme-primary));
  }
</style>