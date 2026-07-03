<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  users: { type: Object, default: () => ({ data: [] }) },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Users</h4>
        <p class="text-body-2 text-medium-emphasis">Manage all users in your tenant</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-plus" :href="route('admin.users.create')">
        Add User
      </VBtn>
    </div>

    <VCard flat border>
      <VTable density="comfortable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Tenant</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users.data" :key="user.id">
            <td>
              <div class="d-flex align-center ga-2">
                <VAvatar color="primary" size="32">
                  <span class="text-uppercase font-weight-bold">{{ user.name?.charAt(0) }}</span>
                </VAvatar>
                <span class="font-weight-medium">{{ user.name }}</span>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td>
              <VChip
                v-if="user.roles?.length"
                size="x-small"
                :color="user.roles[0].name === 'superadmin' ? 'error' : user.roles[0].name === 'tenant_admin' ? 'warning' : 'primary'"
                variant="tonal"
                label
              >
                {{ user.roles[0].name }}
              </VChip>
              <span v-else class="text-caption text-disabled">No role</span>
            </td>
            <td class="text-caption">{{ user.tenant_id }}</td>
            <td class="text-caption">{{ new Date(user.created_at).toLocaleDateString() }}</td>
            <td>
              <div class="d-flex ga-1">
                <VBtn icon size="x-small" color="primary" variant="tonal" :href="route('admin.users.edit', user.id)">
                  <VIcon icon="mdi-pencil" size="16" />
                </VBtn>
                <VBtn icon size="x-small" color="error" variant="tonal" @click="confirmDelete(user)">
                  <VIcon icon="mdi-delete" size="16" />
                </VBtn>
              </div>
            </td>
          </tr>
          <tr v-if="!users.data.length">
            <td colspan="6" class="text-center pa-8 text-medium-emphasis">
              <VIcon icon="mdi-account-off" size="48" color="disabled" class="mb-2" />
              <div>No users found</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <VDivider />
      <VCardText v-if="users.links" class="d-flex justify-center pa-4">
        <VPagination
          v-model="users.current_page"
          :length="users.last_page"
          @update:model-value="val => router.get(route('admin.users.index', { page: val }))"
        />
      </VCardText>
    </VCard>
  </div>
</template>

<script>
export default {
  methods: {
    confirmDelete(user) {
      const confirmed = window.confirm(`Are you sure you want to delete "${user.name}"?`)
      if (confirmed) {
        router.delete(route('admin.users.delete', user.id))
      }
    }
  }
}
</script>
