```vue
<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

console.log('=== SETTINGS.VUE TERLOAD ===')

const page = usePage()

const flash = computed(() => page.props.flash)
const mailSettings = computed(() => page.props.mailSettings)

const form = useForm({
  mail_from_address: mailSettings.value?.mail_from_address ?? '',
  mail_from_name: mailSettings.value?.mail_from_name ?? '',
  mail_host: mailSettings.value?.mail_host ?? '',
  mail_port: mailSettings.value?.mail_port ?? '',
  mail_username: mailSettings.value?.mail_username ?? '',
  mail_password: '',
  mail_encryption: mailSettings.value?.mail_encryption ?? '',
})

const submit = () => {
  console.log('================================')
  console.log('BUTTON SAVE DIKLIK')
  console.log('FORM DATA:', form.data())
  console.log('================================')

  form.put(route('admin.mail-settings.update'), {
    onStart: () => {
      console.log('PUT START')
    },

    onSuccess: (page) => {
      console.log('================================')
      console.log('PUT SUCCESS')
      console.log('PAGE PROPS:', page.props)
      console.log('FLASH:', page.props.flash)
      console.log('================================')
    },

    onError: (errors) => {
      console.log('================================')
      console.log('PUT ERROR')
      console.log('ERRORS:', errors)
      console.log('================================')
    },

    onFinish: () => {
      console.log('================================')
      console.log('PUT FINISHED')
      console.log('================================')
    },
  })
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">
        Mail Settings
      </h4>

      <p class="text-body-2 text-medium-emphasis">
        Configure SMTP email settings for your tenant
      </p>
    </div>

    <!-- Success Message -->
    <VAlert
      v-if="flash?.success"
      type="success"
      closable
      :text="flash.success"
      class="mb-4"
    />

    <!-- Error Message -->
    <VAlert
      v-if="flash?.error"
      type="error"
      closable
      :text="flash.error"
      class="mb-4"
    />

    <VCard
      flat
      border
      max-width="700"
    >
      <VCardText>

        <!--
          Untuk sementara kita tidak menggunakan submit event.
          Submit dipanggil langsung dari tombol.
        -->
        <VForm>
          <VRow>

            <!-- From Address -->
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.mail_from_address"
                label="From Address (email)"
                :error-messages="form.errors.mail_from_address"
              />
            </VCol>

            <!-- From Name -->
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.mail_from_name"
                label="From Name"
                :error-messages="form.errors.mail_from_name"
              />
            </VCol>

            <!-- SMTP Host -->
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.mail_host"
                label="SMTP Host"
                :error-messages="form.errors.mail_host"
              />
            </VCol>

            <!-- SMTP Port -->
            <VCol
              cols="12"
              md="3"
            >
              <VTextField
                v-model.number="form.mail_port"
                label="Port"
                type="number"
                :error-messages="form.errors.mail_port"
              />
            </VCol>

            <!-- Encryption -->
            <VCol
              cols="12"
              md="3"
            >
              <VSelect
                v-model="form.mail_encryption"
                :items="[
                  { title: 'None', value: '' },
                  { title: 'TLS', value: 'tls' },
                  { title: 'SSL', value: 'ssl' }
                ]"
                label="Encryption"
                clearable
                :error-messages="form.errors.mail_encryption"
              />
            </VCol>

            <!-- Username -->
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.mail_username"
                label="Username"
                :error-messages="form.errors.mail_username"
              />
            </VCol>

            <!-- Password -->
            <VCol
              cols="12"
              md="6"
            >
              <VTextField
                v-model="form.mail_password"
                label="Password"
                type="password"
                :error-messages="form.errors.mail_password"
              />
            </VCol>

            <!-- Save Button -->
            <VCol
              cols="12"
              class="d-flex ga-2"
            >
              <VBtn
                type="button"
                color="primary"
                :loading="form.processing"
                @click="submit"
              >
                Save Settings
              </VBtn>
            </VCol>

          </VRow>
        </VForm>

      </VCardText>
    </VCard>
  </div>
</template>
```
