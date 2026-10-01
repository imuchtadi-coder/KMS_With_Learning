<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  tenant: {
    type: Object,
    required: true,
  },

  branding: {
    type: Object,
    default: () => ({
      agency_name: '',
      tagline: '',
      logo_path: null,
      certificate_logo_path: null,
      logo_url: null,
      certificate_logo_url: null,
    }),
  },
})

const page = usePage()

const logoInput = ref(null)
const certificateLogoInput = ref(null)
const removeLogo = ref(false)
const removeCertificateLogo = ref(false)

const form = useForm({
  agency_name: props.branding.agency_name || '',
  tagline: props.branding.tagline || '',
  logo: null,
  certificate_logo: null,
  remove_logo: false,
  remove_certificate_logo: false
})

const logoPreview = ref(props.branding.logo_url || (props.branding.logo_path
    ? `/storage/${props.branding.logo_path}`
    : null))
const certificateLogoPreview = ref(
  props.branding.certificate_logo_url || (props.branding.certificate_logo_path
    ? `/storage/${props.branding.certificate_logo_path}`
    : null)
)

const flashSuccess = computed(() => page.props.flash?.success || '')

const selectLogo = () => {
  logoInput.value?.click()
}

const selectCertificateLogo = () => {
  certificateLogoInput.value?.click()
}

const handleLogoChange = event => {
  const file = event.target.files?.[0]

  if (!file) {
    return
  }

  form.logo = file
  logoPreview.value = URL.createObjectURL(file)
  removeLogo.value = false
  logoPreview.value = URL.createObjectURL(file)
}

const handleCertificateLogoChange = event => {
  const file = event.target.files?.[0]

  if (!file) {
    return
  }

  form.certificate_logo = file
  certificateLogoPreview.value = URL.createObjectURL(file)
  removeCertificateLogo.value = false
  certificateLogoPreview.value = URL.createObjectURL(file)
}

const submit = () => {
  form
    .transform(data => ({
      ...data,
      _method: 'put',
    }))
    .post(route('admin.branding.update'), {
      forceFormData: true,
      preserveScroll: true,
    })
}

const deleteLogo = () => {
  form.logo = null
  form.remove_logo = true
  removeLogo.value = true
  logoPreview.value = null

  if (logoInput.value) {
    logoInput.value.value = ''
  }
}

const deleteCertificateLogo = () => {
  form.certificate_logo = null
  form.remove_certificate_logo = true
  removeCertificateLogo.value = true
  certificateLogoPreview.value = null

  if (certificateLogoInput.value) {
    certificateLogoInput.value.value = ''
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">
        Branding
      </h4>

      <p class="text-body-2 text-medium-emphasis">
        Manage branding configuration for {{ tenant.name }}
      </p>
    </div>

    <VAlert
      v-if="flashSuccess"
      type="success"
      variant="tonal"
      class="mb-6"
    >
      {{ flashSuccess }}
    </VAlert>

    <VCard
      flat
      border
      max-width="900"
    >
      <VCardText>
        <VForm @submit.prevent="submit">
          <VRow>
            <!-- Agency Name -->
            <VCol cols="12">
              <VTextField
                v-model="form.agency_name"
                label="Agency Name"
                placeholder="Enter agency or organization name"
                :error-messages="form.errors.agency_name"
              />
            </VCol>

            <!-- Tagline -->
            <VCol cols="12">
              <VTextField
                v-model="form.tagline"
                label="Tagline"
                placeholder="Enter organization tagline"
                :error-messages="form.errors.tagline"
              />
            </VCol>

            <!-- Main Logo -->
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-subtitle-1 font-weight-medium mb-2">
                Main Logo
              </div>

              <div
                class="border rounded pa-4 d-flex flex-column align-center justify-center"
                style="min-height: 220px;"
              >
                <img
                v-if="logoPreview"
                :src="logoPreview"
                alt="Main Logo"
                class="branding-logo mb-4"
                >

                <div
                  v-else
                  class="text-body-2 text-medium-emphasis mb-4"
                >
                  No logo uploaded
                </div>

                <div class="d-flex ga-2">
                    <VBtn
                        variant="outlined"
                        prepend-icon="tabler-upload"
                        @click="selectLogo"
                    >
                        Choose Logo
                    </VBtn>

                    <VBtn
                        v-if="logoPreview"
                        variant="outlined"
                        color="error"
                        prepend-icon="tabler-trash"
                        @click="deleteLogo"
                    >
                        Remove Logo
                    </VBtn>
                </div>

                <input
                  ref="logoInput"
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="d-none"
                  @change="handleLogoChange"
                >

                <!-- <VBtn
                  variant="outlined"
                  prepend-icon="tabler-upload"
                  @click="selectLogo"
                >
                  Choose Logo
                </VBtn> -->
              </div>

              <div class="text-caption text-medium-emphasis mt-2">
                JPG, JPEG, PNG, or WEBP. Maximum 2 MB.
              </div>

              <div
                v-if="form.errors.logo"
                class="text-error text-caption mt-1"
              >
                {{ form.errors.logo }}
              </div>
            </VCol>

            <!-- Certificate Logo -->
            <VCol
              cols="12"
              md="6"
            >
              <div class="text-subtitle-1 font-weight-medium mb-2">
                Certificate Logo
              </div>

              <div
                class="border rounded pa-4 d-flex flex-column align-center justify-center"
                style="min-height: 220px;"
              >
                <img
                v-if="certificateLogoPreview"
                :src="certificateLogoPreview"
                alt="Certificate Logo"
                class="branding-logo mb-4"
                >

                <div
                  v-else
                  class="text-body-2 text-medium-emphasis mb-4"
                >
                  No certificate logo uploaded
                </div>

                <div class="d-flex ga-2">
                    <VBtn
                        variant="outlined"
                        prepend-icon="tabler-upload"
                        @click="selectCertificateLogo"
                    >
                        Choose Certificate Logo
                    </VBtn>

                    <VBtn
                        v-if="certificateLogoPreview"
                        variant="outlined"
                        color="error"
                        prepend-icon="tabler-trash"
                        @click="deleteCertificateLogo"
                    >
                        Remove Logo
                    </VBtn>
                </div>

                <input
                  ref="certificateLogoInput"
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  class="d-none"
                  @change="handleCertificateLogoChange"
                >
              </div>

              <div class="text-caption text-medium-emphasis mt-2">
                JPG, JPEG, PNG, or WEBP. Maximum 2 MB.
              </div>

              <div
                v-if="form.errors.certificate_logo"
                class="text-error text-caption mt-1"
              >
                {{ form.errors.certificate_logo }}
              </div>
            </VCol>

            <!-- Buttons -->
            <VCol
              cols="12"
              class="d-flex ga-2 mt-2"
            >
              <VBtn
                type="submit"
                color="primary"
                :loading="form.processing"
                :disabled="form.processing"
              >
                Save Branding
              </VBtn>

              <VBtn
                variant="outlined"
                :href="route('admin.dashboard')"
              >
                Cancel
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>