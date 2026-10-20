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
  whatsapp: {
    type: Object,
    default: () => ({
      name: 'Customer Service KIT',
      phone_number: '',
      is_active: true,
    }),
  },
  is_superadmin: {
    type: Boolean,
    default: false,
  },
})

const page = usePage()

const logoInput = ref(null)
const certificateLogoInput = ref(null)

const form = useForm({
  agency_name: props.branding?.agency_name || '',
  tagline: props.branding?.tagline || '',
  logo: null,
  certificate_logo: null,
  remove_logo: false,
  remove_certificate_logo: false,
  whatsapp_name: props.whatsapp?.name || 'Customer Service KIT',
  whatsapp_phone_number: props.whatsapp?.phone_number || '',
  whatsapp_is_active: props.whatsapp?.is_active ?? true,
})

const logoPreview = ref(
  props.branding.logo_url || (props.branding.logo_path ? `/storage/${props.branding.logo_path}` : null)
)
const certificateLogoPreview = ref(
  props.branding.certificate_logo_url || (props.branding.certificate_logo_path ? `/storage/${props.branding.certificate_logo_path}` : null)
)

const activePreviewTab = ref('navbar')
const isDraggingLogo = ref(false)
const isDraggingCert = ref(false)
const clientError = ref('')

const flashSuccess = computed(() => page.props.flash?.success || '')
const flashError = computed(() => page.props.flash?.error || '')

const selectLogo = () => {
  logoInput.value?.click()
}

const selectCertificateLogo = () => {
  certificateLogoInput.value?.click()
}

const validateAndSetFile = (file, type) => {
  clientError.value = ''
  const validTypes = ['image/jpeg', 'image/png', 'image/webp']
  if (!validTypes.includes(file.type)) {
    clientError.value = 'Format file tidak didukung. Harap gunakan file gambar PNG, JPG, JPEG, atau WEBP.'
    return false
  }
  if (file.size > 5 * 1024 * 1024) {
    clientError.value = 'Ukuran file melebihi batas maksimal 5 MB.'
    return false
  }

  if (type === 'logo') {
    form.logo = file
    logoPreview.value = URL.createObjectURL(file)
    form.remove_logo = false
  } else if (type === 'cert') {
    form.certificate_logo = file
    certificateLogoPreview.value = URL.createObjectURL(file)
    form.remove_certificate_logo = false
  }
  return true
}

const handleLogoChange = event => {
  const file = event.target.files?.[0]
  if (!file) return
  validateAndSetFile(file, 'logo')
}

const handleCertificateLogoChange = event => {
  const file = event.target.files?.[0]
  if (!file) return
  validateAndSetFile(file, 'cert')
}

const onLogoDrop = event => {
  isDraggingLogo.value = false
  const file = event.dataTransfer.files?.[0]
  if (file) validateAndSetFile(file, 'logo')
}

const onCertDrop = event => {
  isDraggingCert.value = false
  const file = event.dataTransfer.files?.[0]
  if (file) validateAndSetFile(file, 'cert')
}

const deleteLogo = () => {
  form.logo = null
  form.remove_logo = true
  logoPreview.value = null
  if (logoInput.value) {
    logoInput.value.value = ''
  }
}

const deleteCertificateLogo = () => {
  form.certificate_logo = null
  form.remove_certificate_logo = true
  certificateLogoPreview.value = null
  if (certificateLogoInput.value) {
    certificateLogoInput.value.value = ''
  }
}

const cleanWhatsAppNumber = computed(() => {
  let num = (form.whatsapp_phone_number || '').replace(/[^0-9]/g, '')
  if (num.startsWith('0')) {
    num = '62' + num.slice(1)
  }
  return num
})

const testWhatsAppUrl = computed(() => {
  if (!cleanWhatsAppNumber.value) return '#'
  return `https://wa.me/${cleanWhatsAppNumber.value}`
})

const resetForm = () => {
  form.agency_name = props.branding?.agency_name || ''
  form.tagline = props.branding?.tagline || ''
  form.logo = null
  form.certificate_logo = null
  form.remove_logo = false
  form.remove_certificate_logo = false
  form.whatsapp_name = props.whatsapp?.name || 'Customer Service KIT'
  form.whatsapp_phone_number = props.whatsapp?.phone_number || ''
  form.whatsapp_is_active = props.whatsapp?.is_active ?? true
  logoPreview.value = props.branding?.logo_url || (props.branding?.logo_path ? `/storage/${props.branding.logo_path}` : null)
  certificateLogoPreview.value = props.branding?.certificate_logo_url || (props.branding?.certificate_logo_path ? `/storage/${props.branding.certificate_logo_path}` : null)
  clientError.value = ''
}

const submit = () => {
  clientError.value = ''
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
</script>

<template>
  <div class="admin-branding-page pb-12">
    <!-- Header Banner -->
    <VCard flat border class="mb-6 rounded-lg overflow-hidden bg-surface">
      <div class="pa-4 pa-md-5">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-4">
          <div class="d-flex align-center ga-3-5">
            <VAvatar
              size="48"
              color="primary"
              variant="tonal"
              rounded="lg"
              class="elevation-1"
            >
              <VIcon icon="mdi-palette-swatch-outline" size="26" color="primary" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h6 font-weight-bold text-high-emphasis mb-0">
                  Branding & Identitas Visual
                </h4>
                <VChip
                  size="x-small"
                  color="primary"
                  variant="tonal"
                  label
                  class="font-weight-semibold"
                >
                  {{ tenant.name }}
                </VChip>
              </div>
              <p class="text-caption text-medium-emphasis mt-1 mb-0">
                Kustomisasi nama lembaga, logo platform, dan lambang sertifikat kelulusan resmi tenant Anda.
              </p>
            </div>
          </div>

          <!-- Status Pill -->
          <div class="d-flex align-center ga-2">
            <VChip
              v-if="form.isDirty"
              color="warning"
              variant="tonal"
              size="small"
              label
              class="font-weight-medium"
            >
              <VIcon start icon="mdi-alert-circle-outline" size="14" />
              Perubahan Belum Disimpan
            </VChip>
            <VChip
              v-else-if="branding.agency_name || branding.logo_path"
              color="success"
              variant="tonal"
              size="small"
              label
              class="font-weight-medium"
            >
              <VIcon start icon="mdi-check-circle-outline" size="14" />
              Branding Terpasang
            </VChip>
            <VChip
              v-else
              color="secondary"
              variant="tonal"
              size="small"
              label
              class="font-weight-medium"
            >
              <VIcon start icon="mdi-information-outline" size="14" />
              Gunakan Default
            </VChip>
          </div>
        </div>
      </div>
    </VCard>

    <!-- Success & Error Alerts -->
    <VAlert
      v-if="flashSuccess"
      type="success"
      variant="tonal"
      closable
      class="mb-5 rounded-lg"
    >
      <template #prepend>
        <VIcon icon="mdi-check-circle" size="22" />
      </template>
      <div class="font-weight-medium">{{ flashSuccess }}</div>
    </VAlert>

    <VAlert
      v-if="flashError"
      type="error"
      variant="tonal"
      closable
      class="mb-5 rounded-lg"
    >
      <template #prepend>
        <VIcon icon="mdi-alert-circle" size="22" />
      </template>
      <div class="font-weight-medium">{{ flashError }}</div>
    </VAlert>

    <VAlert
      v-if="clientError"
      type="warning"
      variant="tonal"
      closable
      class="mb-5 rounded-lg"
      @click:close="clientError = ''"
    >
      <template #prepend>
        <VIcon icon="mdi-alert-outline" size="22" />
      </template>
      <div class="font-weight-medium">{{ clientError }}</div>
    </VAlert>

    <!-- Main Two-Column Layout -->
    <VForm @submit.prevent="submit">
      <VRow>
        <!-- LEFT COLUMN: Brand Configuration Controls -->
        <VCol cols="12" lg="7" xl="7">
          <div class="d-flex flex-column ga-5">
            <!-- 1. General Identity Card -->
            <VCard flat border class="rounded-lg overflow-hidden bg-surface">
              <VCardItem class="pa-4 pb-2 border-b">
                <template #prepend>
                  <VAvatar color="primary" variant="tonal" size="34" rounded class="me-2">
                    <VIcon icon="mdi-card-account-details-outline" size="18" />
                  </VAvatar>
                </template>
                <VCardTitle class="text-subtitle-1 font-weight-bold">
                  Identitas Lembaga
                </VCardTitle>
                <VCardSubtitle class="text-caption">
                  Informasi nama dan moto resmi yang akan tampil di seluruh antarmuka sistem.
                </VCardSubtitle>
              </VCardItem>

              <VCardText class="pa-4 pt-4">
                <VRow dense class="ga-y-3">
                  <!-- Agency Name -->
                  <VCol cols="12">
                    <label class="text-caption font-weight-bold text-high-emphasis mb-1 d-block">
                      Nama Lembaga / Organisasi
                    </label>
                    <VTextField
                      v-model="form.agency_name"
                      placeholder="Contoh: Akademi Inovasi Digital"
                      prepend-inner-icon="mdi-domain"
                      variant="outlined"
                      density="comfortable"
                      hide-details="auto"
                      clearable
                      persistent-placeholder
                      :error-messages="form.errors.agency_name"
                    />
                    <div class="text-caption text-medium-emphasis mt-1">
                      Nama ini menggantikan nama default platform pada header, footer, dan dokumen.
                    </div>
                  </VCol>

                  <!-- Tagline -->
                  <VCol cols="12">
                    <label class="text-caption font-weight-bold text-high-emphasis mb-1 d-block">
                      Tagline / Moto Lembaga
                    </label>
                    <VTextField
                      v-model="form.tagline"
                      placeholder="Contoh: Menyiapkan Talenta Digital Unggul Masa Depan"
                      prepend-inner-icon="mdi-format-quote-close"
                      variant="outlined"
                      density="comfortable"
                      hide-details="auto"
                      clearable
                      persistent-placeholder
                      :error-messages="form.errors.tagline"
                    />
                    <div class="text-caption text-medium-emphasis mt-1">
                      Slogan singkat lembaga yang akan terlihat di bawah nama instansi pada halaman masuk dan header.
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- 2. Main Platform Logo Card -->
            <VCard flat border class="rounded-lg overflow-hidden bg-surface">
              <VCardItem class="pa-4 pb-2 border-b">
                <template #prepend>
                  <VAvatar color="info" variant="tonal" size="34" rounded class="me-2">
                    <VIcon icon="mdi-image-outline" size="18" />
                  </VAvatar>
                </template>
                <VCardTitle class="text-subtitle-1 font-weight-bold">
                  Logo Utama Platform
                </VCardTitle>
                <VCardSubtitle class="text-caption">
                  Tampil pada navigasi atas (navbar), sidebar menu, dan portal login peserta.
                </VCardSubtitle>
              </VCardItem>

              <VCardText class="pa-4 pt-4">
                <!-- Dropzone Box -->
                <div
                  class="dropzone-box rounded-lg pa-4 d-flex flex-column align-center justify-center text-center transition-all cursor-pointer"
                  :class="{ 'dropzone-active': isDraggingLogo }"
                  @dragover.prevent="isDraggingLogo = true"
                  @dragleave.prevent="isDraggingLogo = false"
                  @drop.prevent="onLogoDrop"
                  @click="!logoPreview ? selectLogo() : null"
                >
                  <!-- If Logo Exists -->
                  <template v-if="logoPreview">
                    <div class="preview-canvas-wrapper pa-3 rounded border mb-3 checkerboard-bg">
                      <img
                        :src="logoPreview"
                        alt="Preview Logo Utama"
                        class="branding-logo-img"
                      >
                    </div>

                    <div class="d-flex align-center ga-2 mb-2 flex-wrap justify-center">
                      <VChip size="x-small" color="success" variant="tonal" label class="font-weight-medium">
                        <VIcon start icon="mdi-check" size="12" />
                        Logo Siap
                      </VChip>
                      <VChip size="x-small" color="primary" variant="tonal" label>
                        PNG / JPG / WEBP
                      </VChip>
                    </div>

                    <div class="d-flex align-center ga-2 mt-1">
                      <VBtn
                        size="small"
                        density="comfortable"
                        variant="tonal"
                        color="primary"
                        prepend-icon="mdi-upload"
                        @click.stop="selectLogo"
                      >
                        Ganti Logo
                      </VBtn>
                      <VBtn
                        size="small"
                        density="comfortable"
                        variant="tonal"
                        color="error"
                        prepend-icon="mdi-trash-can-outline"
                        @click.stop="deleteLogo"
                      >
                        Hapus Logo
                      </VBtn>
                    </div>
                  </template>

                  <!-- If No Logo Uploaded -->
                  <template v-else>
                    <VAvatar color="primary" variant="tonal" size="52" rounded="circle" class="mb-3">
                      <VIcon icon="mdi-cloud-upload-outline" size="28" />
                    </VAvatar>
                    <div class="text-subtitle-2 font-weight-bold text-high-emphasis mb-1">
                      Pilih atau Seret Logo ke Area Ini
                    </div>
                    <div class="text-caption text-medium-emphasis mb-3" style="max-width: 320px;">
                      Format yang didukung: PNG, JPG, JPEG, WEBP. Maksimal 5 MB. Rasio lanskap transparan disarankan.
                    </div>
                    <VBtn
                      size="small"
                      density="comfortable"
                      variant="elevated"
                      color="primary"
                      prepend-icon="mdi-folder-upload-outline"
                      @click.stop="selectLogo"
                    >
                      Pilih File Logo
                    </VBtn>
                  </template>

                  <!-- Hidden Input -->
                  <input
                    ref="logoInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="d-none"
                    @change="handleLogoChange"
                  >
                </div>

                <div
                  v-if="form.errors.logo"
                  class="text-error text-caption mt-2 d-flex align-center ga-1"
                >
                  <VIcon icon="mdi-alert-circle" size="14" />
                  {{ form.errors.logo }}
                </div>
              </VCardText>
            </VCard>

            <!-- 3. Certificate Logo Card -->
            <VCard flat border class="rounded-lg overflow-hidden bg-surface">
              <VCardItem class="pa-4 pb-2 border-b">
                <template #prepend>
                  <VAvatar color="warning" variant="tonal" size="34" rounded class="me-2">
                    <VIcon icon="mdi-seal-variant" size="18" />
                  </VAvatar>
                </template>
                <VCardTitle class="text-subtitle-1 font-weight-bold">
                  Logo Resmi Sertifikat
                </VCardTitle>
                <VCardSubtitle class="text-caption">
                  Digunakan sebagai stempel atau lambang instansi pada sertifikat kelulusan resmi siswa.
                </VCardSubtitle>
              </VCardItem>

              <VCardText class="pa-4 pt-4">
                <!-- Dropzone Box -->
                <div
                  class="dropzone-box rounded-lg pa-4 d-flex flex-column align-center justify-center text-center transition-all cursor-pointer"
                  :class="{ 'dropzone-active': isDraggingCert }"
                  @dragover.prevent="isDraggingCert = true"
                  @dragleave.prevent="isDraggingCert = false"
                  @drop.prevent="onCertDrop"
                  @click="!certificateLogoPreview ? selectCertificateLogo() : null"
                >
                  <!-- If Certificate Logo Exists -->
                  <template v-if="certificateLogoPreview">
                    <div class="preview-canvas-wrapper pa-3 rounded border mb-3 checkerboard-bg">
                      <img
                        :src="certificateLogoPreview"
                        alt="Preview Logo Sertifikat"
                        class="branding-logo-img"
                      >
                    </div>

                    <div class="d-flex align-center ga-2 mb-2 flex-wrap justify-center">
                      <VChip size="x-small" color="warning" variant="tonal" label class="font-weight-medium">
                        <VIcon start icon="mdi-seal" size="12" />
                        Logo Sertifikat Siap
                      </VChip>
                      <VChip size="x-small" color="primary" variant="tonal" label>
                        PNG / JPG / WEBP
                      </VChip>
                    </div>

                    <div class="d-flex align-center ga-2 mt-1">
                      <VBtn
                        size="small"
                        density="comfortable"
                        variant="tonal"
                        color="primary"
                        prepend-icon="mdi-upload"
                        @click.stop="selectCertificateLogo"
                      >
                        Ganti Logo
                      </VBtn>
                      <VBtn
                        size="small"
                        density="comfortable"
                        variant="tonal"
                        color="error"
                        prepend-icon="mdi-trash-can-outline"
                        @click.stop="deleteCertificateLogo"
                      >
                        Hapus Logo
                      </VBtn>
                    </div>
                  </template>

                  <!-- If No Certificate Logo Uploaded -->
                  <template v-else>
                    <VAvatar color="warning" variant="tonal" size="52" rounded="circle" class="mb-3">
                      <VIcon icon="mdi-certificate-outline" size="28" />
                    </VAvatar>
                    <div class="text-subtitle-2 font-weight-bold text-high-emphasis mb-1">
                      Pilih atau Seret Logo Sertifikat
                    </div>
                    <div class="text-caption text-medium-emphasis mb-3" style="max-width: 320px;">
                      Format: PNG, JPG, JPEG, WEBP. Maksimal 5 MB. Rasio 1:1 atau lambang stempel resmi beresolusi tinggi disarankan.
                    </div>
                    <VBtn
                      size="small"
                      density="comfortable"
                      variant="elevated"
                      color="warning"
                      prepend-icon="mdi-folder-upload-outline"
                      @click.stop="selectCertificateLogo"
                    >
                      Pilih Logo Sertifikat
                    </VBtn>
                  </template>

                  <!-- Hidden Input -->
                  <input
                    ref="certificateLogoInput"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="d-none"
                    @change="handleCertificateLogoChange"
                  >
                </div>

                <div
                  v-if="form.errors.certificate_logo"
                  class="text-error text-caption mt-2 d-flex align-center ga-1"
                >
                  <VIcon icon="mdi-alert-circle" size="14" />
                  {{ form.errors.certificate_logo }}
                </div>
              </VCardText>
            </VCard>

            <!-- 4. WhatsApp Customer Service Card -->
            <VCard flat border class="rounded-lg overflow-hidden bg-surface">
              <VCardItem class="pa-4 pb-2 border-b">
                <template #prepend>
                  <VAvatar color="success" variant="tonal" size="34" rounded class="me-2">
                    <VIcon icon="mdi-whatsapp" size="20" color="success" />
                  </VAvatar>
                </template>
                <VCardTitle class="text-subtitle-1 font-weight-bold d-flex align-center justify-space-between flex-wrap ga-2">
                  <span>Kontak Layanan Pelanggan (WhatsApp)</span>
                  <VChip
                    :color="form.whatsapp_is_active ? 'success' : 'error'"
                    size="x-small"
                    variant="tonal"
                    label
                    class="font-weight-medium"
                  >
                    {{ form.whatsapp_is_active ? 'Status: Aktif' : 'Status: Nonaktif' }}
                  </VChip>
                </VCardTitle>
                <VCardSubtitle class="text-caption">
                  Pengaturan nomor WhatsApp tombol "Layanan Pelanggan" di menu profil, registrasi tenant, dan landing page.
                </VCardSubtitle>
              </VCardItem>

              <VCardText class="pa-4 pt-4">
                <VRow dense class="ga-y-3">
                  <!-- Active Toggle -->
                  <VCol cols="12">
                    <VSwitch
                      v-model="form.whatsapp_is_active"
                      color="success"
                      density="compact"
                      hide-details
                      inset
                      label="Aktifkan Tautan Layanan Pelanggan di Seluruh Aplikasi"
                    />
                  </VCol>

                  <!-- WhatsApp Number Input -->
                  <VCol cols="12" md="6">
                    <label class="text-caption font-weight-bold text-high-emphasis mb-1 d-block">
                      Nomor WhatsApp CS
                    </label>
                    <VTextField
                      v-model="form.whatsapp_phone_number"
                      placeholder="Contoh: 6281110555605 atau 081110555605"
                      prepend-inner-icon="mdi-whatsapp"
                      variant="outlined"
                      density="comfortable"
                      hide-details="auto"
                      clearable
                      persistent-placeholder
                      :error-messages="form.errors.whatsapp_phone_number"
                    />
                    <div class="text-caption text-medium-emphasis mt-1">
                      Format: diawali <code>62</code> atau <code>08</code> (spasi dan tanda minus otomatis dibersihkan).
                    </div>
                  </VCol>

                  <!-- CS Label Name -->
                  <VCol cols="12" md="6">
                    <label class="text-caption font-weight-bold text-high-emphasis mb-1 d-block">
                      Nama Layanan / Label CS
                    </label>
                    <VTextField
                      v-model="form.whatsapp_name"
                      placeholder="Contoh: Customer Service KIT"
                      prepend-inner-icon="mdi-account-headset"
                      variant="outlined"
                      density="comfortable"
                      hide-details="auto"
                      clearable
                      persistent-placeholder
                      :error-messages="form.errors.whatsapp_name"
                    />
                    <div class="text-caption text-medium-emphasis mt-1">
                      Nama atau keterangan kontak bantuan pelanggan.
                    </div>
                  </VCol>

                  <!-- Quick Test Link -->
                  <VCol cols="12" v-if="cleanWhatsAppNumber">
                    <div class="d-flex align-center justify-space-between flex-wrap ga-2 pa-3 rounded border bg-surface-variant-subtle">
                      <div class="d-flex align-center ga-2">
                        <VIcon icon="mdi-link-variant" size="18" color="success" />
                        <span class="text-caption text-medium-emphasis">
                          Tautan aktif: <strong class="text-high-emphasis">https://wa.me/{{ cleanWhatsAppNumber }}</strong>
                        </span>
                      </div>
                      <VBtn
                        size="small"
                        color="success"
                        variant="tonal"
                        prepend-icon="mdi-open-in-new"
                        :href="testWhatsAppUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        Uji Coba Tautan WhatsApp
                      </VBtn>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- 5. Form Action Buttons -->
            <VCard flat border class="rounded-lg pa-4 bg-surface">
              <div class="d-flex align-center justify-space-between flex-wrap ga-3">
                <div class="d-flex align-center ga-2">
                  <VBtn
                    type="submit"
                    color="primary"
                    variant="elevated"
                    size="default"
                    class="font-weight-semibold px-5"
                    prepend-icon="mdi-content-save-outline"
                    :loading="form.processing"
                    :disabled="form.processing"
                  >
                    Simpan Pengaturan
                  </VBtn>

                  <VBtn
                    variant="tonal"
                    color="default"
                    size="default"
                    class="font-weight-medium"
                    prepend-icon="mdi-undo-variant"
                    :disabled="form.processing"
                    @click="resetForm"
                  >
                    Reset
                  </VBtn>
                </div>

                <VBtn
                  variant="text"
                  color="secondary"
                  size="default"
                  prepend-icon="mdi-arrow-left"
                  :href="route('admin.dashboard')"
                >
                  Dashboard
                </VBtn>
              </div>
            </VCard>
          </div>
        </VCol>

        <!-- RIGHT COLUMN: Interactive Live Real-Time Visualizer -->
        <VCol cols="12" lg="5" xl="5">
          <div class="preview-panel-sticky">
            <VCard flat border class="rounded-lg overflow-hidden bg-surface">
              <VCardItem class="pa-4 pb-2 border-b">
                <template #prepend>
                  <VAvatar color="secondary" variant="tonal" size="34" rounded class="me-2">
                    <VIcon icon="mdi-eye-outline" size="18" />
                  </VAvatar>
                </template>
                <VCardTitle class="text-subtitle-1 font-weight-bold">
                  Pratinjau Langsung
                </VCardTitle>
                <VCardSubtitle class="text-caption">
                  Simulasi interaktif tampilan brand secara real-time.
                </VCardSubtitle>
              </VCardItem>

              <!-- Preview Tabs Bar -->
              <div class="border-b px-2 bg-background">
                <VTabs
                  v-model="activePreviewTab"
                  density="compact"
                  color="primary"
                  grow
                >
                  <VTab value="navbar" class="text-capitalize text-caption font-weight-semibold">
                    <VIcon start icon="mdi-page-layout-header" size="16" />
                    Navbar
                  </VTab>
                  <VTab value="certificate" class="text-capitalize text-caption font-weight-semibold">
                    <VIcon start icon="mdi-certificate-outline" size="16" />
                    Sertifikat
                  </VTab>
                  <VTab value="login" class="text-capitalize text-caption font-weight-semibold">
                    <VIcon start icon="mdi-login" size="16" />
                    Login
                  </VTab>
                  <VTab value="whatsapp" class="text-capitalize text-caption font-weight-semibold">
                    <VIcon start icon="mdi-whatsapp" size="16" color="success" />
                    Layanan CS
                  </VTab>
                </VTabs>
              </div>

              <!-- Preview Content Frame -->
              <VCardText class="pa-4 bg-background">
                <!-- 1. Navbar Mockup View -->
                <div v-if="activePreviewTab === 'navbar'" class="mockup-container">
                  <div class="mockup-window rounded-lg border bg-surface overflow-hidden elevation-1">
                    <!-- Browser Dot Controls -->
                    <div class="pa-2 px-3 border-b bg-surface-variant-subtle d-flex align-center justify-space-between">
                      <div class="d-flex align-center ga-1-5">
                        <span class="window-dot dot-red" />
                        <span class="window-dot dot-yellow" />
                        <span class="window-dot dot-green" />
                      </div>
                      <span class="text-caption text-disabled" style="font-size: 0.68rem !important;">
                        lms.{{ tenant.id || 'domain' }}.com
                      </span>
                      <div style="width: 32px;" />
                    </div>

                    <!-- Simulated Navbar Header -->
                    <div class="pa-3 border-b bg-surface d-flex align-center justify-space-between ga-2">
                      <div class="d-flex align-center ga-2 text-truncate" style="min-width: 0;">
                        <img
                          v-if="logoPreview"
                          :src="logoPreview"
                          alt="Navbar Brand"
                          class="mockup-nav-logo"
                        >
                        <VAvatar
                          v-else
                          size="30"
                          color="primary"
                          variant="tonal"
                          rounded
                        >
                          <VIcon icon="mdi-school-outline" size="18" />
                        </VAvatar>

                        <div class="text-truncate">
                          <div class="text-caption font-weight-bold text-high-emphasis text-truncate lh-1">
                            {{ form.agency_name || tenant.name || 'Nama Lembaga' }}
                          </div>
                          <div
                            v-if="form.tagline"
                            class="text-disabled text-truncate mt-0-5"
                            style="font-size: 0.65rem !important;"
                          >
                            {{ form.tagline }}
                          </div>
                        </div>
                      </div>

                      <div class="d-none d-sm-flex align-center ga-2 flex-shrink-0">
                        <span class="text-caption text-primary font-weight-semibold" style="font-size: 0.72rem !important;">Kursus</span>
                        <span class="text-caption text-medium-emphasis" style="font-size: 0.72rem !important;">Siswa</span>
                        <VAvatar size="22" color="primary" variant="tonal" class="ms-1">
                          <span style="font-size: 0.65rem !important;">A</span>
                        </VAvatar>
                      </div>
                    </div>

                    <!-- Mockup Page Body -->
                    <div class="pa-4 bg-background">
                      <div class="pa-3 rounded border bg-surface mb-2">
                        <div class="d-flex align-center ga-2 mb-2">
                          <VAvatar size="20" color="primary" variant="tonal" />
                          <div class="skeleton-line" style="width: 50%;" />
                        </div>
                        <div class="skeleton-line mb-1" style="width: 80%;" />
                        <div class="skeleton-line" style="width: 40%;" />
                      </div>
                    </div>
                  </div>

                  <div class="d-flex align-center ga-1-5 mt-3 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-information-outline" size="14" color="primary" />
                    <span>Logo utama dan nama lembaga tampil di navigasi atas setiap halaman siswa.</span>
                  </div>
                </div>

                <!-- 2. Certificate Mockup View -->
                <div v-else-if="activePreviewTab === 'certificate'" class="mockup-container">
                  <div class="certificate-mockup rounded-lg border pa-4 bg-surface elevation-1 text-center position-relative overflow-hidden">
                    <!-- Subtle Gold Border Ornament -->
                    <div class="certificate-inner-border pa-3 rounded">
                      <!-- Certificate Seal / Logo -->
                      <div class="mb-2 d-flex justify-center">
                        <img
                          v-if="certificateLogoPreview"
                          :src="certificateLogoPreview"
                          alt="Cert Seal"
                          class="mockup-cert-logo"
                        >
                        <img
                          v-else-if="logoPreview"
                          :src="logoPreview"
                          alt="Cert Seal Fallback"
                          class="mockup-cert-logo opacity-85"
                        >
                        <VAvatar
                          v-else
                          size="42"
                          color="warning"
                          variant="tonal"
                          rounded="circle"
                        >
                          <VIcon icon="mdi-seal-variant" size="24" color="warning" />
                        </VAvatar>
                      </div>

                      <!-- Agency Name on Certificate -->
                      <div class="text-caption font-weight-bold text-primary letter-spacing-1 text-uppercase text-truncate mb-1">
                        {{ form.agency_name || tenant.name || 'NAMA LEMBAGA RESMI' }}
                      </div>

                      <div class="text-caption text-disabled font-weight-medium mb-2" style="font-size: 0.62rem !important; letter-spacing: 0.8px;">
                        SERTIFIKAT KELULUSAN RESMI
                      </div>

                      <!-- Recipient -->
                      <div class="text-caption font-italic text-medium-emphasis mb-0-5" style="font-size: 0.68rem !important;">
                        Diberikan dengan hormat kepada:
                      </div>
                      <div class="text-subtitle-2 font-weight-bold text-high-emphasis text-truncate mb-1-5">
                        Ahmad Rizky Pratama
                      </div>

                      <!-- Course Name -->
                      <div class="text-caption text-medium-emphasis text-truncate mb-3" style="font-size: 0.7rem !important;">
                        Atas kelulusan pada program kompetensi resmi
                      </div>

                      <!-- Stamp & Signatures -->
                      <div class="d-flex justify-space-between align-center pt-2 border-t px-2">
                        <div class="text-start">
                          <div class="signature-line mb-1" />
                          <div class="text-caption text-disabled font-weight-bold" style="font-size: 0.6rem !important;">
                            Direktur Lembaga
                          </div>
                        </div>

                        <VAvatar size="26" color="warning" variant="tonal">
                          <VIcon icon="mdi-check-decagram" size="16" color="warning" />
                        </VAvatar>

                        <div class="text-end">
                          <div class="signature-line mb-1 ms-auto" />
                          <div class="text-caption text-disabled font-weight-bold" style="font-size: 0.6rem !important;">
                            Verifikasi Resmi
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex align-center ga-1-5 mt-3 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-information-outline" size="14" color="warning" />
                    <span>Logo sertifikat dicetak dengan resolusi tinggi pada lembar sertifikat kelulusan.</span>
                  </div>
                </div>

                <!-- 3. Login Portal Mockup View -->
                <div v-else-if="activePreviewTab === 'login'" class="mockup-container">
                  <div class="login-mockup rounded-lg border pa-4 bg-surface elevation-1 text-center">
                    <div class="mb-3 d-flex justify-center">
                      <img
                        v-if="logoPreview"
                        :src="logoPreview"
                        alt="Login Brand"
                        class="mockup-login-logo"
                      >
                      <VAvatar
                        v-else
                        size="44"
                        color="primary"
                        variant="tonal"
                        rounded="lg"
                      >
                        <VIcon icon="mdi-shield-check" size="24" />
                      </VAvatar>
                    </div>

                    <div class="text-subtitle-2 font-weight-bold text-high-emphasis text-truncate mb-0-5">
                      {{ form.agency_name || tenant.name || 'Portal Pembelajaran' }}
                    </div>

                    <div class="text-caption text-medium-emphasis text-truncate mb-3" style="font-size: 0.72rem !important;">
                      {{ form.tagline || 'Selamat datang di ruang belajar digital Anda' }}
                    </div>

                    <!-- Dummy Form Inputs -->
                    <div class="d-flex flex-column ga-2 mb-3 text-start">
                      <div class="dummy-input rounded px-2-5 py-1-5 border bg-background text-caption text-disabled d-flex align-center ga-1-5">
                        <VIcon icon="mdi-email-outline" size="14" />
                        <span style="font-size: 0.72rem !important;">nama@email.com</span>
                      </div>
                      <div class="dummy-input rounded px-2-5 py-1-5 border bg-background text-caption text-disabled d-flex align-center ga-1-5">
                        <VIcon icon="mdi-lock-outline" size="14" />
                        <span style="font-size: 0.72rem !important;">••••••••</span>
                      </div>
                    </div>

                    <VBtn
                      block
                      size="small"
                      color="primary"
                      variant="flat"
                      class="font-weight-semibold text-capitalize"
                      disabled
                    >
                      Masuk ke Akun
                    </VBtn>
                  </div>

                  <div class="d-flex align-center ga-1-5 mt-3 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-information-outline" size="14" color="info" />
                    <span>Calon peserta melihat identitas ini saat pertama kali membuka portal.</span>
                  </div>
                </div>

                <!-- 4. WhatsApp Dropdown Mockup View -->
                <div v-else-if="activePreviewTab === 'whatsapp'" class="mockup-container">
                  <div class="mockup-window rounded-lg border bg-surface overflow-hidden elevation-1 pa-4 d-flex flex-column align-center">
                    <div style="width: 250px;" class="border rounded-lg bg-surface elevation-3 py-2">
                      <div class="px-4 py-2 d-flex align-center ga-2 border-b">
                        <VAvatar color="primary" variant="tonal" size="32">
                          <VIcon icon="mdi-account-circle" size="20" />
                        </VAvatar>
                        <div class="text-truncate">
                          <div class="text-caption font-weight-bold text-truncate">{{ page.props.auth?.user?.name || 'Super Admin' }}</div>
                          <div class="text-disabled text-truncate" style="font-size: 11px;">Admin</div>
                        </div>
                      </div>
                      <div class="px-4 py-2 text-caption text-medium-emphasis d-flex align-center ga-2">
                        <VIcon icon="mdi-account-outline" size="18" />
                        <span>Profil</span>
                      </div>
                      <div class="px-4 py-2 text-caption text-medium-emphasis d-flex align-center ga-2">
                        <VIcon icon="mdi-cog-outline" size="18" />
                        <span>Pengaturan</span>
                      </div>
                      <!-- The WhatsApp Item -->
                      <div
                        class="px-4 py-2 text-caption font-weight-medium d-flex align-center ga-2"
                        :class="form.whatsapp_is_active && cleanWhatsAppNumber ? 'text-success bg-surface-variant-subtle' : 'text-disabled'"
                        style="transition: background 0.2s;"
                      >
                        <VIcon icon="mdi-whatsapp" size="18" :color="form.whatsapp_is_active && cleanWhatsAppNumber ? 'success' : 'disabled'" />
                        <span>{{ form.whatsapp_name || 'Layanan Pelanggan' }}</span>
                      </div>
                      <div class="px-3 pt-2">
                        <VBtn size="x-small" color="error" block variant="flat" prepend-icon="mdi-logout">
                          Keluar
                        </VBtn>
                      </div>
                    </div>

                    <div class="mt-3 text-caption text-center text-medium-emphasis">
                      <template v-if="form.whatsapp_is_active && cleanWhatsAppNumber">
                        <VChip size="x-small" color="success" variant="tonal" label class="mb-1 font-weight-medium">
                          <VIcon start icon="mdi-check" size="12" /> Tautan Aktif
                        </VChip>
                        <div>Membuka <code>https://wa.me/{{ cleanWhatsAppNumber }}</code> saat diklik.</div>
                      </template>
                      <template v-else>
                        <VChip size="x-small" color="error" variant="tonal" label class="mb-1 font-weight-medium">
                          <VIcon start icon="mdi-close" size="12" /> Dinonaktifkan
                        </VChip>
                        <div>Tombol akan tersembunyi / dinonaktifkan di menu navigasi.</div>
                      </template>
                    </div>
                  </div>

                  <div class="d-flex align-center ga-1-5 mt-3 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-information-outline" size="14" color="success" />
                    <span>Tampilan simulasi menu dropdown profil di pojok kanan atas.</span>
                  </div>
                </div>
              </VCardText>
            </VCard>
          </div>
        </VCol>
      </VRow>
    </VForm>
  </div>
</template>

<style scoped>
.dropzone-box {
  border: 2px dashed rgba(var(--v-border-color), var(--v-border-opacity) * 0.9);
  background-color: rgba(var(--v-theme-on-surface), 0.015);
  transition: all 0.2s ease;
  min-height: 190px;
}

.dropzone-box:hover {
  border-color: rgba(var(--v-theme-primary), 0.6);
  background-color: rgba(var(--v-theme-primary), 0.02);
}

.dropzone-active {
  border-color: rgb(var(--v-theme-primary)) !important;
  background-color: rgba(var(--v-theme-primary), 0.06) !important;
  transform: scale(0.995);
}

.preview-canvas-wrapper {
  max-width: 100%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.checkerboard-bg {
  background-color: rgba(var(--v-theme-surface), 1);
  background-image: radial-gradient(rgba(var(--v-theme-on-surface), 0.08) 1.5px, transparent 1.5px);
  background-size: 10px 10px;
}

.branding-logo-img {
  max-height: 85px;
  max-width: 240px;
  object-fit: contain;
}

.preview-panel-sticky {
  position: sticky;
  top: 84px;
}

/* Mockup Styles */
.mockup-window {
  box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.06);
}

.bg-surface-variant-subtle {
  background-color: rgba(var(--v-theme-on-surface), 0.03);
}

.window-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}
.dot-red { background-color: #ef4444; }
.dot-yellow { background-color: #f59e0b; }
.dot-green { background-color: #10b981; }

.mockup-nav-logo {
  height: 24px;
  max-width: 100px;
  object-fit: contain;
}

.mockup-cert-logo {
  height: 48px;
  max-width: 140px;
  object-fit: contain;
}

.mockup-login-logo {
  height: 42px;
  max-width: 160px;
  object-fit: contain;
}

.certificate-inner-border {
  border: 1.5px solid rgba(var(--v-theme-warning), 0.45);
  background: linear-gradient(180deg, rgba(var(--v-theme-surface), 1) 0%, rgba(var(--v-theme-warning), 0.02) 100%);
}

.skeleton-line {
  height: 8px;
  border-radius: 4px;
  background-color: rgba(var(--v-theme-on-surface), 0.08);
}

.signature-line {
  width: 50px;
  height: 1.5px;
  background-color: rgba(var(--v-theme-on-surface), 0.3);
}

.dummy-input {
  background-color: rgba(var(--v-theme-on-surface), 0.02);
}

.lh-1 {
  line-height: 1.2 !important;
}

.ga-1-5 {
  gap: 6px !important;
}

.ga-3-5 {
  gap: 14px !important;
}

.mt-0-5 {
  margin-top: 2px !important;
}

.mb-0-5 {
  margin-bottom: 2px !important;
}

.mb-1-5 {
  margin-bottom: 6px !important;
}

.px-2-5 {
  padding-left: 10px !important;
  padding-right: 10px !important;
}

.py-1-5 {
  padding-top: 6px !important;
  padding-bottom: 6px !important;
}

.letter-spacing-1 {
  letter-spacing: 1px !important;
}
</style>
