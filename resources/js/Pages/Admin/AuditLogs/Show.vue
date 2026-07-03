<script setup>
defineProps({
  auditLog: { type: Object, required: true },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Audit Log Details</h4>
        <p class="text-body-2 text-medium-emphasis">#{{ auditLog.id }}</p>
      </div>
      <VBtn variant="outlined" :href="route('admin.audit-logs.index')">Back</VBtn>
    </div>

    <VCard flat border max-width="700">
      <VCardText>
        <VRow>
          <VCol cols="4" class="text-caption text-medium-emphasis">User</VCol>
          <VCol cols="8" class="font-weight-medium">{{ auditLog.user?.name || 'System' }} ({{ auditLog.user?.email || 'N/A' }})</VCol>
          <VCol cols="12"><VDivider /></VCol>
          <VCol cols="4" class="text-caption text-medium-emphasis">Action</VCol>
          <VCol cols="8">
            <VChip :color="auditLog.action === 'deleted' ? 'error' : auditLog.action === 'created' ? 'success' : 'info'" size="small" variant="tonal" label>
              {{ auditLog.action }}
            </VChip>
          </VCol>
          <VCol cols="12"><VDivider /></VCol>
          <VCol cols="4" class="text-caption text-medium-emphasis">Model</VCol>
          <VCol cols="8">{{ auditLog.model_type }} #{{ auditLog.model_id }}</VCol>
          <VCol cols="12"><VDivider /></VCol>
          <VCol cols="4" class="text-caption text-medium-emphasis">Date</VCol>
          <VCol cols="8">{{ new Date(auditLog.created_at).toLocaleString() }}</VCol>
          <VCol cols="12"><VDivider v-if="auditLog.properties" /></VCol>
          <VCol v-if="auditLog.properties" cols="4" class="text-caption text-medium-emphasis">Properties</VCol>
          <VCol v-if="auditLog.properties" cols="8">
            <pre class="text-caption bg-surface pa-3 rounded">{{ JSON.stringify(auditLog.properties, null, 2) }}</pre>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </div>
</template>
