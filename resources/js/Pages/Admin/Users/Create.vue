<script setup>
import { useForm, router } from '@inertiajs/vue3'

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

const submit = () => {
  form.post(route('admin.users.store'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Create User</h4>
      <p class="text-body-2 text-medium-emphasis">Add a new user to the system</p>
    </div>

    <VCard flat border max-width="800">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.name"
                label="Name"
                :error-messages="form.errors.name"
                required
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
                :error-messages="form.errors.email"
                required
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.password"
                label="Password"
                type="password"
                :error-messages="form.errors.password"
                required
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
                :error-messages="form.errors.password_confirmation"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect
                v-model="form.role"
                :items="roles.map(r => ({ title: r.name, value: r.name }))"
                label="Role"
                :error-messages="form.errors.role"
                required
              />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect
                v-model="form.tenant_id"
                :items="tenants.map(t => ({ title: t.name + ' (' + t.id + ')', value: t.id }))"
                label="Tenant"
                :error-messages="form.errors.tenant_id"
                required
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.phone" label="Phone" :error-messages="form.errors.phone" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.birthday" label="Birthday" type="date" :error-messages="form.errors.birthday" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.department" label="Department" :error-messages="form.errors.department" />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="form.address" label="Address" :error-messages="form.errors.address" rows="3" />
            </VCol>
            <VCol cols="12" class="d-flex ga-2">
              <VBtn type="submit" color="primary" :loading="form.processing">Create User</VBtn>
              <VBtn variant="outlined" :href="route('admin.users.index')">Cancel</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
