<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  tags: {
    type: Object,
    default: () => ({ data: [] }),
  },
})

const deleteTag = (tag) => {
  if (!confirm(`Delete tag "${tag.name}"?`)) {
    return
  }

  router.delete(route('admin.tags.destroy', tag.id))
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">
          Tags
        </h4>

        <p class="text-body-2 text-medium-emphasis">
          Manage course tags
        </p>
      </div>

      <VBtn
        color="primary"
        variant="elevated"
        prepend-icon="mdi-plus"
        :href="route('admin.tags.create')"
      >
        Add Tag
      </VBtn>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Courses</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="tag in tags.data"
            :key="tag.id"
          >
            <td class="font-weight-medium">
              {{ tag.name }}
            </td>

            <td class="text-caption">
              {{ tag.slug }}
            </td>

            <td>
              <VChip
                size="x-small"
                variant="tonal"
              >
                {{ tag.courses_count || 0 }}
              </VChip>
            </td>

            <td>
              <div class="d-flex ga-1">
                <VBtn
                  icon
                  size="x-small"
                  color="warning"
                  variant="tonal"
                  :href="route('admin.tags.edit', tag.id)"
                >
                  <VIcon
                    icon="mdi-pencil"
                    size="16"
                  />
                </VBtn>

                <VBtn
                  icon
                  size="x-small"
                  color="error"
                  variant="tonal"
                  @click="deleteTag(tag)"
                >
                  <VIcon
                    icon="mdi-delete"
                    size="16"
                  />
                </VBtn>
              </div>
            </td>
          </tr>

          <tr v-if="!tags.data.length">
            <td
              colspan="4"
              class="text-center pa-8 text-medium-emphasis"
            >
              <VIcon
                icon="mdi-tag-off"
                size="48"
                color="disabled"
                class="mb-2"
              />

              <div>No tags yet</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />

      <VCardText
        v-if="tags.links"
        class="d-flex justify-center pa-4"
      >
        <VPagination
          v-model="tags.current_page"
          :length="tags.last_page"
          @update:model-value="val => router.get(
            route('admin.tags.index', { page: val })
          )"
        />
      </VCardText>
    </VCard>
  </div>
</template>