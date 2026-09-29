<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  paths: { type: Object, default: () => ({ data: [] }) },
})

const deletePath = (pathId) => {
  if (window.confirm('Delete this path?')) {
    router.delete(route('admin.learning-paths.destroy', pathId))
  }
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Learning Paths</h4>
        <p class="text-body-2 text-medium-emphasis">Manage structured learning journeys</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('admin.learning-paths.create')">
        Create Path
      </VBtn>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Courses</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="path in paths.data" :key="path.id">
            <td class="font-weight-medium">{{ path.title }}</td>
            <td>{{ path.courses?.length || 0 }}</td>
            <td>
              <VChip 
                :color="path.published_at ? 'success' : 'warning'" 
                size="x-small" 
                variant="tonal" 
                label 
              >
                {{ path.published_at ? 'Published' : 'Draft' }}
              </VChip>
            </td>
            <td class="text-caption">{{ new Date(path.created_at).toLocaleDateString() }}</td>
            <td>
              <div class="d-flex ga-1">
                <VBtn icon size="x-small" color="primary" variant="tonal" :href="route('admin.learning-paths.show', path.id)">
                  <VIcon icon="mdi-eye" size="16" />
                </VBtn>
                <VBtn icon size="x-small" color="warning" variant="tonal" :href="route('admin.learning-paths.edit', path.id)">
                  <VIcon icon="mdi-pencil" size="16" />
                </VBtn>
                <VBtn
                  icon
                  size="x-small"
                  color="error"
                  variant="tonal"
                  @click="deletePath(path.id)"
                >
                  <VIcon icon="mdi-delete" size="16" />
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!paths.data.length">
            <td colspan="5" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-road-variant" size="48" color="disabled" class="mb-2" />
              <div>No learning paths yet</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />
      <VCardText v-if="paths.links" class="d-flex justify-center pa-4">
        <VPagination
          v-model="paths.current_page"
          :length="paths.last_page"
          @update:model-value="val => router.get(route('admin.learning-paths.index', { page: val }))"
        />
      </VCardText>
    </VCard>
  </div>
</template>
