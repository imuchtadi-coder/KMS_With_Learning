<script setup>
import { useForm, router } from '@inertiajs/vue3'

defineProps({
  user: { type: Object, required: true },
  roles: { type: Array, default: () => [] },
  tenants: { type: Array, default: () => [] },
})

const form = useForm({
  name: '',
  email: '',
  role: '',
  tenant_id: '',
  password: '',
  password_confirmation: '',
  phone: '',
  birthday: '',
  department: '',
  address: '',
})

const init = (user) => {
  form.name = user.name || ''
  form.email = user.email || ''
  form.role = user.roles?.[0]?.name || ''
  form.tenant_id = user.tenant_id || ''
  form.phone = user.phone || ''
  form.birthday = user.birthday || ''
  form.department = user.department || ''
  form.address = user.address || ''
}

const submit = () => {
  form.put(route('admin.users.update', user.id))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Edit User</h4>
      <p class="text-body-2 text-medium-emphasis">{{ user.name }}</p>
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
                label="New Password (leave blank to keep)"
                type="password"
                :error-messages="form.errors.password"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.password_confirmation"
                label="Confirm Password"
                type="password"
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
              <VBtn type="submit" color="primary" :loading="form.processing">Update User</VBtn>
              <VBtn variant="outlined" :href="route('admin.users.index')">Cancel</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<script>
export default {
  created() {
    this.init(this.$props.user)
  }
}
</script>
