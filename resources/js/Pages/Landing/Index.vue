<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

// Existing Assets
import laptopGirl from '@images/illustrations/laptop-girl.png'
import boyAcademy from '@images/illustrations/boy-app-academy.png'
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import avatar3 from '@images/avatars/avatar-3.png'
import avatar4 from '@images/avatars/avatar-4.png'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({ total_courses: 0, total_students: 0, total_tenants: 0 }),
  },
  cs: {
    type: Object,
    default: null,
  },
  plans: {
    type: Array,
    default: () => [],
  },
})

const mobileDrawer = ref(false)
const showScrollTop = ref(false)
const demoLoadingRole = ref(null)

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 350
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

const triggerDemoLogin = (role) => {
  demoLoadingRole.value = role
  router.post(route('demo.login'), { role }, {
    onFinish: () => {
      demoLoadingRole.value = null
    },
  })
}

const scrollToSection = (id) => {
  mobileDrawer.value = false
  const el = document.getElementById(id)
  if (el) {
    const navHeight = 78
    const elementPosition = el.getBoundingClientRect().top
    const offsetPosition = elementPosition + window.pageYOffset - navHeight

    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth',
    })
  }
}

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

const getWhatsappUrl = () => {
  if (!props.cs?.phone_number) return null
  const phone = props.cs.phone_number.replace(/[^0-9]/g, '')
  const text = encodeURIComponent(
    'Halo Tim KITsuluh, saya tertarik untuk konsultasi implementasi LMS KITsuluh di institusi kami.'
  )
  return `https://wa.me/${phone}?text=${text}`
}

const keyFeatures = [
  {
    title: 'Multi-Tenancy Terisolasi',
    description: 'Data antar institusi dipisahkan secara aman. Setiap lembaga memiliki subdomain tersendiri dan tidak saling tercampur data pengguna maupun kursusnya.',
    icon: 'mdi-domain',
    color: 'primary',
    badge: 'Multi-Tenant',
    tags: ['Subdomain Mandiri', 'Isolasi Data Aman'],
  },
  {
    title: 'Gamification Engine',
    description: 'Tingkatkan retensi belajar siswa dengan akumulasi Poin Pengalaman (XP), lencana pencapaian (badges), dan papan peringkat leaderboard interaktif.',
    icon: 'mdi-gamepad-variant-outline',
    color: 'warning',
    badge: 'Interaktif & XP',
    tags: ['Poin XP & Badges', 'Live Leaderboard'],
  },
  {
    title: 'Learning Paths Terarah',
    description: 'Atur alur pembelajaran bertahap (learning path) mulai dari materi dasar hingga mahir, dengan prasyarat penyelesaian yang dapat dikustomisasi.',
    icon: 'mdi-routes',
    color: 'info',
    badge: 'Alur Kurikulum',
    tags: ['Silabus Bertahap', 'Prasyarat Modul'],
  },
  {
    title: 'Streaming Video HLS Adaptif',
    description: 'Dukungan pemutaran video berkecepatan tinggi yang menyesuaikan kualitas jaringan internet pengguna secara otomatis tanpa buffering berat.',
    icon: 'mdi-video-check-outline',
    color: 'error',
    badge: 'Video Streaming',
    tags: ['Bitrate Adaptif HLS', 'Bebas Buffering'],
  },
  {
    title: 'Sertifikat Digital Otomatis',
    description: 'Penerbitan sertifikat kelulusan dalam format PDF resmi dengan nomor seri dan logo institusi seketika setelah siswa memenuhi nilai ambang batas.',
    icon: 'mdi-certificate-outline',
    color: 'success',
    badge: 'Kredensial Digital',
    tags: ['Serial & QR Code', 'Unduh PDF Instan'],
  },
  {
    title: 'Branding Khusus Institusi',
    description: 'Gunakan nama resmi instansi, tagline, dan logo resmi lembaga Anda pada tampilan antarmuka dan laporan evaluasi peserta.',
    icon: 'mdi-palette-outline',
    color: 'secondary',
    badge: 'White-Label',
    tags: ['Logo & Identitas', 'Tema Kustom Lembaga'],
  },
]

const steps = [
  {
    number: '01',
    color: 'primary',
    title: 'Isi Formulir Tenant',
    description: 'Daftarkan nama lembaga, subdomain unik yang diinginkan, serta email akun admin penanggung jawab.',
  },
  {
    number: '02',
    color: 'warning',
    title: 'Verifikasi & Approval',
    description: 'Super Admin meninjau data permohonan untuk memastikan kepatuhan dan mengaktifkan database tenant.',
  },
  {
    number: '03',
    color: 'info',
    title: 'Personalisasi & Kurikulum',
    description: 'Unggah logo instansi, undang instruktur pengajar, dan mulai menyusun silabus materi serta kuis evaluasi.',
  },
  {
    number: '04',
    color: 'success',
    title: 'Pelaksanaan Pembelajaran',
    description: 'Peserta dapat mulai belajar, mengumpulkan poin XP, mengikuti kuis, dan memperoleh sertifikat kompetensi.',
  },
]

const testimonials = [
  {
    avatar: avatar1,
    name: 'Dr. Hendra Saputra',
    role: 'Direktur Pelatihan Vokasi',
    org: 'Politeknik Pertanian Terpadu',
    rating: 5,
    comment: 'Implementasi KITsuluh membuat manajemen sertifikasi dan pelatihan ratusan penyuluh pertanian kami menjadi sangat terstruktur dan terpantau efisien.',
  },
  {
    avatar: avatar2,
    name: 'Ratna Kusuma Dewi, M.Kom',
    role: 'Kepala Pusat Pembelajaran Digital',
    org: 'Akademi Agrobisnis Modern',
    rating: 5,
    comment: 'Fitur video streaming HLS sangat membantu peserta didik di pelosok daerah dengan jaringan terbatas. Tidak ada lagi keluhan video lambat buffering.',
  },
  {
    avatar: avatar3,
    name: 'Dimas Prasetyo',
    role: 'Peserta Pelatihan Pertanian Presisi',
    org: 'Penerima Sertifikasi Batch 4',
    rating: 5,
    comment: 'Antarmukanya sangat mudah digunakan di HP maupun laptop! Papan peringkat dan sistem poin XP membuat belajar tidak membosankan.',
  },
]

const faqs = [
  {
    q: 'Bagaimana cara kerja isolasi multi-tenant di KITsuluh?',
    a: 'Setiap institusi (tenant) memiliki subdomain mandiri dan ruang isolasi data terpisah. Data siswa, kursus, sertifikat, dan pengaturan admin antar institusi dipisahkan secara ketat sehingga privasi dan integritas data terjamin.',
  },
  {
    q: 'Apakah institusi saya bisa menggunakan logo dan nama lembaga sendiri?',
    a: 'Tentu saja. KITsuluh mendukung kustomisasi identitas (white-labeling) yang memungkinkan Anda menampilkan nama lembaga, logo, tagline, serta format sertifikat berlogo resmi instansi Anda.',
  },
  {
    q: 'Bagaimana mekanisme Uji Coba Demo 1-Klik?',
    a: 'Anda dapat langsung mencoba peran Admin Institusi, Instruktur, maupun Siswa hanya dengan menekan tombol peran di bagian Demo. Sistem akan membuatkan sesi demo aktif instan tanpa meminta pendaftaran kartu kredit ataupun password.',
  },
  {
    q: 'Apakah materi video streaming hemat kuota?',
    a: 'Ya, KITsuluh mengonversi seluruh materi video instruktur menggunakan teknologi HLS (HTTP Live Streaming) adaptif. Kualitas tayangan otomatis menyesuaikan kecepatan internet pengguna sehingga lancar dan hemat kuota.',
  },
  {
    q: 'Metode pembayaran apa saja yang didukung untuk biaya langganan?',
    a: 'Kami mendukung transfer antar bank (Virtual Account), QRIS instan seluruh bank & e-Wallet (GoPay, OVO, Dana, ShopeePay), serta konfirmasi cepat melalui tim Customer Service resmi kami.',
  },
]
</script>

<template>
  <Head title="KITsuluh — Learning Management System Multi-Tenant Terpadu" />

  <VApp theme="light" class="landing-app">
    <div class="landing-page">
      <!-- ================= NAVIGATION DRAWER (MOBILE) ================= -->
      <VNavigationDrawer
        v-model="mobileDrawer"
        temporary
        location="right"
        width="300"
        class="landing-mobile-drawer pa-4"
      >
        <div class="d-flex align-center justify-space-between mb-6 pb-3 border-b">
          <div class="d-flex align-center ga-2">
            <img src="/img/logok.png" alt="KITsuluh" height="34" />
            <span class="text-subtitle-1 font-weight-bold text-primary">KITsuluh</span>
          </div>
          <VBtn icon="mdi-close" variant="text" size="small" @click="mobileDrawer = false" />
        </div>

        <div class="d-flex flex-column ga-2 mb-6">
          <VBtn variant="text" class="justify-start text-capitalize font-weight-medium" @click="scrollToSection('fitur')">
            <VIcon icon="mdi-view-grid-outline" class="mr-2" /> Fitur Utama
          </VBtn>
          <VBtn variant="text" class="justify-start text-capitalize font-weight-medium" @click="scrollToSection('demo')">
            <VIcon icon="mdi-play-circle-outline" class="mr-2" /> Coba Demo
          </VBtn>
          <VBtn variant="text" class="justify-start text-capitalize font-weight-medium" @click="scrollToSection('alur')">
            <VIcon icon="mdi-timeline-outline" class="mr-2" /> Alur Tenant
          </VBtn>
          <VBtn variant="text" class="justify-start text-capitalize font-weight-medium" @click="scrollToSection('testimoni')">
            <VIcon icon="mdi-star-outline" class="mr-2" /> Testimoni
          </VBtn>
          <VBtn v-if="plans && plans.length" variant="text" class="justify-start text-capitalize font-weight-medium" @click="scrollToSection('harga')">
            <VIcon icon="mdi-tag-outline" class="mr-2" /> Harga Paket
          </VBtn>
          <VBtn variant="text" class="justify-start text-capitalize font-weight-medium" @click="scrollToSection('faq')">
            <VIcon icon="mdi-help-circle-outline" class="mr-2" /> FAQ
          </VBtn>
        </div>

        <VDivider class="mb-6" />

        <div class="d-flex flex-column ga-3">
          <template v-if="$page.props.auth?.user">
            <VBtn
              variant="tonal"
              color="primary"
              block
              prepend-icon="mdi-view-dashboard"
              :href="route('dashboard')"
            >
              Ke Dasbor
            </VBtn>
          </template>
          <template v-else>
            <VBtn
              variant="outlined"
              color="primary"
              block
              prepend-icon="mdi-login"
              :href="route('login')"
            >
              Masuk
            </VBtn>
          </template>
          <VBtn
            color="primary"
            variant="flat"
            block
            prepend-icon="mdi-domain-plus"
            :href="route('register-tenant')"
          >
            Daftar Tenant
          </VBtn>
        </div>
      </VNavigationDrawer>

      <!-- ================= HEADER / NAVBAR ================= -->
      <header class="landing-header">
        <div class="landing-container">
          <div class="d-flex align-center justify-space-between py-3">
            <!-- Brand Logo -->
            <Link href="/" class="brand-link d-flex align-center ga-3 text-decoration-none">
              <div class="brand-logo-wrap d-flex align-center justify-center">
                <img src="/img/logok.png" alt="KITsuluh" height="40" class="brand-logo" />
              </div>
              <div>
                <div class="brand-name text-h6 font-weight-black text-primary lh-1">KITsuluh</div>
                <div class="text-caption text-medium-emphasis">Multi-Tenant LMS Platform</div>
              </div>
            </Link>

            <!-- Nav Links (Desktop) -->
            <nav class="d-none d-lg-flex align-center ga-6">
              <a href="#fitur" class="nav-item-link" @click.prevent="scrollToSection('fitur')">Fitur</a>
              <a href="#demo" class="nav-item-link" @click.prevent="scrollToSection('demo')">Coba Demo</a>
              <a href="#alur" class="nav-item-link" @click.prevent="scrollToSection('alur')">Alur Tenant</a>
              <a href="#testimoni" class="nav-item-link" @click.prevent="scrollToSection('testimoni')">Testimoni</a>
              <a v-if="plans && plans.length" href="#harga" class="nav-item-link" @click.prevent="scrollToSection('harga')">Harga Paket</a>
              <a href="#faq" class="nav-item-link" @click.prevent="scrollToSection('faq')">FAQ</a>
            </nav>

            <!-- Header Action Buttons -->
            <div class="d-flex align-center ga-2 ga-sm-3">
              <template v-if="$page.props.auth?.user">
                <VBtn
                  variant="tonal"
                  color="primary"
                  size="default"
                  prepend-icon="mdi-view-dashboard-outline"
                  class="font-weight-bold"
                  :href="route('dashboard')"
                >
                  Ke Dasbor
                </VBtn>
              </template>
              <template v-else>
                <VBtn
                  variant="text"
                  color="secondary"
                  size="default"
                  class="d-none d-sm-inline-flex font-weight-medium"
                  :href="route('login')"
                >
                  Masuk
                </VBtn>
              </template>

              <VBtn
                variant="tonal"
                color="primary"
                size="default"
                prepend-icon="mdi-play-circle-outline"
                class="d-none d-md-inline-flex font-weight-bold"
                @click="scrollToSection('demo')"
              >
                Coba Demo
              </VBtn>

              <VBtn
                variant="elevated"
                color="primary"
                size="default"
                prepend-icon="mdi-domain-plus"
                class="font-weight-bold elevation-2"
                :href="route('register-tenant')"
              >
                Daftar Tenant
              </VBtn>

              <!-- Hamburger Toggle on Mobile -->
              <VBtn
                icon="mdi-menu"
                variant="text"
                class="d-lg-none"
                @click="mobileDrawer = true"
              />
            </div>
          </div>
        </div>

        <!-- Active User Toast Banner (If logged in) -->
        <div v-if="$page.props.auth?.user" class="active-user-ribbon py-2 px-4 border-t text-center text-caption d-flex align-center justify-center ga-2">
          <VIcon icon="mdi-account-check" size="18" color="primary" />
          <span>
            Masuk sebagai <strong>{{ $page.props.auth.user.name }}</strong>. Ingin menguji peran lain?
          </span>
          <a href="#demo" class="text-primary font-weight-bold text-decoration-none ml-1" @click.prevent="scrollToSection('demo')">
            Pilih Demo 1-Klik &darr;
          </a>
        </div>
      </header>

      <!-- ================= HERO SECTION ================= -->
      <section class="hero-section py-12 py-md-20">
        <div class="landing-container">
          <VRow align="center" justify="space-between">
            <!-- Left: Headline & Actions -->
            <VCol cols="12" lg="6" class="hero-text-col">
              <div class="d-inline-flex align-center ga-2 px-3 py-1 rounded-pill bg-primary-subtle text-primary font-weight-semibold text-caption mb-4">
                <span class="pulse-indicator"></span>
                <span>Platform LMS Multi-Tenant Modern & Terpadu</span>
              </div>

              <h1 class="hero-headline text-h3 text-md-h2 font-weight-black mb-4 lh-tight">
                Bangun Portal Pembelajaran Mandiri dengan
                <span class="text-gradient">KITsuluh</span>
              </h1>

              <p class="hero-description text-body-1 mb-8" style="max-width: 600px;">
                Solusi Learning Management System siap pakai untuk institusi pendidikan, balai pelatihan vokasi, instansi pemerintah, dan perusahaan. Dilengkapi silabus bertahap, streaming HLS adaptif, gamifikasi XP, serta penerbitan sertifikat digital resmi otomatis.
              </p>

              <!-- CTA Group -->
              <div class="d-flex flex-wrap align-center ga-4 mb-10">
                <VBtn
                  color="primary"
                  size="x-large"
                  elevation="4"
                  prepend-icon="mdi-domain-plus"
                  class="px-8 font-weight-bold hero-btn-main"
                  :href="route('register-tenant')"
                >
                  Daftarkan Lembaga Anda
                </VBtn>

                <VBtn
                  variant="outlined"
                  color="primary"
                  size="x-large"
                  prepend-icon="mdi-play-circle-outline"
                  class="px-6 font-weight-bold"
                  @click="scrollToSection('demo')"
                >
                  Coba Demo 1-Klik
                </VBtn>
              </div>

              <!-- Live Stats Counter -->
              <div class="hero-stats-box d-flex flex-wrap align-center ga-6 pt-5 border-t">
                <div class="stat-item">
                  <div class="text-h4 font-weight-black text-primary">{{ stats.total_courses || '10+' }}</div>
                  <div class="text-caption text-medium-emphasis font-weight-medium">Kursus Aktif</div>
                </div>
                <VDivider vertical style="height: 36px;" />
                <div class="stat-item">
                  <div class="text-h4 font-weight-black text-success">{{ stats.total_students || '50+' }}</div>
                  <div class="text-caption text-medium-emphasis font-weight-medium">Peserta Belajar</div>
                </div>
                <VDivider vertical style="height: 36px;" />
                <div class="stat-item">
                  <div class="text-h4 font-weight-black text-info">{{ stats.total_tenants || '5+' }}</div>
                  <div class="text-caption text-medium-emphasis font-weight-medium">Lembaga Terdaftar</div>
                </div>
              </div>
            </VCol>

            <!-- Right: Hero Visual with Existing Material Illustration -->
            <VCol cols="12" lg="6" class="mt-10 mt-lg-0">
              <div class="hero-visual-wrapper position-relative text-center">
                <!-- Background Glowing Blob -->
                <div class="hero-glow-blob"></div>

                <!-- Main Illustration from Material Existing -->
                <div class="hero-illustration-box position-relative z-index-1">
                  <img
                    :src="laptopGirl"
                    alt="KITsuluh LMS Platform"
                    class="hero-illustration img-fluid"
                  />
                </div>

                <!-- Floating Glass Card 1: Course Progress -->
                <div class="floating-chip floating-chip-progress pa-3 rounded-xl border shadow-lg d-flex align-center ga-3">
                  <div class="chip-icon bg-primary pa-2 rounded-lg text-white d-flex align-center justify-center">
                    <VIcon icon="mdi-play-network" size="20" />
                  </div>
                  <div class="text-left">
                    <div class="text-caption font-weight-bold chip-title">Web & Mobile Vokasi</div>
                    <div class="d-flex align-center ga-2 mt-1">
                      <VProgressLinear model-value="85" color="primary" height="5" rounded style="width: 80px;" />
                      <span class="text-caption text-primary font-weight-bold">85%</span>
                    </div>
                  </div>
                </div>

                <!-- Floating Glass Card 2: Certificate Badge -->
                <div class="floating-chip floating-chip-cert pa-3 rounded-xl border shadow-lg d-flex align-center ga-3">
                  <div class="chip-icon bg-success-subtle pa-2 rounded-lg text-success d-flex align-center justify-center">
                    <VIcon icon="mdi-certificate-outline" size="22" />
                  </div>
                  <div class="text-left">
                    <div class="text-caption font-weight-bold chip-title">Sertifikat Digital</div>
                    <div class="text-caption text-success font-weight-semibold">Terbit Otomatis • QR Code</div>
                  </div>
                </div>

                <!-- Floating Glass Card 3: Gamification XP -->
                <div class="floating-chip floating-chip-xp pa-2 px-3 rounded-pill border shadow-md d-flex align-center ga-2">
                  <VIcon icon="mdi-trophy-variant" color="warning" size="18" />
                  <span class="text-caption font-weight-bold text-warning-darken-2">+250 XP Diperoleh!</span>
                </div>
              </div>
            </VCol>
          </VRow>
        </div>
      </section>

      <!-- ================= 1-CLICK INSTANT DEMO SECTION ================= -->
      <section id="demo" class="demo-section py-16 bg-surface-variant-subtle border-y">
        <div class="landing-container">
          <div class="text-center mb-12">
            <VChip color="primary" size="small" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
              Uji Coba Langsung Tanpa Password
            </VChip>
            <h2 class="section-title text-h4 text-md-h3 font-weight-black text-high-emphasis mb-3">
              Eksplorasi Demo Interaktif 1-Klik
            </h2>
            <p class="section-subtitle text-body-1 text-medium-emphasis mx-auto" style="max-width: 640px;">
              Uji langsung kehandalan KITsuluh sesuai peran yang Anda inginkan. Cukup klik tombol di bawah untuk masuk ke dasbor aktif tanpa perlu registrasi.
            </p>
          </div>

          <!-- 3 Role Cards -->
          <VRow justify="center">
            <!-- 1. Tenant Admin -->
            <VCol cols="12" md="4">
              <VCard elevation="2" rounded="xl" class="role-interactive-card border h-100 pa-6 d-flex flex-column">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="role-icon-box rounded-xl bg-primary-subtle text-primary d-flex align-center justify-center">
                    <VIcon icon="mdi-shield-crown-outline" size="30" />
                  </div>
                  <VChip size="small" color="primary" variant="flat" class="font-weight-bold">
                    Admin Institusi
                  </VChip>
                </div>

                <h3 class="text-h6 font-weight-bold text-high-emphasis mb-2">
                  Admin Lembaga (Tenant)
                </h3>
                <p class="text-body-2 text-medium-emphasis mb-6 flex-grow-1" style="line-height: 1.6;">
                  Pusat kendali organisasi: kelola kursus, pantau seluruh siswa & instruktur, atur branding logo dan tema lembaga, serta tinjau rekap nilai kelulusan.
                </p>

                <div class="role-checklist mb-6">
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mb-2">
                    <VIcon icon="mdi-check-circle" color="primary" size="16" />
                    <span>Kelola Silabus & Kurikulum Kursus</span>
                  </div>
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mb-2">
                    <VIcon icon="mdi-check-circle" color="primary" size="16" />
                    <span>Manajemen Instruktur & Siswa</span>
                  </div>
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-check-circle" color="primary" size="16" />
                    <span>Pengaturan Subdomain & Sertifikat</span>
                  </div>
                </div>

                <VBtn
                  color="primary"
                  block
                  size="large"
                  prepend-icon="mdi-login"
                  class="font-weight-bold"
                  :loading="demoLoadingRole === 'tenant_admin'"
                  :disabled="demoLoadingRole !== null"
                  @click="triggerDemoLogin('tenant_admin')"
                >
                  Masuk Sebagai Admin Tenant
                </VBtn>
              </VCard>
            </VCol>

            <!-- 2. Instruktur -->
            <VCol cols="12" md="4">
              <VCard elevation="2" rounded="xl" class="role-interactive-card border h-100 pa-6 d-flex flex-column">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="role-icon-box rounded-xl bg-info-subtle text-info d-flex align-center justify-center">
                    <VIcon icon="mdi-teach" size="30" />
                  </div>
                  <VChip size="small" color="info" variant="flat" class="font-weight-bold">
                    Instruktur
                  </VChip>
                </div>

                <h3 class="text-h6 font-weight-bold text-high-emphasis mb-2">
                  Instruktur / Pengajar
                </h3>
                <p class="text-body-2 text-medium-emphasis mb-6 flex-grow-1" style="line-height: 1.6;">
                  Rasakan kemudahan menyusun modul video & materi bacaan, membuat bank soal kuis pilihan ganda, menilai tugas siswa, dan membuka forum diskusi.
                </p>

                <div class="role-checklist mb-6">
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mb-2">
                    <VIcon icon="mdi-check-circle" color="info" size="16" />
                    <span>Upload Modul & Video Streaming HLS</span>
                  </div>
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mb-2">
                    <VIcon icon="mdi-check-circle" color="info" size="16" />
                    <span>Bank Soal & Penilaian Kuis Otomatis</span>
                  </div>
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-check-circle" color="info" size="16" />
                    <span>Tanya Jawab & Diskusi Interaktif</span>
                  </div>
                </div>

                <VBtn
                  color="info"
                  block
                  size="large"
                  prepend-icon="mdi-login"
                  class="font-weight-bold text-white"
                  :loading="demoLoadingRole === 'instructor'"
                  :disabled="demoLoadingRole !== null"
                  @click="triggerDemoLogin('instructor')"
                >
                  Masuk Sebagai Instruktur
                </VBtn>
              </VCard>
            </VCol>

            <!-- 3. Siswa / Peserta -->
            <VCol cols="12" md="4">
              <VCard elevation="2" rounded="xl" class="role-interactive-card border h-100 pa-6 d-flex flex-column">
                <div class="d-flex align-center justify-space-between mb-4">
                  <div class="role-icon-box rounded-xl bg-success-subtle text-success d-flex align-center justify-center">
                    <VIcon icon="mdi-school-outline" size="30" />
                  </div>
                  <VChip size="small" color="success" variant="flat" class="font-weight-bold">
                    Peserta Belajar
                  </VChip>
                </div>

                <h3 class="text-h6 font-weight-bold text-high-emphasis mb-2">
                  Peserta / Siswa
                </h3>
                <p class="text-body-2 text-medium-emphasis mb-6 flex-grow-1" style="line-height: 1.6;">
                  Uji antarmuka ruang belajar modern yang responsif. Tonton video lancar bebas buffering, kumpulkan XP, raih badges kompetensi, dan unduh sertifikat PDF resmi.
                </p>

                <div class="role-checklist mb-6">
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mb-2">
                    <VIcon icon="mdi-check-circle" color="success" size="16" />
                    <span>Pemutar Video Materi & Dokumen Interaktif</span>
                  </div>
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mb-2">
                    <VIcon icon="mdi-check-circle" color="success" size="16" />
                    <span>Poin XP, Badges & Papan Peringkat</span>
                  </div>
                  <div class="d-flex align-center ga-2 text-caption text-medium-emphasis">
                    <VIcon icon="mdi-check-circle" color="success" size="16" />
                    <span>Unduh Sertifikat PDF Resmi Instan</span>
                  </div>
                </div>

                <VBtn
                  color="success"
                  block
                  size="large"
                  prepend-icon="mdi-login"
                  class="font-weight-bold text-white"
                  :loading="demoLoadingRole === 'student'"
                  :disabled="demoLoadingRole !== null"
                  @click="triggerDemoLogin('student')"
                >
                  Masuk Sebagai Siswa
                </VBtn>
              </VCard>
            </VCol>
          </VRow>

          <!-- Superadmin Quick Access Pill -->
          <div class="mt-8 text-center">
            <div class="d-inline-flex align-center ga-3 pa-2 px-5 rounded-pill border bg-surface shadow-sm flex-wrap justify-center">
              <div class="d-flex align-center ga-2">
                <VIcon icon="mdi-shield-crown" color="warning" size="20" />
                <span class="text-caption text-medium-emphasis font-weight-medium">
                  Manajemen Platform Multi-Tenant:
                </span>
              </div>
              <VBtn
                size="small"
                variant="tonal"
                color="primary"
                prepend-icon="mdi-account-cog"
                class="font-weight-bold"
                :loading="demoLoadingRole === 'superadmin'"
                :disabled="demoLoadingRole !== null"
                @click="triggerDemoLogin('superadmin')"
              >
                Masuk Sebagai Super Admin
              </VBtn>
            </div>
          </div>
        </div>
      </section>

      <!-- ================= FITUR UNGGULAN SECTION ================= -->
      <section id="fitur" class="py-16">
        <div class="landing-container">
          <div class="text-center mb-12">
            <VChip color="primary" size="small" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
              Solusi Lengkap & Handal
            </VChip>
            <h2 class="section-title text-h4 text-md-h3 font-weight-black text-high-emphasis mb-3">
              Fitur Lengkap Sesuai Kebutuhan Anda
            </h2>
            <p class="section-subtitle text-body-1 text-medium-emphasis mx-auto" style="max-width: 600px;">
              Dirancang dengan standar arsitektur modern untuk memastikan kenyamanan belajar dan kemudahan tata kelola lembaga.
            </p>
          </div>

          <VRow>
            <VCol
              v-for="(item, index) in keyFeatures"
              :key="index"
              cols="12"
              md="4"
            >
              <VCard
                elevation="0"
                rounded="xl"
                class="feature-card border h-100 pa-6 d-flex flex-column"
              >
                <!-- Top Row: Icon + Badge -->
                <div class="d-flex align-center justify-space-between mb-4">
                  <div
                    class="feature-icon-wrapper rounded-xl d-flex align-center justify-center"
                    :class="`bg-${item.color}-subtle text-${item.color}`"
                  >
                    <VIcon :icon="item.icon" size="28" />
                  </div>
                  <VChip
                    size="small"
                    :color="item.color"
                    variant="tonal"
                    class="font-weight-semibold text-caption"
                  >
                    {{ item.badge }}
                  </VChip>
                </div>

                <!-- Title & Description -->
                <h3 class="text-h6 font-weight-bold text-high-emphasis mb-2">
                  {{ item.title }}
                </h3>
                <p class="text-body-2 text-medium-emphasis mb-5 flex-grow-1" style="line-height: 1.6;">
                  {{ item.description }}
                </p>

                <!-- Tags / Badges -->
                <div class="d-flex flex-wrap ga-2 pt-3 border-t mt-auto">
                  <span
                    v-for="(tag, tagIdx) in item.tags"
                    :key="tagIdx"
                    class="feature-tag-chip text-caption d-inline-flex align-center ga-1"
                  >
                    <VIcon icon="mdi-check-circle" size="14" :color="item.color" />
                    <span>{{ tag }}</span>
                  </span>
                </div>
              </VCard>
            </VCol>
          </VRow>
        </div>
      </section>

      <!-- ================= ALUR BERGABUNG TENANT ================= -->
      <section id="alur" class="py-16 bg-surface-variant-subtle border-t">
        <div class="landing-container">
          <div class="text-center mb-12">
            <VChip color="primary" size="small" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
              Kemudahan Implementasi
            </VChip>
            <h2 class="section-title text-h4 text-md-h3 font-weight-black text-high-emphasis mb-3">
              Langkah Mudah Membuka Portal Pembelajaran
            </h2>
            <p class="section-subtitle text-body-1 text-medium-emphasis mx-auto" style="max-width: 600px;">
              Mulai dari pendaftaran formulir hingga kelas aktif hanya memerlukan 4 langkah terstruktur.
            </p>
          </div>

          <VRow>
            <VCol
              v-for="(step, idx) in steps"
              :key="idx"
              cols="12"
              sm="6"
              lg="3"
            >
              <VCard elevation="1" rounded="xl" class="step-process-card border bg-surface pa-6 h-100">
                <div class="step-num text-h3 font-weight-black mb-3" :class="`text-${step.color}`">
                  {{ step.number }}
                </div>
                <h4 class="text-h6 font-weight-bold text-high-emphasis mb-2">
                  {{ step.title }}
                </h4>
                <p class="text-body-2 text-medium-emphasis mb-0" style="line-height: 1.6;">
                  {{ step.description }}
                </p>
              </VCard>
            </VCol>
          </VRow>

          <div class="text-center mt-10">
            <VBtn
              color="primary"
              size="large"
              elevation="2"
              prepend-icon="mdi-domain-plus"
              class="font-weight-bold px-8"
              :href="route('register-tenant')"
            >
              Daftar Sebagai Tenant Sekarang
            </VBtn>
          </div>
        </div>
      </section>

      <!-- ================= TESTIMONI PENGGUNA ================= -->
      <section id="testimoni" class="py-16">
        <div class="landing-container">
          <div class="text-center mb-12">
            <VChip color="success" size="small" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
              Pengalaman Pengguna
            </VChip>
            <h2 class="section-title text-h4 text-md-h3 font-weight-black text-high-emphasis mb-3">
              Dipercaya oleh Pengajar & Institusi
            </h2>
            <p class="section-subtitle text-body-1 text-medium-emphasis mx-auto" style="max-width: 600px;">
              Kisah sukses para pimpinan institusi dan peserta yang mentransformasi pendidikan bersama KITsuluh.
            </p>
          </div>

          <VRow>
            <VCol
              v-for="(testi, tIdx) in testimonials"
              :key="tIdx"
              cols="12"
              md="4"
            >
              <VCard elevation="1" rounded="xl" class="testimonial-card border h-100 pa-6 d-flex flex-column">
                <!-- Star Rating -->
                <div class="d-flex align-center ga-1 mb-4 text-warning">
                  <VIcon v-for="star in 5" :key="star" icon="mdi-star" size="18" />
                </div>

                <p class="text-body-2 text-high-emphasis mb-6 flex-grow-1 fst-italic" style="line-height: 1.7;">
                  "{{ testi.comment }}"
                </p>

                <!-- Profile Info -->
                <div class="d-flex align-center ga-3 pt-3 border-t">
                  <VAvatar size="46" class="border">
                    <img :src="testi.avatar" :alt="testi.name" />
                  </VAvatar>
                  <div>
                    <div class="text-body-2 font-weight-bold text-high-emphasis">{{ testi.name }}</div>
                    <div class="text-caption text-primary font-weight-medium">{{ testi.role }}</div>
                    <div class="text-caption text-medium-emphasis">{{ testi.org }}</div>
                  </div>
                </div>
              </VCard>
            </VCol>
          </VRow>
        </div>
      </section>

      <!-- ================= PAKET BERLANGGANAN SAAS ================= -->
      <section v-if="plans && plans.length" id="harga" class="py-16 bg-surface-variant-subtle border-t">
        <div class="landing-container">
          <div class="text-center mb-12">
            <VChip color="primary" size="small" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
              Skema Investasi Terjangkau
            </VChip>
            <h2 class="section-title text-h4 text-md-h3 font-weight-black text-high-emphasis mb-3">
              Pilihan Paket Berlangganan SaaS
            </h2>
            <p class="section-subtitle text-body-1 text-medium-emphasis mx-auto" style="max-width: 650px;">
              Pilih kapasitas yang sesuai dengan skala peserta didik Anda. Seluruh paket mencakup isolasi subdomain dan backup berkala.
            </p>
          </div>

          <VRow justify="center">
            <VCol
              v-for="plan in plans"
              :key="plan.id"
              cols="12"
              md="4"
            >
              <VCard
                elevation="2"
                rounded="xl"
                class="pricing-plan-card border h-100 pa-6 d-flex flex-column"
                :class="{ 'border-primary pricing-featured-card elevation-4': plan.badge }"
              >
                <!-- Top Row -->
                <div class="d-flex align-center justify-space-between mb-3">
                  <h3 class="text-h5 font-weight-bold text-high-emphasis mb-0">
                    {{ plan.name }}
                  </h3>
                  <VChip
                    v-if="plan.badge"
                    color="primary"
                    size="small"
                    variant="flat"
                    class="font-weight-bold text-caption"
                  >
                    {{ plan.badge }}
                  </VChip>
                </div>

                <p class="text-caption text-medium-emphasis mb-4" style="min-height: 40px; line-height: 1.5;">
                  {{ plan.description }}
                </p>

                <!-- Price Box -->
                <div class="pa-4 rounded-xl bg-surface border mb-5">
                  <div class="d-flex align-baseline ga-1">
                    <span class="text-h4 font-weight-black text-primary">
                      {{ formatRupiah(plan.price) }}
                    </span>
                    <span class="text-caption text-medium-emphasis">
                      / {{ plan.billing_period }}
                    </span>
                  </div>
                  <div class="text-caption text-success font-weight-medium mt-1 d-flex align-center ga-1">
                    <VIcon icon="mdi-shield-check" size="14" />
                    <span>Pembayaran QRIS & Virtual Account</span>
                  </div>
                </div>

                <!-- Limits Chips -->
                <div class="d-flex flex-wrap ga-2 mb-4">
                  <VChip size="small" variant="tonal" color="primary">
                    <VIcon start icon="mdi-account-group" size="14" />
                    {{ plan.max_users ? `Maks. ${plan.max_users} Siswa` : 'Unlimited Siswa' }}
                  </VChip>
                  <VChip size="small" variant="tonal" color="info">
                    <VIcon start icon="mdi-book-multiple" size="14" />
                    {{ plan.max_courses ? `Maks. ${plan.max_courses} Kursus` : 'Unlimited Kursus' }}
                  </VChip>
                </div>

                <VDivider class="my-3" />

                <!-- Feature List -->
                <div class="text-caption font-weight-bold text-uppercase text-medium-emphasis mb-3">
                  Fitur Paket:
                </div>
                <div class="d-flex flex-column ga-2 mb-6 flex-grow-1">
                  <div
                    v-for="(feature, featIdx) in plan.features"
                    :key="featIdx"
                    class="d-flex align-start ga-2 text-caption"
                  >
                    <VIcon icon="mdi-check-circle" size="16" color="primary" class="flex-shrink-0 mt-1" />
                    <span class="text-high-emphasis">{{ feature }}</span>
                  </div>
                </div>

                <!-- Button CTA -->
                <VBtn
                  :color="plan.badge ? 'primary' : 'secondary'"
                  :variant="plan.badge ? 'elevated' : 'tonal'"
                  size="large"
                  block
                  prepend-icon="mdi-domain-plus"
                  class="font-weight-bold mt-auto"
                  :href="`${route('register-tenant')}?plan=${plan.slug}`"
                >
                  Pilih Paket {{ plan.name }}
                </VBtn>
              </VCard>
            </VCol>
          </VRow>
        </div>
      </section>

      <!-- ================= FAQ SECTION ================= -->
      <section id="faq" class="py-16">
        <div class="landing-container">
          <div class="text-center mb-12">
            <VChip color="primary" size="small" variant="tonal" class="mb-3 font-weight-bold text-uppercase">
              Tanya Jawab
            </VChip>
            <h2 class="section-title text-h4 text-md-h3 font-weight-black text-high-emphasis mb-3">
              Pertanyaan yang Sering Diajukan
            </h2>
            <p class="section-subtitle text-body-1 text-medium-emphasis mx-auto" style="max-width: 600px;">
              Temukan jawaban atas pertanyaan umum seputar fitur, lisensi multi-tenant, dan aktivasi portal.
            </p>
          </div>

          <div class="faq-accordion-wrap mx-auto" style="max-width: 860px;">
            <VExpansionPanels variant="accordion" class="custom-faq-panels">
              <VExpansionPanel
                v-for="(faq, fIdx) in faqs"
                :key="fIdx"
                rounded="xl"
                class="border mb-3 overflow-hidden"
              >
                <VExpansionPanelTitle class="font-weight-bold text-body-1 py-4">
                  <div class="d-flex align-center ga-3">
                    <VIcon icon="mdi-help-circle-outline" color="primary" size="20" />
                    <span>{{ faq.q }}</span>
                  </div>
                </VExpansionPanelTitle>
                <VExpansionPanelText class="text-body-2 text-medium-emphasis pt-2 pb-4" style="line-height: 1.7;">
                  {{ faq.a }}
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </div>
        </div>
      </section>

      <!-- ================= CTA BANNER ================= -->
      <section class="py-16">
        <div class="landing-container">
          <VCard elevation="6" rounded="xl" class="pa-8 pa-md-14 text-center text-white bg-gradient-cta border-0 overflow-hidden position-relative">
            <!-- Background Watermark Glow -->
            <div class="cta-glow-circle"></div>

            <div class="position-relative z-index-1">
              <h2 class="text-h4 text-md-h3 font-weight-black mb-4">
                Siap Mentransformasi Pembelajaran di Organisasi Anda?
              </h2>
              <p class="text-body-1 mb-8 opacity-90 mx-auto" style="max-width: 640px; line-height: 1.7;">
                Bergabunglah bersama ribuan peserta dan puluhan lembaga yang telah menggunakan KITsuluh untuk pelatihan berkualitas dan sertifikasi terpercaya.
              </p>

              <div class="d-flex flex-wrap align-center justify-center ga-4">
                <VBtn
                  color="white"
                  size="x-large"
                  class="text-primary font-weight-bold px-8 elevation-2"
                  :href="route('register-tenant')"
                >
                  Daftar Tenant Sekarang
                </VBtn>

                <VBtn
                  v-if="getWhatsappUrl()"
                  color="white"
                  variant="outlined"
                  size="x-large"
                  prepend-icon="mdi-whatsapp"
                  :href="getWhatsappUrl()"
                  target="_blank"
                  class="font-weight-bold px-6"
                >
                  Konsultasi WhatsApp
                </VBtn>
              </div>
            </div>
          </VCard>
        </div>
      </section>

      <!-- ================= FOOTER ================= -->
      <footer class="landing-footer py-12 border-t bg-surface">
        <div class="landing-container">
          <VRow justify="space-between">
            <VCol cols="12" md="5" class="mb-6 mb-md-0">
              <div class="d-flex align-center ga-3 mb-3">
                <img src="/img/logok.png" alt="KITsuluh" height="40" />
                <span class="text-h6 font-weight-black text-primary">KITsuluh</span>
              </div>
              <p class="text-body-2 text-medium-emphasis mb-4" style="max-width: 400px; line-height: 1.6;">
                Platform Learning Management System multi-tenant modern dengan kurikulum adaptif, streaming hemat kuota, dan sertifikasi digital terverifikasi.
              </p>
              <div class="text-caption text-medium-emphasis">
                &copy; {{ new Date().getFullYear() }} KITsuluh. All rights reserved.
              </div>
            </VCol>

            <VCol cols="6" md="3">
              <div class="font-weight-bold text-body-2 text-high-emphasis mb-3">Navigasi Cepat</div>
              <div class="d-flex flex-column ga-2 text-body-2">
                <a href="#fitur" class="footer-link" @click.prevent="scrollToSection('fitur')">Fitur Utama</a>
                <a href="#demo" class="footer-link" @click.prevent="scrollToSection('demo')">Coba Demo 1-Klik</a>
                <a href="#alur" class="footer-link" @click.prevent="scrollToSection('alur')">Alur Pendaftaran</a>
                <a href="#testimoni" class="footer-link" @click.prevent="scrollToSection('testimoni')">Testimoni</a>
                <a v-if="plans && plans.length" href="#harga" class="footer-link" @click.prevent="scrollToSection('harga')">Harga Paket</a>
                <a href="#faq" class="footer-link" @click.prevent="scrollToSection('faq')">FAQ</a>
                <Link :href="route('login')" class="footer-link">Halaman Masuk</Link>
              </div>
            </VCol>

            <VCol cols="6" md="4">
              <div class="font-weight-bold text-body-2 text-high-emphasis mb-3">Layanan & Bantuan</div>
              <div class="d-flex flex-column ga-3 text-body-2 text-medium-emphasis">
                <div v-if="cs?.phone_number" class="d-flex align-center ga-2">
                  <VIcon icon="mdi-whatsapp" color="success" size="18" />
                  <span>WhatsApp: {{ cs.phone_number }} ({{ cs.name || 'CS' }})</span>
                </div>
                <div class="d-flex align-center ga-2">
                  <VIcon icon="mdi-shield-check" color="primary" size="18" />
                  <span>Isolasi Multi-Tenant Aman & Terverifikasi</span>
                </div>
                <div class="d-flex align-center ga-2">
                  <VIcon icon="mdi-clock-outline" color="info" size="18" />
                  <span>Layanan Bantuan 08:00 - 17:00 WIB</span>
                </div>
              </div>
            </VCol>
          </VRow>
        </div>
      </footer>

      <!-- ================= SCROLL TO TOP FLOATING BUTTON ================= -->
      <Transition name="fade-slide">
        <VBtn
          v-if="showScrollTop"
          icon="mdi-arrow-up"
          color="primary"
          elevation="4"
          size="default"
          class="scroll-to-top-btn"
          @click="scrollToTop"
        />
      </Transition>
    </div>
  </VApp>
</template>

<style scoped>
/* Root App and Page Configuration */
.landing-app {
  background-color: #fafafa !important;
  font-family: inherit;
  color: #2f2b3d;
}

.landing-page {
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
}

/* Ensure smooth section scrolling */
html {
  scroll-behavior: smooth;
}

section {
  scroll-margin-top: 85px;
}

.landing-container {
  max-width: 1240px;
  margin: 0 auto;
  padding-left: 24px;
  padding-right: 24px;
}

/* Header & Sticky Navbar */
.landing-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(47, 43, 61, 0.08);
  transition: all 0.3s ease;
}

.brand-link {
  color: inherit;
}

.brand-logo-wrap {
  width: 44px;
  height: 44px;
}

.brand-name {
  letter-spacing: -0.5px;
}

.nav-item-link {
  color: #5d596c;
  text-decoration: none;
  font-weight: 500;
  font-size: 15px;
  padding: 8px 12px;
  border-radius: 8px;
  transition: color 0.2s ease, background-color 0.2s ease;
}

.nav-item-link:hover {
  color: #7367f0;
  background-color: rgba(115, 103, 240, 0.06);
}

.footer-link {
  color: #5d596c;
  text-decoration: none;
  transition: color 0.2s ease, transform 0.2s ease;
  display: inline-block;
}

.footer-link:hover {
  color: #7367f0;
  transform: translateX(3px);
}

.active-user-ribbon {
  background: rgba(115, 103, 240, 0.08);
}

/* Hero Section */
.hero-section {
  background: radial-gradient(circle at 85% 15%, rgba(115, 103, 240, 0.12) 0%, rgba(250, 250, 250, 0) 65%);
}

.pulse-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #7367f0;
  box-shadow: 0 0 0 rgba(115, 103, 240, 0.5);
  animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
  0% {
    box-shadow: 0 0 0 0 rgba(115, 103, 240, 0.7);
  }
  70% {
    box-shadow: 0 0 0 8px rgba(115, 103, 240, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(115, 103, 240, 0);
  }
}

.text-gradient {
  background: linear-gradient(135deg, #7367f0 0%, #00bad1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-headline {
  color: #232030 !important;
  font-weight: 900 !important;
  letter-spacing: -0.5px;
}

.hero-description {
  color: #4b465c !important;
  font-size: 1.0625rem !important;
  line-height: 1.75 !important;
  font-weight: 400 !important;
}

.hero-btn-main {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hero-btn-main:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(115, 103, 240, 0.3) !important;
}

/* Hero Visual */
.hero-visual-wrapper {
  padding: 20px;
}

.hero-glow-blob {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 380px;
  height: 380px;
  background: radial-gradient(circle, rgba(115, 103, 240, 0.2) 0%, rgba(0, 186, 209, 0.1) 50%, transparent 75%);
  filter: blur(40px);
  z-index: 0;
  pointer-events: none;
}

.hero-illustration {
  max-width: 440px;
  width: 100%;
  height: auto;
  filter: drop-shadow(0 18px 32px rgba(47, 43, 61, 0.12));
  animation: float-main 6s ease-in-out infinite;
}

@keyframes float-main {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

/* Floating interactive chips */
.floating-chip {
  position: absolute;
  z-index: 2;
  background: #ffffff !important;
  color: #2f2b3d !important;
  border: 1px solid rgba(47, 43, 61, 0.12) !important;
  box-shadow: 0 10px 28px rgba(47, 43, 61, 0.14) !important;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  transition: transform 0.3s ease;
}

.chip-title {
  color: #2f2b3d !important;
}

.floating-chip:hover {
  transform: scale(1.05);
}

.floating-chip-progress {
  bottom: 24px;
  left: 0;
  animation: float-subtle 5s ease-in-out infinite 0.5s;
}

.floating-chip-cert {
  top: 20px;
  right: 10px;
  animation: float-subtle 5s ease-in-out infinite 1.2s;
}

.floating-chip-xp {
  top: 65%;
  right: -10px;
  animation: float-subtle 4.5s ease-in-out infinite 2s;
}

@keyframes float-subtle {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

/* Role Interactive Cards */
.role-interactive-card {
  transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease, border-color 0.3s ease;
  background-color: #ffffff !important;
}

.role-interactive-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 36px rgba(115, 103, 240, 0.12) !important;
  border-color: rgba(115, 103, 240, 0.4) !important;
}

.role-icon-box {
  width: 56px;
  height: 56px;
}

/* Features */
.feature-card {
  background-color: #ffffff !important;
  transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease, border-color 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 14px 32px rgba(115, 103, 240, 0.1) !important;
  border-color: rgba(115, 103, 240, 0.35) !important;
}

.feature-icon-wrapper {
  width: 52px;
  height: 52px;
  transition: transform 0.25s ease;
}

.feature-card:hover .feature-icon-wrapper {
  transform: scale(1.08);
}

.feature-tag-chip {
  color: #5d596c;
  font-weight: 500;
}

/* Process Steps */
.step-process-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background-color: #ffffff !important;
}

.step-process-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 28px rgba(47, 43, 61, 0.08) !important;
}

/* Testimonial Cards */
.testimonial-card {
  background-color: #ffffff !important;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.testimonial-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 14px 30px rgba(47, 43, 61, 0.08) !important;
}

/* Pricing Cards */
.pricing-plan-card {
  background-color: #ffffff !important;
  transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease, border-color 0.3s ease;
}

.pricing-plan-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 36px rgba(47, 43, 61, 0.1) !important;
}

.pricing-featured-card {
  border-color: #7367f0 !important;
  box-shadow: 0 10px 30px rgba(115, 103, 240, 0.18) !important;
}

/* FAQ */
.custom-faq-panels :deep(.v-expansion-panel) {
  background-color: #ffffff !important;
  transition: all 0.2s ease;
}

.custom-faq-panels :deep(.v-expansion-panel--active) {
  border-color: rgba(115, 103, 240, 0.4) !important;
  box-shadow: 0 8px 24px rgba(115, 103, 240, 0.08) !important;
}

/* CTA Banner */
.bg-gradient-cta {
  background: linear-gradient(135deg, #7367f0 0%, #5b4fe8 100%) !important;
}

.cta-glow-circle {
  position: absolute;
  top: -80px;
  right: -80px;
  width: 320px;
  height: 320px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.12);
  pointer-events: none;
}

/* Floating Scroll to Top */
.scroll-to-top-btn {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 999;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

/* Colors Subtle */
.bg-primary-subtle {
  background-color: rgba(115, 103, 240, 0.12);
}

.bg-info-subtle {
  background-color: rgba(0, 186, 209, 0.12);
}

.bg-success-subtle {
  background-color: rgba(40, 199, 111, 0.12);
}

.bg-warning-subtle {
  background-color: rgba(255, 159, 67, 0.12);
}

.bg-surface-variant-subtle {
  background-color: #f7f6f9;
}
</style>
