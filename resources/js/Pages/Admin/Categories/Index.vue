<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  categories: { type: Object, default: () => ({ data: [] }) },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Categories</h4>
        <p class="text-body-2 text-medium-emphasis">Organize courses by category</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('admin.categories.create')">
        Add Category
      </VBtn>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Parent</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cat in categories.data" :key="cat.id">
            <td class="font-weight-medium">{{ cat.name }}</td>
            <td class="text-caption">{{ cat.slug }}</td>
            <td>{{ cat.parent?.name || '—' }}</td>
            <td class="text-caption text-truncate" style="max-width: 300px;">{{ cat.description || '—' }}</td>
            <td>
              <div class="d-flex ga-1">
                <VBtn icon size="x-small" color="warning" variant="tonal" :href="route('admin.categories.edit', cat.id)">
                  <VIcon icon="mdi-pencil" size="16" />
                </VBtn>
                <VBtn icon size="x-small" color="error" variant="tonal" @click="window.confirm('Delete?') && router.delete(route('admin.categories.destroy', cat.id))">
                  <VIcon icon="mdi-delete" size="16" />
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!categories.data.length">
            <td colspan="5" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-shape-outline" size="48" color="disabled" class="mb-2" />
              <div>No categories yet</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />
      <VCardText v-if="categories.links" class="d-flex justify-center pa-4">
        <VPagination
          v-model="categories.current_page"
          :length="categories.last_page"
          @update:model-value="val => router.get(route('admin.categories.index', { page: val }))"
        />
      </VCardText>
    </VCard>
  </div>
</template>
