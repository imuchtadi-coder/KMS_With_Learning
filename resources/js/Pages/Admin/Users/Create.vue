<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineProps({
  roles: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
  tenant_id: '',
  phone: '',
  birthday: '',
  department: '',
  address: '',
})

const snackbar = ref(false)
const snackbarMessage = ref('')

const submit = () => {
  form.post(route('admin.users.store'), {
    preserveScroll: true,

    onStart: () => {
      console.log('=== REQUEST START ===')
    },

    onError: (errors) => {
      console.log('=== VALIDATION ERRORS ===')
      console.log(errors)

      const fields = Object.keys(errors)

      if (fields.length > 0) {
        snackbarMessage.value =
          fields.length === 1
            ? `Please complete the required field: ${fields[0]}`
            : `Please complete the required fields: ${fields.join(', ')}`
        
        snackbar.value = true
      }
    },

    onSuccess: () => {
      console.log('=== USER CREATED ===')
    },

    onFinish: () => {
      console.log('=== REQUEST FINISHED ===')
    },
  })
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">
        Create User
      </h4>

      <p class="text-body-2 text-medium-emphasis">
        Add a new user to the system
      </p>
    </div>

    <VCard flat border max-width="800">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <!-- Name -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.name"
                label="Name"
                :error-messages="form.errors.name"
                required
              />
            </VCol>

            <!-- Email -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
                :error-messages="form.errors.email"
                required
              />
            </VCol>

            <!-- Password -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.password"
                label="Password"
                type="password"
                :error-messages="form.errors.password"
                required
              />
            </VCol>

            <!-- Confirm Password -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                :error-messages="form.errors.password_confirmation"
                required
              />
            </VCol>

            <!-- Role -->
            <VCol cols="12" md="6">
              <VSelect
                v-model="form.role"
                :items="roles.map(r => ({
                  title: r.name,
                  value: r.name,
                }))"
                label="Role"
                :error-messages="form.errors.role"
                required
              />
            </VCol>

            <!-- Tenant -->
            <VCol cols="12" md="6">
              <VSelect
                v-model="form.tenant_id"
                :items="tenants.map(t => ({
                  title: t.name + ' (' + t.id + ')',
                  value: t.id,
                }))"
                label="Tenant"
                :error-messages="form.errors.tenant_id"
                required
              />
            </VCol>

            <!-- Phone -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.phone"
                label="Phone"
                :error-messages="form.errors.phone"
              />
            </VCol>

            <!-- Birthday -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.birthday"
                label="Birthday"
                type="date"
                :error-messages="form.errors.birthday"
              />
            </VCol>

            <!-- Department -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.department"
                label="Department"
                :error-messages="form.errors.department"
              />
            </VCol>

            <!-- Address -->
            <VCol cols="12">
              <VTextarea
                v-model="form.address"
                label="Address"
                :error-messages="form.errors.address"
                rows="3"
              />
            </VCol>

            <!-- Buttons -->
            <VCol cols="12" class="d-flex ga-2">
              <VBtn
                type="submit"
                color="primary"
                :loading="form.processing"
              >
                Create User
              </VBtn>

              <VBtn
                variant="outlined"
                :href="route('admin.users.index')"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>

    <!-- Validation Snackbar -->
    <VSnackbar
      v-model="snackbar"
      color="error"
      location="top right"
      :timeout="5000"
    >
      {{ snackbarMessage }}

      <template #actions>
        <VBtn
          variant="text"
          @click="snackbar = false"
        >
          Close
        </VBtn>
      </template>
    </VSnackbar>
  </div>
</template>
