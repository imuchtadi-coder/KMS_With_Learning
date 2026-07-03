<script setup>
import { useForm, usePage } from '@inertiajs/vue3'

const flash = usePage().props.flash

const form = useForm({
  mail_from_address: '',
  mail_from_name: '',
  mail_host: '',
  mail_port: '',
  mail_username: '',
  mail_password: '',
  mail_encryption: '',
})

const submit = () => {
  form.put(route('admin.mail-settings.update'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Mail Settings</h4>
      <p class="text-body-2 text-medium-emphasis">Configure SMTP email settings for your tenant</p>
    </div>

    <VAlert v-if="flash?.success" type="success" closable :text="flash.success" class="mb-4" />

    <VCard flat border max-width="700">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField v-model="form.mail_from_address" label="From Address (email)" :error-messages="form.errors.mail_from_address" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.mail_from_name" label="From Name" :error-messages="form.errors.mail_from_name" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.mail_host" label="SMTP Host" :error-messages="form.errors.mail_host" />
            </VCol>
            <VCol cols="12" md="3">
              <VTextField v-model.number="form.mail_port" label="Port" type="number" :error-messages="form.errors.mail_port" />
            </VCol>
            <VCol cols="12" md="3">
              <VSelect
                v-model="form.mail_encryption"
                :items="[{ title: 'None', value: '' }, { title: 'TLS', value: 'tls' }, { title: 'SSL', value: 'ssl' }]"
                label="Encryption"
                clearable
                :error-messages="form.errors.mail_encryption"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.mail_username" label="Username" :error-messages="form.errors.mail_username" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.mail_password" label="Password" type="password" :error-messages="form.errors.mail_password" />
            </VCol>
            <VCol cols="12" class="d-flex ga-2">
              <VBtn type="submit" color="primary" :loading="form.processing">Save Settings</VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
