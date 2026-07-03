<script setup>
defineProps({
  totalCourses: { type: Number, default: 0 },
  publishedCourses: { type: Number, default: 0 },
  totalEnrollments: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  totalInstructors: { type: Number, default: 0 },
  totalQuizzes: { type: Number, default: 0 },
  totalAssignments: { type: Number, default: 0 },
  courseAnalytics: { type: Array, default: () => [] },
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Assessment Analytics</h4>
      <p class="text-body-2 text-medium-emphasis">Overview of assessment performance across all courses</p>
    </div>

    <VRow class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" rounded>
              <VIcon icon="mdi-book-open-variant" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalCourses }}</div>
              <div class="text-caption text-medium-emphasis">Total Courses</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="success" variant="tonal" rounded>
              <VIcon icon="mdi-check-circle" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ publishedCourses }}</div>
              <div class="text-caption text-medium-emphasis">Published</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="info" variant="tonal" rounded>
              <VIcon icon="mdi-account-group" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalEnrollments }}</div>
              <div class="text-caption text-medium-emphasis">Enrollments</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" md="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="warning" variant="tonal" rounded>
              <VIcon icon="mdi-help-box" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalQuizzes + totalAssignments }}</div>
              <div class="text-caption text-medium-emphasis">Assessments</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard flat border title="Per-Course Performance">
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Course</th>
            <th>Instructor</th>
            <th>Enrollments</th>
            <th>Avg Score</th>
            <th>Pass Rate</th>
            <th>Quizzes</th>
            <th>Assignments</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in courseAnalytics" :key="course.id">
            <td class="font-weight-medium">{{ course.title }}</td>
            <td class="text-caption">{{ course.instructor }}</td>
            <td>{{ course.enrollments }}</td>
            <td>
              <VChip v-if="course.avg_score !== null" size="x-small" :color="course.avg_score >= 70 ? 'success' : 'error'" variant="tonal">
                {{ course.avg_score }}%
              </VChip>
              <span v-else class="text-disabled">—</span>
            </td>
            <td>
              <VChip v-if="course.pass_rate !== null" size="x-small" :color="course.pass_rate >= 70 ? 'success' : 'warning'" variant="tonal">
                {{ course.pass_rate }}%
              </VChip>
              <span v-else class="text-disabled">—</span>
            </td>
            <td>{{ course.quiz_count }}</td>
            <td>{{ course.assignment_count }}</td>
          </tr>
          <tr v-if="!courseAnalytics.length">
            <td colspan="7" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-chart-bar-off" size="48" color="disabled" class="mb-2" />
              <div>No course data available</div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
