<script setup>
import { router } from '@inertiajs/vue3'
import { reactive } from 'vue'

defineProps({
  logs: { type: Object, default: () => ({ data: [] }) },
  actions: { type: Array, default: () => [] },
  modelTypes: { type: Array, default: () => [] },
})

const filterForm = reactive({
  action: '',
  model_type: '',
  user_id: '',
  date_from: '',
  date_to: '',
})

const applyFilter = () => {
  router.get(route('admin.audit-logs.index'), filterForm, { preserveState: true })
}

const resetFilter = () => {
  Object.assign(filterForm, { action: '', model_type: '', user_id: '', date_from: '', date_to: '' })
  router.get(route('admin.audit-logs.index'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Audit Logs</h4>
      <p class="text-body-2 text-medium-emphasis">Track all system activities</p>
    </div>

    <VCard flat border class="mb-4">
      <VCardText>
        <VRow dense>
          <VCol cols="12" sm="6" md="2">
            <VSelect v-model="filterForm.action" :items="actions" label="Action" clearable density="default" />
          </VCol>
          <VCol cols="12" sm="6" md="3">
            <VSelect v-model="filterForm.model_type" :items="modelTypes" label="Model Type" clearable density="default" />
          </VCol>
          <VCol cols="12" sm="6" md="2">
            <VTextField v-model="filterForm.date_from" label="From" type="date" density="default" />
          </VCol>
          <VCol cols="12" sm="6" md="2">
            <VTextField v-model="filterForm.date_to" label="To" type="date" density="default" />
          </VCol>
          <VCol cols="12" md="3" class="d-flex align-center ga-2">
            <VBtn size="small" color="primary" variant="tonal" @click="applyFilter">Filter</VBtn>
            <VBtn size="small" variant="outlined" @click="resetFilter">Reset</VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>User</th>
            <th>Action</th>
            <th>Model</th>
            <th>Details</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="log in logs.data" :key="log.id">
            <td>
              <span class="font-weight-medium">{{ log.user?.name || 'System' }}</span>
              <div class="text-caption">{{ log.user?.email }}</div>
            </td>
            <td>
              <VChip
                :color="log.action === 'deleted' ? 'error' : log.action === 'created' ? 'success' : 'info'"
                size="x-small"
                variant="tonal"
                label
              >
                {{ log.action }}
              </VChip>
            </td>
            <td class="text-caption">
              {{ log.model_type }} #{{ log.model_id }}
            </td>
            <td class="text-caption text-truncate" style="max-width: 250px;">
              <VBtn variant="text" size="x-small" density="compact" :href="route('admin.audit-logs.show', log.id)">
                View Details
              </VBtn>
            </td>
            <td class="text-caption">{{ new Date(log.created_at).toLocaleString() }}</td>
          </tr>
          <tr v-if="!logs.data.length">
            <td colspan="5" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-clipboard-text-off" size="48" color="disabled" class="mb-2" />
              <div>No audit logs found</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />
      <VCardText v-if="logs.links" class="d-flex justify-center pa-4">
        <VPagination
          v-model="logs.current_page"
          :length="logs.last_page"
          @update:model-value="val => router.get(route('admin.audit-logs.index', { page: val }))"
        />
      </VCardText>
    </VCard>
  </div>
</template>
