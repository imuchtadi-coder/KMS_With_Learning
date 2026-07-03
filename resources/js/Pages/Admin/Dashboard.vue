<script setup>
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

defineProps({
  totalUsers: { type: Number, default: 0 },
  totalAdmins: { type: Number, default: 0 },
  totalInstructors: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  recentUsers: { type: Array, default: () => [] },
})

const flash = usePage().props.flash
</script>

<template>
  <div>
    <VAlert
      v-if="flash?.success"
      type="success"
      closable
      class="mb-6"
      :text="flash.success"
    />

    <div class="mb-8">
      <h4 class="text-h4 font-weight-bold mb-1">Welcome!</h4>
      <p class="text-body-1 text-medium-emphasis">
        <template v-if="$page.props.auth.user.roles?.includes('tenant_admin')">
          Here's what's happening in your tenant.
        </template>
        <template v-else>
          Here's what's happening with your LMS today.
        </template>
      </p>
    </div>

    <VRow class="mb-6">
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" rounded size="48">
              <VIcon icon="mdi-account-group" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalUsers }}</div>
              <div class="text-caption text-medium-emphasis">Total Users</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="success" variant="tonal" rounded size="48">
              <VIcon icon="mdi-shield-account" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalAdmins }}</div>
              <div class="text-caption text-medium-emphasis">Tenant Admins</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="warning" variant="tonal" rounded size="48">
              <VIcon icon="mdi-teach" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalInstructors }}</div>
              <div class="text-caption text-medium-emphasis">Instructors</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" sm="6" lg="3">
        <VCard flat border>
          <VCardText class="d-flex align-center ga-3">
            <VAvatar color="info" variant="tonal" rounded size="48">
              <VIcon icon="mdi-school" size="24" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">{{ totalStudents }}</div>
              <div class="text-caption text-medium-emphasis">Students</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VCard flat border :title="'Recent Users (' + recentUsers.length + ')'">
      <template #append>
        <VBtn
          color="primary"
          variant="tonal"
          size="small"
          prepend-icon="mdi-plus"
          :href="route('admin.users.create')"
        >
          Add User
        </VBtn>
      </template>

      <VCardText v-if="!recentUsers.length" class="text-center pa-12">
        <VIcon icon="mdi-account-off" size="64" color="disabled" />
        <h5 class="text-h5 mt-4 mb-2">No users yet</h5>
        <VBtn color="primary" :href="route('admin.users.create')">Create First User</VBtn>
      </VCardText>

      <VTable v-else density="comfortable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in recentUsers" :key="user.id">
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
              <VChip v-if="user.roles?.length" size="x-small" color="primary" variant="tonal" label>
                {{ user.roles[0].name }}
              </VChip>
              <span v-else class="text-caption text-disabled">No role</span>
            </td>
            <td>{{ new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</td>
            <td>
              <div class="d-flex ga-1">
                <VBtn icon size="x-small" color="primary" variant="tonal" :href="route('admin.users.edit', user.id)">
                  <VIcon icon="mdi-pencil" size="16" />
                </VBtn>
                <VBtn icon size="x-small" color="error" variant="tonal" @click="router.delete(route('admin.users.delete', user.id), { onSuccess: () => {} })">
                  <VIcon icon="mdi-delete" size="16" />
                </VBtn>
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>
