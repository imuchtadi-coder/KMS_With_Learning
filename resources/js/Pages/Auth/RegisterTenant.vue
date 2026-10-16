<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AppTextField from '@core/components/app-form-elements/AppTextField.vue'
import AppTextarea from '@core/components/app-form-elements/AppTextarea.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'

const props = defineProps({
  cs: {
    type: Object,
    default: null,
  },
  plans: {
    type: Array,
    default: () => [],
  },
  paymentMethods: {
    type: Array,
    default: () => [],
  },
  selectedPlanSlug: {
    type: String,
    default: null,
  },
})

const page = usePage()
const registrationSuccess = computed(() => page.props.flash?.registration_success)

const form = useForm({
  plan_id: null,
  institution_name: '',
  tenant_slug: '',
  admin_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  notes: '',
  payment_method: '',
  payment_proof: null,
  agree_terms: false,
})

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const isAutoSlug = ref(true)
const proofPreview = ref(null)
const fileInputRef = ref(null)

onMounted(() => {
  if (props.plans && props.plans.length) {
    if (props.selectedPlanSlug) {
      const found = props.plans.find(p => p.slug === props.selectedPlanSlug)
      if (found) form.plan_id = found.id
    }
    if (!form.plan_id) {
      const defaultPlan = props.plans.find(p => p.badge) || props.plans[0]
      form.plan_id = defaultPlan.id
    }
  }

  if (props.paymentMethods && props.paymentMethods.length) {
    form.payment_method = props.paymentMethods[0].code
  }
})

const selectedPlan = computed(() => {
  return props.plans.find(p => p.id === form.plan_id) || props.plans[0] || null
})

const selectedPaymentMethod = computed(() => {
  return props.paymentMethods.find(pm => pm.code === form.payment_method) || null
})

const onInstitutionInput = () => {
  if (isAutoSlug.value) {
    form.tenant_slug = form.institution_name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .slice(0, 40)
  }
}

const onSlugManualEdit = () => {
  isAutoSlug.value = false
}

const handleProofUpload = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    form.payment_proof = file
    const reader = new FileReader()
    reader.onload = (e) => {
      proofPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const removeProof = () => {
  form.payment_proof = null
  proofPreview.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const submit = () => {
  form.post(route('register-tenant.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

const whatsappUrl = computed(() => {
  if (!props.cs?.phone_number) return null
  const phone = props.cs.phone_number.replace(/[^0-9]/g, '')
  const text = encodeURIComponent(
    'Halo Admin KITsuluh, saya ingin menanyakan status pendaftaran tenant/institusi kami dan konfirmasi bukti pembayaran langganan.'
  )
  return `https://wa.me/${phone}?text=${text}`
})

const benefits = [
  {
    icon: 'mdi-domain',
    color: 'primary',
    title: 'Subdomain Mandiri',
    desc: 'Alamat portal khusus (misal: instansi.kitsuluh.test) eksklusif untuk lembaga Anda.',
  },
  {
    icon: 'mdi-shield-lock-outline',
    color: 'success',
    title: 'Isolasi Data Terjamin',
    desc: 'Database pengguna, silabus, dan materi belajar terpisah secara aman antar institusi.',
  },
  {
    icon: 'mdi-palette-outline',
    color: 'info',
    title: 'Kustomisasi Branding',
    desc: 'Sematkan logo, tagline resmi, dan desain sertifikat kelulusan khas institusi.',
  },
  {
    icon: 'mdi-gamepad-variant-outline',
    color: 'warning',
    title: 'Gamifikasi & HLS Video',
    desc: 'Didukung streaming video hemat kuota, kuis interaktif, leaderboard, dan poin XP.',
  },
]
</script>

<template>
  <Head title="Daftar Tenant & Pilih Paket — KITsuluh" />

  <div class="register-tenant-layout min-vh-100 d-flex flex-column bg-surface-container">
    <!-- Navbar Header -->
    <header class="register-header py-3 px-6 border-b bg-surface d-flex align-center justify-space-between">
      <Link href="/" class="d-flex align-center text-decoration-none text-high-emphasis ga-3">
        <img src="/img/logok.png" alt="KITsuluh Logo" height="36" />
        <div>
          <div class="text-h6 font-weight-bold lh-1 text-primary">KITsuluh</div>
          <span class="text-caption text-medium-emphasis">Multi-Tenant Learning Management System</span>
        </div>
      </Link>

      <div class="d-flex align-center ga-3">
        <NavbarThemeSwitcher />
        <div class="d-none d-sm-flex align-center ga-1 text-body-2 text-medium-emphasis">
          <span>Sudah punya akun institusi?</span>
        </div>
        <VBtn
          variant="tonal"
          color="primary"
          size="small"
          prepend-icon="mdi-login"
          :href="route('login')"
          class="font-weight-medium"
        >
          Masuk
        </VBtn>
        <VBtn
          variant="text"
          color="secondary"
          size="small"
          prepend-icon="mdi-home-outline"
          href="/"
          class="d-none d-md-inline-flex"
        >
          Beranda
        </VBtn>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow-1 py-8 py-md-12 px-4 px-sm-6">
      <div class="container-wrapper mx-auto">
        
        <!-- ================= SUCCESS STATE ================= -->
        <VCard
          v-if="registrationSuccess"
          elevation="0"
          class="tenant-premium-card border pa-8 pa-md-10 text-center mx-auto"
          style="max-width: 680px;"
        >
          <div class="success-icon-badge mx-auto mb-6 d-flex align-center justify-center rounded-circle bg-success-subtle">
            <VIcon icon="mdi-check-decagram" size="64" color="success" />
          </div>

          <VChip color="success" variant="tonal" size="small" class="font-weight-bold mb-3">
            BUKTI PEMBAYARAN DITERIMA
          </VChip>

          <h1 class="text-h4 font-weight-bold text-high-emphasis mb-3">
            Pendaftaran & Pembayaran Berhasil Diajukan!
          </h1>

          <p class="text-body-1 text-medium-emphasis mb-6 mx-auto" style="max-width: 540px;">
            Terima kasih telah mendaftarkan <strong class="text-high-emphasis">{{ registrationSuccess.institution_name }}</strong>. 
            Bukti pembayaran e-Wallet dan data pendaftaran Anda sedang dalam proses verifikasi oleh <strong>Super Admin</strong>.
          </p>

          <!-- Detail Card -->
          <VCard variant="tonal" color="primary" class="pa-5 rounded-lg mb-6 text-left border">
            <div class="text-subtitle-2 font-weight-bold mb-3 d-flex align-center ga-2 text-primary">
              <VIcon icon="mdi-receipt-text-check-outline" size="20" />
              Rincian Pendaftaran & Paket Berlangganan
            </div>
            <div class="d-flex flex-column ga-2 text-body-2 text-high-emphasis">
              <div class="d-flex justify-space-between flex-wrap">
                <span class="text-medium-emphasis">Nama Institusi:</span>
                <span class="font-weight-bold">{{ registrationSuccess.institution_name }}</span>
              </div>
              <div class="d-flex justify-space-between flex-wrap">
                <span class="text-medium-emphasis">Subdomain Akses:</span>
                <span class="font-weight-bold text-primary">{{ registrationSuccess.tenant_slug }}.kitsuluh.test</span>
              </div>
              <div class="d-flex justify-space-between flex-wrap">
                <span class="text-medium-emphasis">Paket Terpilih:</span>
                <VChip size="x-small" color="primary" variant="flat" class="font-weight-bold">
                  {{ registrationSuccess.plan_name }}
                </VChip>
              </div>
              <div class="d-flex justify-space-between flex-wrap">
                <span class="text-medium-emphasis">Nominal Tagihan:</span>
                <span class="font-weight-bold text-success">{{ formatRupiah(registrationSuccess.amount_paid) }}</span>
              </div>
              <div class="d-flex justify-space-between flex-wrap">
                <span class="text-medium-emphasis">Metode Pembayaran:</span>
                <span class="font-weight-bold">{{ registrationSuccess.payment_method }}</span>
              </div>
              <div class="d-flex justify-space-between flex-wrap">
                <span class="text-medium-emphasis">Status Verifikasi:</span>
                <VChip size="x-small" color="warning" variant="flat" class="font-weight-bold">
                  Menunggu Persetujuan Superadmin
                </VChip>
              </div>
            </div>
          </VCard>

          <p class="text-caption text-medium-emphasis mb-6">
            Akun admin dan subdomain akan segera diaktifkan setelah diverifikasi. Anda dapat menghubungi CS WhatsApp kami untuk proses konfirmasi instan.
          </p>

          <div class="d-flex flex-wrap align-center justify-center ga-3">
            <VBtn
              v-if="whatsappUrl"
              color="success"
              variant="elevated"
              prepend-icon="mdi-whatsapp"
              size="large"
              :href="whatsappUrl"
              target="_blank"
              class="font-weight-bold shadow-sm"
            >
              Konfirmasi via WhatsApp
            </VBtn>

            <VBtn
              variant="outlined"
              color="primary"
              size="large"
              :href="route('login')"
            >
              Ke Halaman Masuk
            </VBtn>

            <VBtn
              variant="text"
              color="secondary"
              size="large"
              href="/"
            >
              Kembali ke Beranda
            </VBtn>
          </div>
        </VCard>

        <!-- ================= REGISTRATION FORM (2-COLUMN SPLIT) ================= -->
        <VRow v-else align="start" justify="center">
          
          <!-- LEFT COLUMN: Value Proposition & Onboarding Guide -->
          <VCol cols="12" md="5" lg="4" class="pe-md-4">
            <div class="sticky-sidebar d-flex flex-column ga-6">
              
              <!-- Benefits Card -->
              <VCard elevation="0" class="tenant-premium-card border pa-6">
                <div class="d-flex align-center ga-2 mb-3">
                  <VChip color="primary" variant="tonal" size="small" class="font-weight-bold">
                    PORTAL MITRA RESMI
                  </VChip>
                  <VChip color="success" variant="tonal" size="small" class="font-weight-bold">
                    SaaS Multi-Tenant
                  </VChip>
                </div>

                <h2 class="text-h5 font-weight-bold text-high-emphasis mb-2">
                  Bangun Akademi Digital Institusi Anda
                </h2>

                <p class="text-body-2 text-medium-emphasis mb-6">
                  Platform LMS terisolasi siap pakai untuk kampus, kedinasan, lembaga diklat, dan institusi korporat.
                </p>

                <!-- Benefits List -->
                <div class="d-flex flex-column ga-4">
                  <div
                    v-for="(item, idx) in benefits"
                    :key="idx"
                    class="d-flex align-start ga-3"
                  >
                    <div
                      class="benefit-icon-box d-flex align-center justify-center rounded-lg flex-shrink-0"
                      :class="`bg-${item.color}-subtle`"
                    >
                      <VIcon :icon="item.icon" size="20" :color="item.color" />
                    </div>
                    <div>
                      <div class="text-body-2 font-weight-bold text-high-emphasis mb-0">
                        {{ item.title }}
                      </div>
                      <div class="text-caption text-medium-emphasis lh-sm">
                        {{ item.desc }}
                      </div>
                    </div>
                  </div>
                </div>

                <VDivider class="my-5" />

                <!-- 3 Steps Timeline -->
                <div class="text-caption font-weight-bold text-uppercase text-medium-emphasis mb-3">
                  Alur Singkat Pendaftaran:
                </div>
                <div class="d-flex flex-column ga-3">
                  <div class="d-flex align-center ga-3 text-caption">
                    <span class="step-num-badge">1</span>
                    <span class="text-high-emphasis font-weight-medium">Pilih Paket Langganan & Data Instansi</span>
                  </div>
                  <div class="d-flex align-center ga-3 text-caption">
                    <span class="step-num-badge">2</span>
                    <span class="text-high-emphasis font-weight-medium">Transfer e-Wallet & Upload Bukti Transaksi</span>
                  </div>
                  <div class="d-flex align-center ga-3 text-caption">
                    <span class="step-num-badge">3</span>
                    <span class="text-high-emphasis font-weight-medium">Verifikasi Super Admin & Portal Siap Digunakan</span>
                  </div>
                </div>
              </VCard>

              <!-- CS Support Mini Card -->
              <VCard
                v-if="whatsappUrl"
                elevation="0"
                class="tenant-premium-card border pa-5 bg-gradient-support"
              >
                <div class="d-flex align-center ga-3 mb-2">
                  <div class="cs-avatar-box rounded-circle bg-success d-flex align-center justify-center text-white flex-shrink-0">
                    <VIcon icon="mdi-whatsapp" size="20" />
                  </div>
                  <div>
                    <div class="text-subtitle-2 font-weight-bold text-high-emphasis">
                      Butuh Bantuan Pendaftaran?
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      Konsultasikan pilihan paket dan custom kebutuhan dengan Customer Service.
                    </div>
                  </div>
                </div>
                <VBtn
                  block
                  variant="outlined"
                  color="success"
                  size="small"
                  prepend-icon="mdi-chat-outline"
                  :href="whatsappUrl"
                  target="_blank"
                  class="mt-2 font-weight-medium"
                >
                  Chat CS Sekarang
                </VBtn>
              </VCard>

            </div>
          </VCol>

          <!-- RIGHT COLUMN: Registration Form Card -->
          <VCol cols="12" md="7" lg="8">
            <VCard
              elevation="0"
              class="tenant-premium-card border overflow-hidden"
            >
              <!-- Card Header Banner -->
              <div class="px-6 px-md-8 pt-7 pb-5 border-b bg-gradient-banner">
                <div class="d-flex align-center ga-2 mb-2">
                  <VIcon icon="mdi-domain-plus" color="primary" size="24" />
                  <span class="text-subtitle-2 font-weight-bold text-primary text-uppercase tracking-wider">
                    Registrasi Tenant & Langganan SaaS
                  </span>
                </div>
                <h1 class="text-h5 font-weight-bold text-high-emphasis mb-2">
                  Formulir Pengajuan Institusi
                </h1>
                <p class="text-body-2 text-medium-emphasis mb-0">
                  Tentukan paket langganan, lengkapi profil instansi, dan selesaikan pembayaran e-Wallet.
                </p>
              </div>

              <!-- Form Body -->
              <form @submit.prevent="submit" class="pa-6 pa-md-8">
                
                <!-- ================= STEP 1: PILIH PAKET LANGGANAN ================= -->
                <div class="form-section mb-8">
                  <div class="d-flex align-center ga-3 mb-4">
                    <div class="section-badge bg-primary text-white font-weight-bold rounded-circle d-flex align-center justify-center flex-shrink-0">
                      1
                    </div>
                    <div>
                      <h2 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-0">
                        Pilih Paket Langganan Institusi *
                      </h2>
                      <span class="text-caption text-medium-emphasis">
                        Pilih kapasitas kuota siswa dan fitur yang paling sesuai dengan kebutuhan lembaga Anda.
                      </span>
                    </div>
                  </div>

                  <div v-if="form.errors.plan_id" class="text-caption text-error mb-2">
                    {{ form.errors.plan_id }}
                  </div>

                  <VRow>
                    <VCol
                      v-for="plan in plans"
                      :key="plan.id"
                      cols="12"
                      sm="4"
                    >
                      <div
                        class="plan-selection-card pa-4 rounded-xl border cursor-pointer h-100 d-flex flex-column transition-all"
                        :class="{ 'plan-selected border-primary': form.plan_id === plan.id }"
                        @click="form.plan_id = plan.id"
                      >
                        <div class="d-flex align-center justify-space-between mb-2">
                          <VChip
                            v-if="plan.badge"
                            size="x-small"
                            color="primary"
                            variant="flat"
                            class="font-weight-bold"
                          >
                            {{ plan.badge }}
                          </VChip>
                          <span v-else class="text-caption font-weight-bold text-medium-emphasis text-uppercase">
                            Paket
                          </span>

                          <VIcon
                            :icon="form.plan_id === plan.id ? 'mdi-check-circle' : 'mdi-checkbox-blank-circle-outline'"
                            :color="form.plan_id === plan.id ? 'primary' : 'secondary'"
                            size="20"
                          />
                        </div>

                        <div class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1">
                          {{ plan.name }}
                        </div>

                        <div class="d-flex align-baseline ga-1 mb-2">
                          <span class="text-subtitle-1 font-weight-bold text-primary">
                            {{ formatRupiah(plan.price) }}
                          </span>
                          <span class="text-caption text-medium-emphasis">
                            / {{ plan.billing_period }}
                          </span>
                        </div>

                        <div class="text-caption text-medium-emphasis mb-3 line-clamp-2">
                          {{ plan.description }}
                        </div>

                        <!-- Mini features preview -->
                        <div class="mt-auto pt-2 border-t d-flex flex-column ga-1">
                          <div class="text-caption text-high-emphasis font-weight-medium d-flex align-center ga-1">
                            <VIcon icon="mdi-account-group" size="14" color="primary" />
                            <span>{{ plan.max_users ? `Maks. ${plan.max_users} Siswa` : 'Unlimited Siswa' }}</span>
                          </div>
                          <div class="text-caption text-high-emphasis font-weight-medium d-flex align-center ga-1">
                            <VIcon icon="mdi-book-multiple" size="14" color="info" />
                            <span>{{ plan.max_courses ? `Maks. ${plan.max_courses} Kursus` : 'Unlimited Kursus' }}</span>
                          </div>
                        </div>
                      </div>
                    </VCol>
                  </VRow>
                </div>

                <VDivider class="my-6" />

                <!-- ================= STEP 2: IDENTITAS INSTITUSI ================= -->
                <div class="form-section mb-6">
                  <div class="d-flex align-center ga-3 mb-4">
                    <div class="section-badge bg-primary text-white font-weight-bold rounded-circle d-flex align-center justify-center flex-shrink-0">
                      2
                    </div>
                    <div>
                      <h2 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-0">
                        Identitas Institusi & Domain Portal
                      </h2>
                      <span class="text-caption text-medium-emphasis">Informasi resmi lembaga dan alamat subdomain akses siswa.</span>
                    </div>
                  </div>

                  <VRow>
                    <VCol cols="12" md="7">
                      <AppTextField
                        v-model="form.institution_name"
                        label="Nama Institusi / Lembaga *"
                        placeholder="Contoh: Universitas Teknologi Mandiri"
                        prepend-inner-icon="mdi-domain"
                        :error-messages="form.errors.institution_name"
                        required
                        @input="onInstitutionInput"
                      />
                    </VCol>

                    <VCol cols="12" md="5">
                      <AppTextField
                        v-model="form.tenant_slug"
                        label="ID Subdomain Unik *"
                        placeholder="contoh: univ-teknologi"
                        prepend-inner-icon="mdi-link-variant"
                        suffix=".kitsuluh.test"
                        :error-messages="form.errors.tenant_slug"
                        hint="Hanya huruf kecil, angka, dan strip (-)"
                        persistent-hint
                        required
                        @input="onSlugManualEdit"
                      />
                    </VCol>

                    <!-- Real-time Live Subdomain URL Preview -->
                    <VCol cols="12" class="pt-0">
                      <div class="subdomain-preview-box px-3 py-2 rounded-lg bg-primary-subtle d-flex align-center ga-2">
                        <VIcon icon="mdi-web" size="16" color="primary" />
                        <span class="text-caption text-medium-emphasis">Alamat Portal Instansi:</span>
                        <span class="text-caption font-weight-bold text-primary text-truncate">
                          https://{{ form.tenant_slug ? form.tenant_slug.toLowerCase() : 'nama-subdomain' }}.kitsuluh.test
                        </span>
                      </div>
                    </VCol>

                    <VCol cols="12">
                      <AppTextarea
                        v-model="form.notes"
                        label="Kebutuhan & Gambaran Penggunaan (Opsional)"
                        placeholder="Tuliskan estimasi jumlah peserta atau jenis pelatihan yang ingin diselenggarakan..."
                        prepend-inner-icon="mdi-text-box-outline"
                        rows="2"
                        :error-messages="form.errors.notes"
                      />
                    </VCol>
                  </VRow>
                </div>

                <VDivider class="my-6" />

                <!-- ================= STEP 3: AKUN PENANGGUNG JAWAB ================= -->
                <div class="form-section mb-6">
                  <div class="d-flex align-center ga-3 mb-4">
                    <div class="section-badge bg-primary text-white font-weight-bold rounded-circle d-flex align-center justify-center flex-shrink-0">
                      3
                    </div>
                    <div>
                      <h2 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-0">
                        Akun Penanggung Jawab (Tenant Admin)
                      </h2>
                      <span class="text-caption text-medium-emphasis">Akun ini akan memiliki hak kelola penuh atas portal institusi Anda.</span>
                    </div>
                  </div>

                  <VRow>
                    <VCol cols="12" md="6">
                      <AppTextField
                        v-model="form.admin_name"
                        label="Nama Lengkap Penanggung Jawab *"
                        placeholder="Nama PIC atau Administrator"
                        prepend-inner-icon="mdi-account-outline"
                        :error-messages="form.errors.admin_name"
                        required
                      />
                    </VCol>

                    <VCol cols="12" md="6">
                      <AppTextField
                        v-model="form.phone"
                        label="Nomor WhatsApp / HP *"
                        placeholder="Contoh: 081234567890"
                        prepend-inner-icon="mdi-phone-outline"
                        :error-messages="form.errors.phone"
                        required
                      />
                    </VCol>

                    <VCol cols="12">
                      <AppTextField
                        v-model="form.email"
                        label="Email Administrator (Untuk Login) *"
                        placeholder="admin@institusi.ac.id"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        :error-messages="form.errors.email"
                        required
                      />
                    </VCol>

                    <VCol cols="12" md="6">
                      <AppTextField
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        label="Kata Sandi *"
                        placeholder="Minimal 8 karakter"
                        prepend-inner-icon="mdi-lock-outline"
                        :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        :error-messages="form.errors.password"
                        required
                        @click:append-inner="showPassword = !showPassword"
                      />
                    </VCol>

                    <VCol cols="12" md="6">
                      <AppTextField
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        label="Konfirmasi Kata Sandi *"
                        placeholder="Ketik ulang kata sandi"
                        prepend-inner-icon="mdi-lock-check-outline"
                        :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                        :error-messages="form.errors.password_confirmation"
                        required
                        @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
                      />
                    </VCol>
                  </VRow>
                </div>

                <VDivider class="my-6" />

                <!-- ================= STEP 4: PEMBAYARAN E-WALLET & BUKTI TRANSAKSI ================= -->
                <div class="form-section mb-6">
                  <div class="d-flex align-center ga-3 mb-4">
                    <div class="section-badge bg-primary text-white font-weight-bold rounded-circle d-flex align-center justify-center flex-shrink-0">
                      4
                    </div>
                    <div>
                      <h2 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-0">
                        Pembayaran e-Wallet & Upload Bukti Transaksi *
                      </h2>
                      <span class="text-caption text-medium-emphasis">
                        Selesaikan pembayaran langganan melalui e-Wallet dan lampirkan bukti transfer sebelum diverifikasi oleh Super Admin.
                      </span>
                    </div>
                  </div>

                  <!-- Ringkasan Tagihan Sesuai Paket -->
                  <div class="pa-4 bill-summary-banner mb-4 d-flex align-center justify-space-between flex-wrap ga-2">
                    <div>
                      <span class="text-caption text-medium-emphasis d-block">Paket yang Dipilih:</span>
                      <span class="text-subtitle-1 font-weight-bold text-high-emphasis">
                        {{ selectedPlan?.name || 'Paket' }}
                      </span>
                    </div>
                    <div class="text-sm-right">
                      <span class="text-caption text-medium-emphasis d-block">Total Nominal Transfer:</span>
                      <span class="text-h5 font-weight-bold text-primary">
                        {{ formatRupiah(selectedPlan?.price) }}
                      </span>
                    </div>
                  </div>

                  <!-- Pilih Saluran e-Wallet -->
                  <div class="mb-4">
                    <label class="text-caption font-weight-bold text-uppercase text-medium-emphasis d-block mb-2">
                      Pilih Metode e-Wallet Pembayaran:
                    </label>

                    <div class="d-flex flex-wrap ga-2">
                      <div
                        v-for="pm in paymentMethods"
                        :key="pm.code"
                        class="wallet-chip-select pa-3 rounded-lg border cursor-pointer d-flex align-center ga-2 transition-all"
                        :class="{ 'wallet-selected border-primary bg-primary-subtle': form.payment_method === pm.code }"
                        @click="form.payment_method = pm.code"
                      >
                        <VIcon
                          :icon="pm.type === 'qris' ? 'mdi-qrcode-scan' : 'mdi-cellphone-text'"
                          :color="form.payment_method === pm.code ? 'primary' : 'secondary'"
                          size="20"
                        />
                        <span class="text-body-2 font-weight-bold text-high-emphasis">
                          {{ pm.name }}
                        </span>
                      </div>
                    </div>

                    <div v-if="form.errors.payment_method" class="text-caption text-error mt-1">
                      {{ form.errors.payment_method }}
                    </div>
                  </div>

                  <!-- Info Rekening e-Wallet Terpilih -->
                  <VCard v-if="selectedPaymentMethod" elevation="0" class="border pa-4 mb-4 bg-surface payment-instruction-card">
                    <div class="d-flex align-center ga-2 mb-2 text-primary font-weight-bold text-body-2">
                      <VIcon icon="mdi-information-outline" size="18" />
                      Petunjuk Pembayaran {{ selectedPaymentMethod.name }}
                    </div>

                    <div class="d-flex flex-column ga-2 text-caption">
                      <div class="d-flex justify-space-between flex-wrap">
                        <span class="text-medium-emphasis">Nomor Akun / No HP Tujuan:</span>
                        <span class="font-weight-bold text-primary text-subtitle-2">{{ selectedPaymentMethod.account_number }}</span>
                      </div>
                      <div class="d-flex justify-space-between flex-wrap">
                        <span class="text-medium-emphasis">Atas Nama Penerima:</span>
                        <span class="font-weight-medium text-high-emphasis">{{ selectedPaymentMethod.account_name }}</span>
                      </div>
                      <div v-if="selectedPaymentMethod.instructions" class="pt-2 border-t text-medium-emphasis">
                        {{ selectedPaymentMethod.instructions }}
                      </div>
                    </div>
                  </VCard>

                  <!-- Upload Bukti Transaksi -->
                  <div>
                    <label class="text-caption font-weight-bold text-uppercase text-medium-emphasis d-block mb-2">
                      Unggah Bukti Transaksi Pembayaran (Screenshot / Foto) *
                    </label>

                    <input
                      ref="fileInputRef"
                      type="file"
                      accept="image/*"
                      class="d-none"
                      @change="handleProofUpload"
                    />

                    <!-- Dropzone / Upload Box -->
                    <div
                      v-if="!proofPreview"
                      class="proof-upload-box pa-6 text-center cursor-pointer transition-all bg-surface"
                      @click="triggerFileInput"
                    >
                      <VIcon icon="mdi-cloud-upload-outline" size="40" color="primary" class="mb-2" />
                      <div class="text-subtitle-2 font-weight-bold text-high-emphasis mb-1">
                        Klik untuk Memilih Bukti Pembayaran
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        Format file JPG, PNG, atau WEBP (Maksimal 5MB)
                      </div>
                    </div>

                    <!-- Proof Preview Thumbnail -->
                    <div v-else class="proof-preview-container pa-4 rounded-xl border bg-surface d-flex align-center justify-space-between flex-wrap ga-3">
                      <div class="d-flex align-center ga-3">
                        <img
                          :src="proofPreview"
                          alt="Preview Bukti Pembayaran"
                          class="rounded-lg border shadow-sm"
                          style="width: 72px; height: 72px; object-fit: cover;"
                        />
                        <div>
                          <div class="text-body-2 font-weight-bold text-high-emphasis">
                            {{ form.payment_proof?.name || 'Bukti-Pembayaran.jpg' }}
                          </div>
                          <div class="text-caption text-success d-flex align-center ga-1">
                            <VIcon icon="mdi-check-circle" size="14" />
                            File siap diunggah
                          </div>
                        </div>
                      </div>

                      <div class="d-flex align-center ga-2">
                        <VBtn
                          variant="tonal"
                          color="secondary"
                          size="small"
                          prepend-icon="mdi-refresh"
                          @click="triggerFileInput"
                        >
                          Ganti
                        </VBtn>
                        <VBtn
                          variant="text"
                          color="error"
                          size="small"
                          icon
                          @click="removeProof"
                        >
                          <VIcon icon="mdi-delete-outline" />
                        </VBtn>
                      </div>
                    </div>

                    <div v-if="form.errors.payment_proof" class="text-caption text-error mt-2">
                      {{ form.errors.payment_proof }}
                    </div>
                  </div>
                </div>

                <!-- Syarat & Ketentuan -->
                <div class="terms-agreement-box pa-4 rounded-lg border mb-6 bg-surface">
                  <VCheckbox
                    v-model="form.agree_terms"
                    density="compact"
                    color="primary"
                    hide-details="auto"
                    :error-messages="form.errors.agree_terms"
                    required
                  >
                    <template #label>
                      <span class="text-body-2 text-high-emphasis">
                        Saya menyatakan data institusi yang diisi adalah benar, paket langganan yang dipilih sesuai, dan bukti transfer pembayaran telah dilampirkan untuk proses verifikasi Super Admin.
                      </span>
                    </template>
                  </VCheckbox>
                </div>

                <!-- Submit Button Area -->
                <div class="d-flex flex-column align-center ga-3">
                  <VBtn
                    type="submit"
                    color="primary"
                    size="large"
                    block
                    :loading="form.processing"
                    :disabled="form.processing || !form.agree_terms || !form.plan_id || !form.payment_proof"
                    prepend-icon="mdi-send-check"
                    class="font-weight-bold register-submit-btn"
                  >
                    Kirim Pendaftaran & Bukti Pembayaran
                  </VBtn>

                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis text-center">
                    <VIcon icon="mdi-shield-check" color="success" size="16" />
                    <span>Data institusi dan transaksi Anda dijamin kerahasiaannya dan diproses secara aman.</span>
                  </div>
                </div>
              </form>
            </VCard>
          </VCol>
        </VRow>

      </div>
    </main>

    <!-- Footer Simple -->
    <footer class="py-4 px-6 border-t bg-surface text-center text-caption text-medium-emphasis">
      &copy; {{ new Date().getFullYear() }} KITsuluh — Multi-Tenant Learning Management System. All rights reserved.
    </footer>
  </div>
</template>

<style scoped>
.register-tenant-layout {
  min-height: 100vh;
  background-color: rgb(var(--v-theme-background)) !important;
  color: rgb(var(--v-theme-on-surface)) !important;
}

.container-wrapper {
  max-width: 1200px;
  width: 100%;
}

.tenant-premium-card {
  border-radius: 16px !important;
  background-color: rgb(var(--v-theme-surface)) !important;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  box-shadow: 0 4px 18px 0 rgba(var(--v-theme-on-surface), 0.06) !important;
}

.bg-gradient-banner {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.09) 0%, rgba(var(--v-theme-primary), 0.01) 100%) !important;
}

.bg-gradient-support {
  background: linear-gradient(135deg, rgba(var(--v-theme-success), 0.09) 0%, rgba(var(--v-theme-success), 0.01) 100%) !important;
}

.sticky-sidebar {
  position: sticky;
  top: 24px;
}

.section-badge {
  width: 28px;
  height: 28px;
  font-size: 13px;
}

.step-num-badge {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgba(115, 103, 240, 0.15);
  color: #7367f0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 11px;
}

.benefit-icon-box {
  width: 36px;
  height: 36px;
}

.cs-avatar-box {
  width: 36px;
  height: 36px;
}

.success-icon-badge {
  width: 96px;
  height: 96px;
}

.subdomain-preview-box {
  border: 1px dashed rgba(115, 103, 240, 0.35);
}

.terms-agreement-box {
  background-color: rgba(var(--v-theme-on-surface), 0.03) !important;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  border-radius: 12px !important;
}

.bill-summary-banner {
  border-radius: 14px !important;
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08) 0%, rgba(var(--v-theme-surface), 0.8) 100%) !important;
  border: 1px solid rgba(var(--v-theme-primary), 0.25) !important;
}

.plan-selection-card {
  border: 2px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  background-color: rgb(var(--v-theme-surface)) !important;
  border-radius: 14px !important;
}

.plan-selection-card:hover {
  border-color: rgba(var(--v-theme-primary), 0.5) !important;
  transform: translateY(-2px);
}

.plan-selected {
  border-color: rgb(var(--v-theme-primary)) !important;
  background-color: rgba(var(--v-theme-primary), 0.06) !important;
  box-shadow: 0 4px 14px rgba(var(--v-theme-primary), 0.16) !important;
}

.wallet-chip-select {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  background-color: rgb(var(--v-theme-surface)) !important;
  border-radius: 10px !important;
}

.wallet-chip-select:hover {
  border-color: rgba(var(--v-theme-primary), 0.5) !important;
}

.wallet-selected {
  border-color: rgb(var(--v-theme-primary)) !important;
  background-color: rgba(var(--v-theme-primary), 0.1) !important;
}

.payment-instruction-card {
  border-radius: 14px !important;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

.proof-upload-box {
  border: 2px dashed rgba(var(--v-theme-primary), 0.35) !important;
  border-radius: 14px !important;
  background-color: rgb(var(--v-theme-surface)) !important;
}

.proof-upload-box:hover {
  background-color: rgba(var(--v-theme-primary), 0.04) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.register-submit-btn {
  height: 48px;
  font-size: 15px;
  letter-spacing: 0.3px;
  box-shadow: 0 4px 12px rgba(115, 103, 240, 0.3) !important;
}

/* Color subtle background utilities */
.bg-primary-subtle {
  background-color: rgba(115, 103, 240, 0.1) !important;
}

.bg-success-subtle {
  background-color: rgba(40, 199, 111, 0.1) !important;
}

.bg-warning-subtle {
  background-color: rgba(255, 159, 67, 0.1) !important;
}

.bg-info-subtle {
  background-color: rgba(0, 207, 232, 0.1) !important;
}
</style>
