<script setup>
defineProps({
  totalCourses: { type: Number, default: 0 },
  publishedCourses: { type: Number, default: 0 },
  draftCourses: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  recentCourses: { type: Array, default: () => [] },
})
</script>

<template>
  <div>
    <div class="mb-8">
      <h4 class="text-h4 font-weight-bold mb-1">Welcome!</h4>
      <p class="text-body-1 text-medium-emphasis">Create and manage your courses</p>
    </div>

    <VRow class="mb-6">
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" rounded size="48">
              <VIcon icon="mdi-book-open-variant" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalCourses }}</div>
              <div class="text-caption text-medium-emphasis">Total Courses</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="success" variant="tonal" rounded size="48">
              <VIcon icon="mdi-check-circle" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ publishedCourses }}</div>
              <div class="text-caption text-medium-emphasis">Published</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="warning" variant="tonal" rounded size="48">
              <VIcon icon="mdi-pencil-outline" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ draftCourses }}</div>
              <div class="text-caption text-medium-emphasis">Draft</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="purple" variant="tonal" rounded size="48">
              <VIcon icon="mdi-account-group" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalStudents }}</div>
              <div class="text-caption text-medium-emphasis">Total Students</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <div>
      <div class="d-flex align-center justify-space-between mb-4">
        <h5 class="text-h5 font-weight-bold">My Courses</h5>
        <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('instructor.courses.create')">
          Create New Course
        </VBtn>
      </div>

      <VRow v-if="recentCourses.length">
        <VCol v-for="course in recentCourses" :key="course.id" cols="12" md="6" lg="4">
          <VCard flat border hover>
            <div class="position-relative overflow-hidden" style="height: 128px;">
              <img v-if="course.thumbnail" :src="course.thumbnail" class="w-100 h-100" style="object-fit: cover; object-position: center;" />
              <div v-if="course.thumbnail" class="position-absolute w-100 h-100" style="inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.3), transparent);" />
              <div v-else class="w-100 h-100" style="background: linear-gradient(135deg, rgb(115, 103, 240), rgb(94, 75, 227));" />
            </div>
            <VCardText>
              <h6 class="text-h6 font-weight-bold mb-1">{{ course.title }}</h6>
              <p class="text-body-2 text-medium-emphasis text-truncate mb-4">{{ course.description }}</p>
              <div class="d-flex align-center justify-space-between mb-3">
                <VChip :color="course.published ? 'success' : 'warning'" size="small" variant="tonal" label>
                  {{ course.published ? 'Published' : 'Draft' }}
                </VChip>
                <span class="text-caption text-medium-emphasis">{{ course.sections?.length || 0 }} sections</span>
              </div>
              <div class="d-flex ga-2" style="flex-wrap: nowrap;">
              <div class="flex-grow-1">
                <VBtn variant="tonal" color="primary" block :href="route('instructor.courses.show', course.id)">Detail</VBtn>
              </div>
              <div class="flex-grow-1">
                <VBtn variant="elevated" color="primary" block :href="route('instructor.courses.edit', course.id)">Edit</VBtn>
              </div>

            </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
      <VCard v-else flat border class="pa-12 text-center">
        <VIcon icon="mdi-book-open-blank-variant" size="64" color="disabled" />
        <h5 class="text-h5 mt-4 mb-2">No courses yet</h5>
        <p class="text-body-2 text-medium-emphasis mb-4">Create your first course to get started teaching students</p>
        <VBtn color="primary" variant="elevated" :href="route('instructor.courses.create')">Create Your First Course</VBtn>
      </VCard>
    </div>
  </div>
</template>
