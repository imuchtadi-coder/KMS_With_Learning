<script setup>
import { useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const flash = page.props.flash

const form = useForm({
  csv: null,
})

const submit = () => {
  form.post(route('admin.bulk.import-users'), {
    forceFormData: true,

    onSuccess: () => {
      form.reset()
    },
  })
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">
        Import Users
      </h4>

      <p class="text-body-2 text-medium-emphasis">
        Bulk import users from a CSV file
      </p>
    </div>

    <VAlert
      v-if="flash?.success"
      type="success"
      closable
      :text="flash.success"
      class="mb-4"
    />

    <VAlert
      v-if="flash?.import_errors?.length"
      type="warning"
      closable
      class="mb-4"
    >
      <div
        v-for="(err, index) in flash.import_errors"
        :key="index"
      >
        {{ err }}
      </div>
    </VAlert>

    <VCard flat border max-width="600">
      <VCardText>
        <p class="text-body-2 mb-4">
          CSV must have headers:
          <code>name, email, password, role, department</code>.
          <br>
          Role must be
          <code>student</code>
          or
          <code>instructor</code>.
        </p>

        <VForm @submit.prevent="submit">
          <VFileInput
            v-model="form.csv"
            label="CSV File"
            accept=".csv,.txt"
            :error-messages="form.errors.csv"
            required
            class="mb-4"
          />

          <VBtn
            type="submit"
            color="primary"
            :loading="form.processing"
          >
            Import Users
          </VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>