<script setup>
import { ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
  users: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
})

const page = usePage()
const showImportDialog = ref(false)

const search = ref(props.filters?.search ?? '')

const form = useForm({
  csv: null,
})

let searchTimeout

watch(search, value => {
  clearTimeout(searchTimeout)

  searchTimeout = setTimeout(() => {
    router.get(
      route('admin.users.index'),
      {
        search: value || undefined,
        page: 1,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      },
    )
  }, 400)
})

const submitImport = () => {
  if (!form.csv) return

  form.post(route('admin.bulk.import-users'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      showImportDialog.value = false
    },
  })
}

const closeImportDialog = () => {
  if (form.processing) return

  form.reset()
  form.clearErrors()
  showImportDialog.value = false
}

const confirmDelete = user => {
  const confirmed = window.confirm(
    `Are you sure you want to delete "${user.name}"?`,
  )

  if (confirmed) {
    router.delete(route('admin.users.delete', user.id))
  }
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Users</h4>
        <p class="text-body-2 text-medium-emphasis">
          Manage all users in your tenant
        </p>
      </div>

      <div class="d-flex ga-2">
        <!-- Import CSV -->
        <VBtn
          color="secondary"
          variant="tonal"
          prepend-icon="mdi-file-upload-outline"
          @click="showImportDialog = true"
        >
          Import CSV
        </VBtn>

        <!-- Add User -->
        <VBtn
          color="primary"
          variant="elevated"
          prepend-icon="mdi-plus"
          :href="route('admin.users.create')"
        >
          Add User
        </VBtn>
      </div>
    </div>

    <!-- Flash Success -->
    <VAlert
      v-if="page.props.flash?.success"
      type="success"
      closable
      :text="page.props.flash.success"
      class="mb-4"
    />

    <!-- Flash Import Errors -->
    <VAlert
      v-if="page.props.flash?.errors?.length"
      type="warning"
      closable
      class="mb-4"
    >
      <div
        v-for="err in page.props.flash.errors"
        :key="err"
      >
        {{ err }}
      </div>
    </VAlert>

    <!-- Search -->
    <div class="mb-4">
      <VTextField
        v-model="search"
        label="Search users"
        placeholder="Search by name, email, role, or tenant..."
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        density="comfortable"
        clearable
        hide-details
      />
    </div>

    <!-- Users Table -->
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
          <tr
            v-for="user in users.data"
            :key="user.id"
          >
            <td>
              <div class="d-flex align-center ga-2">
                <VAvatar
                  color="primary"
                  size="32"
                >
                  <span class="text-uppercase font-weight-bold">
                    {{ user.name?.charAt(0) }}
                  </span>
                </VAvatar>

                <span class="font-weight-medium">
                  {{ user.name }}
                </span>
              </div>
            </td>

            <td>{{ user.email }}</td>

            <td>
              <VChip
                v-if="user.roles?.length"
                size="x-small"
                :color="
                  user.roles[0].name === 'superadmin'
                    ? 'error'
                    : user.roles[0].name === 'tenant_admin'
                      ? 'warning'
                      : 'primary'
                "
                variant="tonal"
                label
              >
                {{ user.roles[0].name }}
              </VChip>

              <span
                v-else
                class="text-caption text-disabled"
              >
                No role
              </span>
            </td>

            <td class="text-caption">
              {{ user.tenant_id }}
            </td>

            <td class="text-caption">
              {{ new Date(user.created_at).toLocaleDateString() }}
            </td>

            <td>
              <div class="d-flex ga-1">
                <VBtn
                  icon
                  size="x-small"
                  color="primary"
                  variant="tonal"
                  :href="route('admin.users.edit', user.id)"
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
                  @click="confirmDelete(user)"
                >
                  <VIcon
                    icon="mdi-delete"
                    size="16"
                  />
                </VBtn>
              </div>
            </td>
          </tr>

          <tr v-if="!users.data.length">
            <td
              colspan="6"
              class="text-center pa-8 text-medium-emphasis"
            >
              <VIcon
                icon="mdi-account-off"
                size="48"
                color="disabled"
                class="mb-2"
              />

              <div>No users found</div>
            </td>
          </tr>
        </tbody>
      </VTable>

      <!-- Pagination -->
      <VDivider />

      <VCardText
        v-if="users.links"
        class="d-flex justify-center pa-4"
      >
        <VPagination
          v-model="users.current_page"
          :length="users.last_page"
          @update:model-value="
            val => router.get(route('admin.users.index', { page: val, search: search || undefined, })
            )
          "

        />
      </VCardText>
    </VCard>

    <!-- Import CSV Dialog -->
    <VDialog
      v-model="showImportDialog"
      max-width="600"
      persistent
    >
      <VCard>
        <VCardTitle class="d-flex align-center justify-space-between">
          <div class="d-flex align-center ga-2">
            <VIcon icon="mdi-file-upload-outline" />
            <span>Import Users</span>
          </div>

          <VBtn
            icon="mdi-close"
            variant="text"
            :disabled="form.processing"
            @click="closeImportDialog"
          />
        </VCardTitle>

        <VDivider />

        <VCardText>
          <p class="text-body-2 mb-4">
            Bulk import users from a CSV file.
          </p>

          <VAlert
            type="info"
            variant="tonal"
            density="comfortable"
            class="mb-4"
          >
            <div class="text-body-2">
              CSV must have headers:
            </div>

            <code>
              name, email, password, role, department
            </code>

            <div class="text-body-2 mt-2">
              Role must be
              <code>student</code>
              or
              <code>instructor</code>.
            </div>
          </VAlert>

          <VForm @submit.prevent="submitImport">
            <VFileInput
              v-model="form.csv"
              label="CSV File"
              placeholder="Choose CSV file"
              accept=".csv,.txt"
              prepend-icon="mdi-file-delimited-outline"
              :error-messages="form.errors.csv"
              :disabled="form.processing"
              show-size
              clearable
              required
              class="mb-4"
            />

            <div class="d-flex justify-end ga-2">
              <VBtn
                variant="tonal"
                :disabled="form.processing"
                @click="closeImportDialog"
              >
                Cancel
              </VBtn>

              <VBtn
                type="submit"
                color="primary"
                prepend-icon="mdi-upload"
                :loading="form.processing"
                :disabled="!form.csv"
              >
                Import Users
              </VBtn>
            </div>
          </VForm>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
