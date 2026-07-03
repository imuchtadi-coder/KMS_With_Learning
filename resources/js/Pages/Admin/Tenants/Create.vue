<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  domain: '',
})

const submit = () => {
  form.post(route('admin.tenants.store'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Create Tenant</h4>
      <p class="text-body-2 text-medium-emphasis">Register a new tenant organization</p>
    </div>

    <VCard flat border max-width="600">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VTextField
            v-model="form.name"
            label="Tenant Name"
            :error-messages="form.errors.name"
            required
            class="mb-4"
          />
          <VTextField
            v-model="form.domain"
            label="Domain (e.g. tenant.example.com)"
            :error-messages="form.errors.domain"
            required
            class="mb-4"
          />
          <div class="d-flex ga-2">
            <VBtn type="submit" color="primary" :loading="form.processing">Create Tenant</VBtn>
            <VBtn variant="outlined" :href="route('admin.tenants.index')">Cancel</VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
