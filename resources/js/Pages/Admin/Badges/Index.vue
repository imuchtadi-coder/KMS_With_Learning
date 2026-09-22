<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  badges: {
    type: Object,
    default: () => ({ data: [] }),
  },
})

const deleteBadge = badge => {
  if (!window.confirm(`Delete badge "${badge.name}"?`)) {
    return
  }

  router.delete(
    route('admin.badges.destroy', badge.id)
  )
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Badges</h4>
        <p class="text-body-2 text-medium-emphasis">Gamification badges and achievements</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('admin.badges.create')">
        Create Badge
      </VBtn>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Criteria</th>
            <th>Value</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="badge in badges.data" :key="badge.id">
            <td>
              <div class="d-flex align-center ga-2">
                <VAvatar v-if="badge.image_url" :image="badge.image_url" size="32" rounded />
                <VAvatar v-else color="warning" variant="tonal" size="32" rounded>
                  <VIcon icon="mdi-trophy" size="18" />
                </VAvatar>
                <span class="font-weight-medium">{{ badge.name }}</span>
              </div>
            </td>
            <td class="text-caption">{{ badge.criteria_type?.replace(/_/g, ' ') }}</td>
            <td>{{ badge.criteria_value }}</td>
            <td>
              <VChip :color="badge.active ? 'success' : 'disabled'" size="x-small" variant="tonal" label>
                {{ badge.active ? 'Active' : 'Inactive' }}
              </VChip>
            </td>
            <td>
              <div class="d-flex ga-1">
                <VBtn icon size="x-small" color="warning" variant="tonal" :href="route('admin.badges.edit', badge.id)">
                  <VIcon icon="mdi-pencil" size="16" />
                </VBtn>
                <VBtn icon size="x-small" color="error" variant="tonal" @click="deleteBadge(badge)">
                  <VIcon icon="mdi-delete" size="16" />
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!badges.data.length">
            <td colspan="5" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-trophy-off" size="48" color="disabled" class="mb-2" />
              <div>No badges yet</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />
      <VCardText v-if="badges.links" class="d-flex justify-center pa-4">
        <VPagination
          v-model="badges.current_page"
          :length="badges.last_page"
          @update:model-value="val => router.get(route('admin.badges.index', { page: val }))"
        />
      </VCardText>
    </VCard>
  </div>
</template>
