<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  tenant: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  name: props.tenant.name || '',
  domain: props.tenant.domains?.[0]?.domain || '',
})

const submit = () => {
  form.put(route('admin.tenants.update', props.tenant.id))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">
        Edit Tenant
      </h4>

      <p class="text-body-2 text-medium-emphasis">
        {{ props.tenant.name }}
      </p>
    </div>

    <VCard
      flat
      border
      max-width="600"
    >
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
            label="Domain"
            :error-messages="form.errors.domain"
            required
            class="mb-4"
          />

          <div class="d-flex ga-2">
            <VBtn
              type="submit"
              color="primary"
              :loading="form.processing"
            >
              Update Tenant
            </VBtn>

            <VBtn
              variant="outlined"
              :href="route('admin.tenants.index')"
            >
              Cancel
            </VBtn>
          </div>

        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>