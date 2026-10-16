<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppTextField from '@core/components/app-form-elements/AppTextField.vue'
import AppSelect from '@core/components/app-form-elements/AppSelect.vue'
import AppTextarea from '@core/components/app-form-elements/AppTextarea.vue'

const props = defineProps({
  plans: {
    type: Array,
    default: () => [],
  },
  paymentMethods: {
    type: Array,
    default: () => [],
  },
})

const currentTab = ref('plans')

// Format Rupiah
const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

// Plan Dialog State
const isPlanDialogOpen = ref(false)
const isEditingPlan = ref(false)
const currentPlanId = ref(null)

const planForm = useForm({
  name: '',
  price: 0,
  billing_period: 'bulan',
  badge: '',
  description: '',
  max_users: null,
  max_courses: null,
  features: [''],
  is_active: true,
  sort_order: 1,
})

const openAddPlanDialog = () => {
  isEditingPlan.value = false
  currentPlanId.value = null
  planForm.reset()
  planForm.features = ['Subdomain Mandiri', 'Sertifikat Digital Otomatis']
  planForm.is_active = true
  planForm.sort_order = props.plans.length + 1
  isPlanDialogOpen.value = true
}

const openEditPlanDialog = (plan) => {
  isEditingPlan.value = true
  currentPlanId.value = plan.id
  planForm.name = plan.name
  planForm.price = plan.price
  planForm.billing_period = plan.billing_period || 'bulan'
  planForm.badge = plan.badge || ''
  planForm.description = plan.description || ''
  planForm.max_users = plan.max_users
  planForm.max_courses = plan.max_courses
  planForm.features = Array.isArray(plan.features) && plan.features.length ? [...plan.features] : ['']
  planForm.is_active = Boolean(plan.is_active)
  planForm.sort_order = plan.sort_order || 1
  isPlanDialogOpen.value = true
}

const addFeatureRow = () => {
  planForm.features.push('')
}

const removeFeatureRow = (index) => {
  if (planForm.features.length > 1) {
    planForm.features.splice(index, 1)
  }
}

const savePlan = () => {
  if (isEditingPlan.value) {
    planForm.put(route('admin.subscription-plans.update', currentPlanId.value), {
      onSuccess: () => {
        isPlanDialogOpen.value = false
      },
    })
  } else {
    planForm.post(route('admin.subscription-plans.store'), {
      onSuccess: () => {
        isPlanDialogOpen.value = false
      },
    })
  }
}

const deletePlan = (plan) => {
  if (!confirm(`Hapus paket "${plan.name}"? Pastikan tidak ada tenant yang masih menggunakan paket ini.`)) {
    return
  }
  router.delete(route('admin.subscription-plans.destroy', plan.id), {
    preserveScroll: true,
  })
}

// Payment Method Dialog State
const isPaymentDialogOpen = ref(false)
const currentPaymentId = ref(null)

const paymentForm = useForm({
  account_number: '',
  account_name: '',
  instructions: '',
  is_active: true,
})

const openEditPaymentDialog = (pm) => {
  currentPaymentId.value = pm.id
  paymentForm.account_number = pm.account_number
  paymentForm.account_name = pm.account_name
  paymentForm.instructions = pm.instructions || ''
  paymentForm.is_active = Boolean(pm.is_active)
  isPaymentDialogOpen.value = true
}

const savePaymentMethod = () => {
  paymentForm.put(route('admin.payment-methods.update', currentPaymentId.value), {
    onSuccess: () => {
      isPaymentDialogOpen.value = false
    },
  })
}
</script>

<template>
  <div>
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-center justify-space-between gap-4 mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">
          Paket Langganan & e-Wallet
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Kelola paket langganan SaaS (Starter, Profesional, Enterprise), biaya, fitur, kuota, serta rekening e-Wallet pembayaran.
        </p>
      </div>

      <div class="d-flex align-center ga-2">
        <VBtn
          color="primary"
          prepend-icon="mdi-plus"
          @click="openAddPlanDialog"
        >
          Tambah Paket
        </VBtn>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <VTabs v-model="currentTab" class="mb-6 border-b" color="primary">
      <VTab value="plans">
        <VIcon start icon="mdi-tag-multiple-outline" />
        Daftar Paket Langganan ({{ plans.length }})
      </VTab>
      <VTab value="payment_methods">
        <VIcon start icon="mdi-wallet-outline" />
        Rekening & e-Wallet Pembayaran ({{ paymentMethods.length }})
      </VTab>
    </VTabs>

    <!-- ================= TAB 1: PLANS ================= -->
    <div v-if="currentTab === 'plans'">
      <VRow>
        <VCol
          v-for="plan in plans"
          :key="plan.id"
          cols="12"
          md="4"
        >
          <VCard
            elevation="0"
            class="h-100 d-flex flex-column border subscription-plan-card"
            :class="{ 'featured-plan-card': plan.badge }"
          >
            <!-- Plan Header Banner (Theme-adaptive) -->
            <div
              class="pa-6 border-b plan-header-banner"
              :class="{ 'featured-header-banner': plan.badge }"
            >
              <div class="d-flex align-center justify-space-between mb-3" style="min-height: 28px;">
                <VChip
                  v-if="plan.badge"
                  color="primary"
                  size="small"
                  variant="flat"
                  class="font-weight-bold"
                >
                  {{ plan.badge }}
                </VChip>
                <div v-else></div>

                <VChip
                  :color="plan.is_active ? 'success' : 'secondary'"
                  size="small"
                  variant="tonal"
                  class="font-weight-medium"
                >
                  <VIcon start icon="mdi-circle-medium" size="14" />
                  {{ plan.is_active ? 'Aktif' : 'Nonaktif' }}
                </VChip>
              </div>

              <h3 class="text-h5 font-weight-bold text-high-emphasis mb-0">
                {{ plan.name }}
              </h3>
            </div>

            <!-- Price & Description -->
            <div class="pa-6 flex-grow-1 d-flex flex-column">
              <div class="d-flex align-baseline ga-1 mb-2">
                <span class="text-h4 font-weight-bold text-primary">
                  {{ formatRupiah(plan.price) }}
                </span>
                <span class="text-caption text-medium-emphasis">
                  / {{ plan.billing_period }}
                </span>
              </div>

              <p class="text-caption text-medium-emphasis mb-4">
                {{ plan.description || 'Tidak ada deskripsi singkat.' }}
              </p>

              <!-- Quota Badges -->
              <div class="d-flex flex-wrap ga-2 mb-4">
                <VChip size="small" variant="outlined" color="primary">
                  <VIcon start icon="mdi-account-group" size="14" />
                  {{ plan.max_users ? `${plan.max_users} Siswa` : 'Unlimited Siswa' }}
                </VChip>
                <VChip size="small" variant="outlined" color="info">
                  <VIcon start icon="mdi-book-multiple" size="14" />
                  {{ plan.max_courses ? `${plan.max_courses} Kursus` : 'Unlimited Kursus' }}
                </VChip>
                <VChip size="small" variant="tonal" color="secondary">
                  {{ plan.tenants_count || 0 }} Tenant Aktif
                </VChip>
              </div>

              <VDivider class="my-3" />

              <!-- Feature List -->
              <div class="text-caption font-weight-bold text-uppercase text-medium-emphasis mb-3">
                Fitur Termasuk:
              </div>
              <div class="d-flex flex-column ga-2 flex-grow-1">
                <div
                  v-for="(feat, fIdx) in plan.features"
                  :key="fIdx"
                  class="d-flex align-start ga-2 text-caption"
                >
                  <VIcon icon="mdi-check-circle" size="16" color="success" class="flex-shrink-0 mt-1" />
                  <span class="text-high-emphasis">{{ feat }}</span>
                </div>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="pa-4 border-t bg-surface d-flex align-center justify-space-between">
              <VBtn
                variant="tonal"
                color="primary"
                size="small"
                prepend-icon="mdi-pencil-outline"
                @click="openEditPlanDialog(plan)"
              >
                Edit Paket
              </VBtn>

              <VBtn
                variant="text"
                color="error"
                size="small"
                icon
                :disabled="plan.tenants_count > 0"
                :title="plan.tenants_count > 0 ? 'Paket masih digunakan' : 'Hapus paket'"
                @click="deletePlan(plan)"
              >
                <VIcon icon="mdi-delete-outline" />
              </VBtn>
            </div>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <!-- ================= TAB 2: PAYMENT METHODS ================= -->
    <div v-else-if="currentTab === 'payment_methods'">
      <VRow>
        <VCol
          v-for="pm in paymentMethods"
          :key="pm.id"
          cols="12"
          md="6"
        >
          <VCard elevation="0" class="border pa-6 h-100 d-flex flex-column payment-method-card">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="d-flex align-center ga-3">
                <VAvatar color="primary" variant="tonal" rounded="lg" size="44">
                  <VIcon :icon="pm.type === 'qris' ? 'mdi-qrcode-scan' : 'mdi-cellphone-text'" size="24" />
                </VAvatar>
                <div>
                  <h4 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-0">
                    {{ pm.name }}
                  </h4>
                  <span class="text-caption text-medium-emphasis text-uppercase">
                    {{ pm.type }}
                  </span>
                </div>
              </div>

              <VChip
                :color="pm.is_active ? 'success' : 'default'"
                size="small"
                variant="tonal"
                class="font-weight-medium"
              >
                {{ pm.is_active ? 'Aktif' : 'Nonaktif' }}
              </VChip>
            </div>

            <VDivider class="mb-4" />

            <div class="d-flex flex-column ga-3 flex-grow-1">
              <div>
                <span class="text-caption text-medium-emphasis d-block">Nomor Akun / No HP e-Wallet:</span>
                <span class="text-subtitle-2 font-weight-bold text-primary">{{ pm.account_number }}</span>
              </div>

              <div>
                <span class="text-caption text-medium-emphasis d-block">Atas Nama Penerima:</span>
                <span class="text-body-2 font-weight-medium text-high-emphasis">{{ pm.account_name }}</span>
              </div>

              <div>
                <span class="text-caption text-medium-emphasis d-block">Instruksi Pembayaran:</span>
                <p class="text-caption text-high-emphasis mb-0">
                  {{ pm.instructions || '-' }}
                </p>
              </div>
            </div>

            <div class="pt-4 border-t mt-4 d-flex justify-end">
              <VBtn
                variant="tonal"
                color="primary"
                size="small"
                prepend-icon="mdi-pencil-outline"
                @click="openEditPaymentDialog(pm)"
              >
                Ubah Rekening / e-Wallet
              </VBtn>
            </div>
          </VCard>
        </VCol>
      </VRow>
    </div>

    <!-- ================= PLAN MODAL DIALOG ================= -->
    <VDialog v-model="isPlanDialogOpen" max-width="650" persistent scrollable>
      <VCard class="rounded-xl">
        <VCardTitle class="pa-6 border-b d-flex align-center justify-space-between">
          <span class="text-h6 font-weight-bold">
            {{ isEditingPlan ? `Edit Paket: ${planForm.name}` : 'Tambah Paket Baru' }}
          </span>
          <VBtn icon variant="text" size="small" @click="isPlanDialogOpen = false">
            <VIcon icon="mdi-close" />
          </VBtn>
        </VCardTitle>

        <VCardText class="pa-6">
          <VRow>
            <VCol cols="12" sm="8">
              <AppTextField
                v-model="planForm.name"
                label="Nama Paket *"
                placeholder="Contoh: Profesional"
                :error-messages="planForm.errors.name"
                required
              />
            </VCol>

            <VCol cols="12" sm="4">
              <AppTextField
                v-model="planForm.badge"
                label="Badge / Label"
                placeholder="Contoh: Populer"
                :error-messages="planForm.errors.badge"
              />
            </VCol>

            <VCol cols="12" sm="7">
              <AppTextField
                v-model="planForm.price"
                label="Harga Langganan (Rp) *"
                placeholder="799000"
                type="number"
                prefix="Rp"
                :error-messages="planForm.errors.price"
                required
              />
            </VCol>

            <VCol cols="12" sm="5">
              <AppSelect
                v-model="planForm.billing_period"
                label="Periode Tagihan *"
                :items="[
                  { title: 'Per Bulan', value: 'bulan' },
                  { title: 'Per Tahun', value: 'tahun' },
                ]"
                item-title="title"
                item-value="value"
                :error-messages="planForm.errors.billing_period"
                required
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea
                v-model="planForm.description"
                label="Deskripsi Singkat"
                placeholder="Tuliskan target dan keunggulan utama paket ini..."
                rows="2"
                :error-messages="planForm.errors.description"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="planForm.max_users"
                label="Kuota Maksimal Siswa"
                placeholder="Kosongkan untuk Unlimited"
                type="number"
                hint="Biarkan kosong jika tanpa batas"
                persistent-hint
                :error-messages="planForm.errors.max_users"
              />
            </VCol>

            <VCol cols="12" sm="6">
              <AppTextField
                v-model="planForm.max_courses"
                label="Kuota Maksimal Kursus"
                placeholder="Kosongkan untuk Unlimited"
                type="number"
                hint="Biarkan kosong jika tanpa batas"
                persistent-hint
                :error-messages="planForm.errors.max_courses"
              />
            </VCol>

            <!-- Dynamic Features List -->
            <VCol cols="12">
              <div class="d-flex align-center justify-space-between mb-2">
                <span class="text-subtitle-2 font-weight-bold">Fitur-Fitur Paket:</span>
                <VBtn
                  variant="text"
                  color="primary"
                  size="small"
                  prepend-icon="mdi-plus"
                  @click="addFeatureRow"
                >
                  Tambah Fitur
                </VBtn>
              </div>

              <div class="d-flex flex-column ga-2">
                <div
                  v-for="(_, index) in planForm.features"
                  :key="index"
                  class="d-flex align-center ga-2"
                >
                  <AppTextField
                    v-model="planForm.features[index]"
                    placeholder="Contoh: Streaming Video HLS Adaptif"
                    density="compact"
                    class="flex-grow-1"
                  />
                  <VBtn
                    icon
                    size="small"
                    variant="text"
                    color="error"
                    :disabled="planForm.features.length === 1"
                    @click="removeFeatureRow(index)"
                  >
                    <VIcon icon="mdi-delete-outline" />
                  </VBtn>
                </div>
              </div>
            </VCol>

            <VCol cols="12">
              <VCheckbox
                v-model="planForm.is_active"
                label="Aktifkan Paket (Tampil di Landing Page & Pendaftaran)"
                color="primary"
                hide-details
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-6 border-t d-flex justify-end ga-2">
          <VBtn variant="outlined" color="secondary" @click="isPlanDialogOpen = false">
            Batal
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            :loading="planForm.processing"
            @click="savePlan"
          >
            Simpan Paket
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- ================= PAYMENT METHOD MODAL DIALOG ================= -->
    <VDialog v-model="isPaymentDialogOpen" max-width="500" persistent>
      <VCard class="rounded-xl">
        <VCardTitle class="pa-6 border-b d-flex align-center justify-space-between">
          <span class="text-h6 font-weight-bold">
            Ubah Data Rekening / e-Wallet
          </span>
          <VBtn icon variant="text" size="small" @click="isPaymentDialogOpen = false">
            <VIcon icon="mdi-close" />
          </VBtn>
        </VCardTitle>

        <VCardText class="pa-6">
          <VRow>
            <VCol cols="12">
              <AppTextField
                v-model="paymentForm.account_number"
                label="Nomor Akun / No HP e-Wallet *"
                placeholder="Contoh: 0812-3456-7890"
                :error-messages="paymentForm.errors.account_number"
                required
              />
            </VCol>

            <VCol cols="12">
              <AppTextField
                v-model="paymentForm.account_name"
                label="Atas Nama Penerima *"
                placeholder="Contoh: PT KITSULUH EDUKASI"
                :error-messages="paymentForm.errors.account_name"
                required
              />
            </VCol>

            <VCol cols="12">
              <AppTextarea
                v-model="paymentForm.instructions"
                label="Petunjuk Pembayaran"
                placeholder="Langkah-langkah transfer yang akan dibaca pendaftar..."
                rows="3"
                :error-messages="paymentForm.errors.instructions"
              />
            </VCol>

            <VCol cols="12">
              <VCheckbox
                v-model="paymentForm.is_active"
                label="Aktifkan Metode Pembayaran Ini"
                color="primary"
                hide-details
              />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions class="pa-6 border-t d-flex justify-end ga-2">
          <VBtn variant="outlined" color="secondary" @click="isPaymentDialogOpen = false">
            Batal
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            :loading="paymentForm.processing"
            @click="savePaymentMethod"
          >
            Simpan Perubahan
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.subscription-plan-card {
  border-radius: 16px !important;
  background-color: rgb(var(--v-theme-surface)) !important;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  overflow: hidden !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.subscription-plan-card:hover {
  box-shadow: 0 8px 24px rgba(var(--v-theme-on-surface), 0.08) !important;
}

.featured-plan-card {
  border-color: rgba(var(--v-theme-primary), 0.6) !important;
  box-shadow: 0 4px 18px rgba(var(--v-theme-primary), 0.1) !important;
}

.featured-plan-card:hover {
  border-color: rgb(var(--v-theme-primary)) !important;
  box-shadow: 0 10px 28px rgba(var(--v-theme-primary), 0.18) !important;
}

.plan-header-banner {
  background: linear-gradient(180deg, rgba(var(--v-theme-on-surface), 0.035) 0%, rgba(var(--v-theme-surface), 0) 100%);
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

.featured-header-banner {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.10) 0%, rgba(var(--v-theme-primary), 0.02) 100%) !important;
  border-bottom: 1px solid rgba(var(--v-theme-primary), 0.22) !important;
}

.payment-method-card {
  border-radius: 16px !important;
  background-color: rgb(var(--v-theme-surface)) !important;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.payment-method-card:hover {
  box-shadow: 0 8px 24px rgba(var(--v-theme-on-surface), 0.08) !important;
}
</style>
