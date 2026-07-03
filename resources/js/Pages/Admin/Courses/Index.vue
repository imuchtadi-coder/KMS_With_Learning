<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  courses: { type: Object, default: () => ({ data: [] }) },
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Courses</h4>
      <p class="text-body-2 text-medium-emphasis">Review and manage courses in your tenant</p>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Instructor</th>
            <th>Sections</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in courses.data" :key="course.id">
            <td class="font-weight-medium">{{ course.title }}</td>
            <td>{{ course.instructor?.name || 'N/A' }}</td>
            <td>{{ course.sections?.length || 0 }}</td>
            <td>
              <VChip
                :color="course.published ? 'success' : 'warning'"
                size="x-small"
                variant="tonal"
                label
              >
                {{ course.published ? 'Published' : 'Draft' }}
              </VChip>
            </td>
            <td class="text-caption">{{ new Date(course.created_at).toLocaleDateString() }}</td>
            <td>
              <div class="d-flex ga-1">
                <VBtn icon size="x-small" color="primary" variant="tonal" :href="route('admin.courses.show', course.id)">
                  <VIcon icon="mdi-eye" size="16" />
                </VBtn>
                <VBtn
                  v-if="!course.published"
                  icon
                  size="x-small"
                  color="success"
                  variant="tonal"
                  @click="router.post(route('admin.courses.publish', course.id))"
                >
                  <VIcon icon="mdi-check" size="16" />
                </VBtn>
                <VBtn
                  v-else
                  icon
                  size="x-small"
                  color="warning"
                  variant="tonal"
                  @click="router.post(route('admin.courses.unpublish', course.id))"
                >
                  <VIcon icon="mdi-close" size="16" />
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!courses.data.length">
            <td colspan="6" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-book-off" size="48" color="disabled" class="mb-2" />
              <div>No courses found</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />
      <VCardText v-if="courses.links" class="d-flex justify-center pa-4">
        <VPagination
          v-model="courses.current_page"
          :length="courses.last_page"
          @update:model-value="val => router.get(route('admin.courses.index', { page: val }))"
        />
      </VCardText>
    </VCard>
  </div>
</template>
