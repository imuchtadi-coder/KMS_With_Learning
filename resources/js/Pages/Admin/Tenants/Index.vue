<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  tenants: {
    type: Object,
    default: () => ({ data: [] }),
  },
})

const deleteTenant = (tenant) => {
  if (!confirm(`Delete "${tenant.name}"? This will remove all data.`)) {
    return
  }

  router.delete(route('admin.tenants.delete', tenant.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">
          Tenants
        </h4>

        <p class="text-body-2 text-medium-emphasis">
          Manage all tenants (superadmin only)
        </p>
      </div>

      <VBtn
        color="primary"
        variant="elevated"
        prepend-icon="mdi-plus"
        :href="route('admin.tenants.create')"
      >
        Create Tenant
      </VBtn>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Domain</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="tenant in tenants.data"
            :key="tenant.id"
          >
            <td class="text-caption font-family-mono">
              {{ tenant.id }}
            </td>

            <td class="font-weight-medium">
              {{ tenant.name }}
            </td>

            <td>
              <VChip
                v-for="domain in tenant.domains"
                :key="domain.id"
                size="x-small"
                variant="tonal"
                class="mr-1"
              >
                {{ domain.domain }}
              </VChip>

              <span
                v-if="!tenant.domains?.length"
                class="text-disabled"
              >
                No domain
              </span>
            </td>

            <td class="text-caption">
              {{ new Date(tenant.created_at).toLocaleDateString() }}
            </td>

            <td>
              <div class="d-flex ga-1">
                <!-- Edit -->
                <VBtn
                  icon
                  size="x-small"
                  color="warning"
                  variant="tonal"
                  :href="route('admin.tenants.edit', tenant.id)"
                >
                  <VIcon
                    icon="mdi-pencil"
                    size="16"
                  />
                </VBtn>

                <!-- Delete -->
                <VBtn
                  icon
                  size="x-small"
                  color="error"
                  variant="tonal"
                  @click="deleteTenant(tenant)"
                >
                  <VIcon
                    icon="mdi-delete"
                    size="16"
                  />
                </VBtn>
              </div>
            </td>
          </tr>

          <tr v-if="!tenants.data.length">
            <td
              colspan="5"
              class="text-center pa-8 text-medium-emphasis"
            >
              <VIcon
                icon="mdi-domain-off"
                size="48"
                color="disabled"
                class="mb-2"
              />

              <div>No tenants yet</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />

      <VCardText
        v-if="tenants.links"
        class="d-flex justify-center pa-4"
      >
        <VPagination
          v-model="tenants.current_page"
          :length="tenants.last_page"
          @update:model-value="
            val => router.get(
              route('admin.tenants.index', { page: val })
            )
          "
        />
      </VCardText>
    </VCard>
  </div>
</template>