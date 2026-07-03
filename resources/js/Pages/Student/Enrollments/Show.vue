<script setup>
import { computed } from 'vue'

const props = defineProps({
  enrollment: { type: Object, required: true },
  totalLessons: { type: Number, default: 0 },
  completedLessons: { type: Number, default: 0 },
  courseProgress: { type: Number, default: 0 },
  courseQuizzes: { type: Array, default: () => [] },
  courseAssignments: { type: Array, default: () => [] },
})

const course = computed(() => props.enrollment.course || {})
const sections = computed(() => course.value.sections || [])
const instructor = computed(() => course.value.instructor || {})
</script>

<template>
  <VContainer fluid class="bg-grey-lighten-4 min-h-screen py-6">
    <!-- Course Header -->
    <div class="mb-6">
      <h2 class="text-h4 font-weight-bold mb-1">{{ course.title }}</h2>
      <div class="d-flex align-center ga-4">
        <span class="text-body-2 text-medium-emphasis">by <strong>{{ instructor.name || 'Unknown' }}</strong></span>
        <VChip color="primary" size="small" variant="flat">{{ course.level?.charAt(0).toUpperCase() + course.level?.slice(1) }}</VChip>
      </div>
    </div>

    <!-- Progress Overview -->
    <VCard flat border class="mb-6 pa-4">
      <VRow>
        <VCol cols="12" md="3">
          <h6 class="text-body-2 font-weight-bold mb-2">Overall Progress</h6>
          <p class="text-h3 font-weight-bold text-primary mb-2">{{ enrollment.progress || 0 }}%</p>
          <VProgressLinear :model-value="enrollment.progress || 0" color="primary" height="8" rounded />
        </VCol>
        <VCol cols="12" md="3">
          <h6 class="text-body-2 font-weight-bold mb-2">Course Stats</h6>
          <div class="d-flex flex-column ga-1 text-body-2">
            <div class="d-flex justify-between"><span class="text-medium-emphasis">Total Lessons:</span><span class="font-weight-bold">{{ totalLessons }}</span></div>
            <div class="d-flex justify-between"><span class="text-medium-emphasis">Completed:</span><span class="font-weight-bold">{{ completedLessons }}</span></div>
          </div>
        </VCol>
        <VCol cols="12" md="3">
          <h6 class="text-body-2 font-weight-bold mb-2">Status</h6>
          <div v-if="enrollment.completed_at" class="d-flex align-center text-success ga-2">
            <VIcon icon="mdi-check-circle" size="20" />
            <span class="font-weight-bold">Completed</span>
            <span class="text-caption text-medium-emphasis">{{ new Date(enrollment.completed_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) }}</span>
          </div>
          <div v-else class="d-flex align-center text-primary ga-2">
            <VIcon icon="mdi-loading" size="20" class="spin" />
            <span class="font-weight-bold">In Progress</span>
          </div>
        </VCol>
      </VRow>
    </VCard>

    <VRow>
      <!-- Sections & Lessons Sidebar -->
      <VCol cols="12" lg="3">
        <VCard flat border class="sticky-top">
          <VCardTitle>Course Content</VCardTitle>
          <VDivider />
          <div class="overflow-y-auto" style="max-height: 24rem;">
            <template v-for="section in sections" :key="section.id">
              <div class="pa-3 bg-grey-lighten-3 font-weight-bold text-body-2 border-bottom">{{ section.title }}</div>
              <div class="d-flex flex-column">
                <a v-for="lesson in section.lessons || []" :key="lesson.id"
                  :href="route('student.lessons.view', { course: course.id, lesson: lesson.id })"
                  class="d-flex align-center ga-2 pa-2 text-body-2 text-decoration-none"
                  :class="$page.url?.includes('/lessons/' + lesson.id) ? 'bg-primary-lighten-5 text-primary font-weight-bold border-left-thick border-primary' : 'text-medium-emphasis'">
                  <VIcon icon="mdi-play-box-outline" size="16" />
                  <span class="line-clamp-2">{{ lesson.title }}</span>
                </a>
              </div>
            </template>
          </div>
          <VDivider />
          <VCardText>
            <h6 class="text-body-2 font-weight-bold mb-2">Assessments</h6>
            <template v-if="courseQuizzes.length">
              <a v-for="quiz in courseQuizzes" :key="quiz.id"
                :href="route('student.quizzes.show', quiz.id)"
                class="d-flex align-center ga-2 pa-2 text-body-2 text-decoration-none text-medium-emphasis rounded">
                <VIcon icon="mdi-file-document-outline" size="16" />
                <span>{{ quiz.title }}</span>
              </a>
            </template>
            <template v-if="courseAssignments.length">
              <a v-for="assignment in courseAssignments" :key="assignment.id"
                :href="route('student.assignments.show', assignment.id)"
                class="d-flex align-center ga-2 pa-2 text-body-2 text-decoration-none text-medium-emphasis rounded">
                <VIcon icon="mdi-clipboard-text-outline" size="16" />
                <span>{{ assignment.title }}</span>
              </a>
            </template>
            <p v-if="!courseQuizzes.length && !courseAssignments.length" class="text-caption text-medium-emphasis">No assessments available yet.</p>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Main Content Area -->
      <VCol cols="12" lg="9">
        <VCard flat border>
          <div class="bg-grey-darken-4 d-flex align-center justify-center" style="aspect-ratio: 16/9;">
            <div class="text-center text-white">
              <VIcon icon="mdi-play-circle-outline" size="64" color="disabled" />
              <p class="text-body-1 text-medium-emphasis mt-2">Video Player</p>
              <p class="text-caption text-medium-emphasis">Video content will be displayed here</p>
            </div>
          </div>
          <VCardText>
            <h4 class="text-h4 font-weight-bold mb-2">Lesson Overview</h4>
            <p class="text-body-1 text-medium-emphasis mb-6">Select a lesson from the sidebar to view its content</p>

            <VAlert type="info" variant="tonal" class="mb-6">
              <template #title>What You'll Learn</template>
              <ul class="mt-1">
                <li>Key concepts and fundamental knowledge</li>
                <li>Practical application of the concepts</li>
                <li>Best practices and industry standards</li>
                <li>Hands-on exercises and examples</li>
              </ul>
            </VAlert>

            <VCard flat border class="pa-4 mb-6 bg-grey-lighten-4">
              <h6 class="text-body-2 font-weight-bold mb-3">Resources</h6>
              <div class="d-flex flex-column ga-2">
                <a href="#" class="text-primary text-decoration-none font-weight-medium"><VIcon icon="mdi-file-pdf-box" size="16" class="me-1" />Lesson Notes (PDF)</a>
                <a href="#" class="text-primary text-decoration-none font-weight-medium"><VIcon icon="mdi-zip-box" size="16" class="me-1" />Exercise Files (ZIP)</a>
              </div>
            </VCard>

            <VBtn v-if="!enrollment.completed_at" color="success" variant="elevated" prepend-icon="mdi-check" class="mb-6">Mark as Complete</VBtn>

            <VDivider class="mb-4" />
            <div class="d-flex ga-3">
              <VBtn variant="tonal" disabled prepend-icon="mdi-arrow-left">Previous Lesson</VBtn>
              <VBtn color="primary" variant="elevated" append-icon="mdi-arrow-right">Next Lesson</VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.sticky-top { position: sticky; top: 80px; }
.bg-grey-lighten-4 { background-color: rgb(var(--v-theme-grey-lighten-4)) !important; }
.min-h-screen { min-height: 100vh; }
.border-left-thick { border-left: 4px solid; }
.border-bottom { border-bottom: 1px solid rgba(0,0,0,0.12); }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.spin { animation: spin 1.5s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
