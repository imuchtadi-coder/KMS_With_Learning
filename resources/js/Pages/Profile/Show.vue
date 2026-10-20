<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  user: { type: Object, required: true },
  skillCatalog: { type: Array, default: () => [] },
})

const page = usePage()
const flash = computed(() => page.props.flash || {})
const initials = computed(() => props.user.name?.charAt(0).toUpperCase() || 'U')

const isAddSkillModalOpen = ref(false)
const addSkillForm = useForm({
  skill_id: null,
  proficiency_level: 'intermediate',
})

const submitAddSkill = () => {
  addSkillForm.post(route('experts.skills.add'), {
    onSuccess: () => {
      isAddSkillModalOpen.value = false
      addSkillForm.reset()
    },
  })
}

const deleteSkill = (userSkillId) => {
  if (confirm('Hapus keahlian ini dari profil Anda?')) {
    router.delete(route('experts.skills.destroy', userSkillId))
  }
}

const memberSince = computed(() => {
  if (!props.user.created_at) return '-'
  const d = new Date(props.user.created_at)
  return d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
})

const formattedBirthday = computed(() => {
  if (!props.user.birthday) return null
  const d = new Date(props.user.birthday)
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
})

const roleBadgeColor = (roleName) => {
  if (roleName.includes('superadmin')) return 'error'
  if (roleName.includes('tenant_admin') || roleName.includes('admin')) return 'primary'
  if (roleName.includes('instructor')) return 'warning'
  return 'info'
}

const formatRoleName = (name) => {
  if (!name) return ''
  return name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}
</script>

<template>
  <div class="profile-page">
    <!-- Feedback Alert -->
    <VAlert
      v-if="flash.success"
      type="success"
      variant="tonal"
      closable
      class="mb-5 border-success"
    >
      {{ flash.success }}
    </VAlert>

    <!-- 1. Hero Profile Banner Card -->
    <VCard flat border class="overflow-hidden mb-5 rounded-lg">
      <!-- Decorative Ambient Gradient Top Banner -->
      <div class="profile-hero-banner position-relative">
        <div class="hero-overlay" />
      </div>

      <VCardText class="position-relative pt-0 px-4 px-md-6 pb-5">
        <div class="d-flex align-end justify-space-between flex-wrap ga-4 profile-header-content">
          <!-- Avatar + Identity Info -->
          <div class="d-flex align-end ga-4 flex-wrap">
            <VAvatar
              size="84"
              color="primary"
              variant="elevated"
              class="profile-avatar shadow-lg"
            >
              <VImg v-if="user.avatar_url && !user.avatar_url.includes('ui-avatars')" :src="user.avatar_url" />
              <span v-else class="text-h3 font-weight-bold text-white">{{ initials }}</span>
            </VAvatar>

            <div class="identity-info pt-2">
              <div class="d-flex align-center ga-2 flex-wrap mb-1">
                <h4 class="text-h4 font-weight-bold mb-0">{{ user.name }}</h4>
                <div v-if="user.roles?.length" class="d-flex ga-1">
                  <VChip
                    v-for="role in user.roles"
                    :key="role.id"
                    :color="roleBadgeColor(role.name)"
                    size="x-small"
                    variant="elevated"
                    class="font-weight-bold text-capitalize"
                  >
                    {{ formatRoleName(role.name) }}
                  </VChip>
                </div>
              </div>

              <div class="d-flex align-center ga-3 flex-wrap text-caption text-medium-emphasis">
                <span class="d-inline-flex align-center ga-1">
                  <VIcon icon="mdi-email-outline" size="14" />
                  {{ user.email }}
                </span>
                <span v-if="user.department" class="d-inline-flex align-center ga-1">
                  <VIcon icon="mdi-briefcase-outline" size="14" />
                  {{ user.department }}
                </span>
                <span v-if="user.tenant" class="d-inline-flex align-center ga-1">
                  <VIcon icon="mdi-domain" size="14" />
                  {{ user.tenant.name }}
                </span>
              </div>
            </div>
          </div>

          <!-- Edit Profile Action Button -->
          <div class="action-btn-wrapper">
            <Link :href="route('profile.edit')" class="text-decoration-none">
              <VBtn
                color="primary"
                variant="elevated"
                size="default"
                prepend-icon="mdi-account-edit-outline"
                class="font-weight-medium"
              >
                Ubah Profil & Kata Sandi
              </VBtn>
            </Link>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- 2. Metric Overview Cards -->
    <VRow class="mb-5">
      <VCol cols="12" sm="4">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">
                Kursus Terdaftar
              </span>
              <span class="text-h4 font-weight-bold text-primary mt-1 d-block">
                {{ user.enrollments?.length || 0 }}
              </span>
              <span class="text-caption text-medium-emphasis">
                Partisipasi pembelajaran aktif
              </span>
            </div>
            <VAvatar color="primary" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-book-multiple-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">
                Sertifikat Diraih
              </span>
              <span class="text-h4 font-weight-bold text-success mt-1 d-block">
                {{ user.certificates?.length || 0 }}
              </span>
              <span class="text-caption text-medium-emphasis">
                Bukti kompetensi terselesaikan
              </span>
            </div>
            <VAvatar color="success" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-certificate-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard flat border class="pa-4 h-100 metric-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">
                Anggota Sejak
              </span>
              <span class="text-h5 font-weight-bold text-high-emphasis mt-1 d-block">
                {{ memberSince }}
              </span>
              <span class="text-caption text-success font-weight-medium">
                <VIcon icon="mdi-shield-check" size="14" class="me-1" />
                Akun Terverifikasi
              </span>
            </div>
            <VAvatar color="warning" variant="tonal" size="44" rounded>
              <VIcon icon="mdi-calendar-check-outline" size="24" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- 3. Two-Column Detailed Information Layout -->
    <VRow>
      <!-- Left Column: Personal & Contact Information -->
      <VCol cols="12" md="7">
        <VCard flat border class="pa-5 h-100 rounded-lg">
          <div class="d-flex align-center ga-2 mb-4">
            <VIcon icon="mdi-card-account-details-outline" color="primary" size="22" />
            <h6 class="text-subtitle-1 font-weight-bold mb-0">Informasi Pribadi & Kontak</h6>
          </div>

          <div class="info-list d-flex flex-column ga-3">
            <!-- Full Name -->
            <div class="info-item d-flex align-start pa-3 rounded border bg-surface">
              <VIcon icon="mdi-account-outline" size="20" class="text-medium-emphasis me-3 mt-1" />
              <div class="flex-grow-1">
                <span class="text-caption text-medium-emphasis d-block">Nama Lengkap</span>
                <span class="text-body-2 font-weight-semibold">{{ user.name }}</span>
              </div>
            </div>

            <!-- Email Address -->
            <div class="info-item d-flex align-start pa-3 rounded border bg-surface">
              <VIcon icon="mdi-email-outline" size="20" class="text-medium-emphasis me-3 mt-1" />
              <div class="flex-grow-1">
                <span class="text-caption text-medium-emphasis d-block">Alamat Email</span>
                <span class="text-body-2 font-weight-semibold">{{ user.email }}</span>
              </div>
            </div>

            <!-- Phone Number -->
            <div class="info-item d-flex align-start pa-3 rounded border bg-surface">
              <VIcon icon="mdi-phone-outline" size="20" class="text-medium-emphasis me-3 mt-1" />
              <div class="flex-grow-1">
                <span class="text-caption text-medium-emphasis d-block">Nomor Telepon</span>
                <span class="text-body-2 font-weight-semibold">{{ user.phone || 'Belum diisi' }}</span>
              </div>
            </div>

            <!-- Birthday -->
            <div class="info-item d-flex align-start pa-3 rounded border bg-surface">
              <VIcon icon="mdi-cake-variant-outline" size="20" class="text-medium-emphasis me-3 mt-1" />
              <div class="flex-grow-1">
                <span class="text-caption text-medium-emphasis d-block">Tanggal Lahir</span>
                <span class="text-body-2 font-weight-semibold">{{ formattedBirthday || 'Belum diisi' }}</span>
              </div>
            </div>

            <!-- Department -->
            <div class="info-item d-flex align-start pa-3 rounded border bg-surface">
              <VIcon icon="mdi-briefcase-outline" size="20" class="text-medium-emphasis me-3 mt-1" />
              <div class="flex-grow-1">
                <span class="text-caption text-medium-emphasis d-block">Departemen / Divisi</span>
                <span class="text-body-2 font-weight-semibold">{{ user.department || 'Belum diisi' }}</span>
              </div>
            </div>

            <!-- Address -->
            <div class="info-item d-flex align-start pa-3 rounded border bg-surface">
              <VIcon icon="mdi-map-marker-outline" size="20" class="text-medium-emphasis me-3 mt-1" />
              <div class="flex-grow-1">
                <span class="text-caption text-medium-emphasis d-block">Alamat Domisili</span>
                <span class="text-body-2 font-weight-semibold">{{ user.address || 'Belum diisi' }}</span>
              </div>
            </div>
          </div>
        </VCard>
      </VCol>

      <!-- Right Column: Organization, Skills & Security Overview -->
      <VCol cols="12" md="5">
        <div class="d-flex flex-column ga-5">
          <!-- SME Skills & Expertise Card -->
          <VCard flat border class="pa-5 rounded-lg">
            <div class="d-flex align-center justify-space-between mb-3">
              <div class="d-flex align-center ga-2">
                <VIcon icon="mdi-account-star-outline" color="primary" size="22" />
                <h6 class="text-subtitle-1 font-weight-bold mb-0">Keahlian & Kepakaran (SME)</h6>
              </div>
              <VBtn
                color="primary"
                variant="tonal"
                size="x-small"
                rounded="lg"
                prepend-icon="mdi-plus"
                @click="isAddSkillModalOpen = true"
              >
                Tambah
              </VBtn>
            </div>

            <p class="text-caption text-medium-emphasis mb-3">
              Daftar keahlian profesional yang Anda kuasai dan diakui oleh rekan kerja melalui endorsement.
            </p>

            <div v-if="user.user_skills && user.user_skills.length > 0" class="d-flex flex-column ga-2 mb-3">
              <div
                v-for="us in user.user_skills"
                :key="us.id"
                class="pa-2 px-3 rounded border bg-surface d-flex align-center justify-space-between flex-wrap ga-2"
              >
                <div>
                  <div class="d-flex align-center ga-1">
                    <span class="text-caption font-weight-bold">{{ us.skill?.name }}</span>
                    <VIcon v-if="us.is_verified" icon="mdi-check-decagram" size="14" color="warning" title="SME Resmi Terverifikasi" />
                  </div>
                  <div class="d-flex align-center ga-2 mt-1">
                    <VChip size="x-small" color="primary" variant="tonal" class="text-uppercase" style="font-size: 0.65rem;">
                      {{ us.proficiency_level }}
                    </VChip>
                    <span class="text-caption text-medium-emphasis" style="font-size: 0.72rem;">
                      👍 {{ us.endorsements_count || 0 }} Endorsement
                    </span>
                  </div>
                </div>

                <VBtn
                  icon="mdi-close"
                  variant="text"
                  size="x-small"
                  color="secondary"
                  title="Hapus keahlian"
                  @click="deleteSkill(us.id)"
                />
              </div>
            </div>

            <div v-else class="text-center pa-4 border border-dashed rounded text-medium-emphasis mb-3 text-caption">
              Belum ada keahlian yang didaftarkan.
            </div>

            <Link :href="route('experts.index')" class="text-decoration-none d-block">
              <VBtn variant="text" color="primary" size="x-small" block append-icon="mdi-arrow-right">
                Buka Direktori Pakar Organisasi
              </VBtn>
            </Link>
          </VCard>

          <!-- Organization / Tenant Card -->
          <VCard flat border class="pa-5 rounded-lg">
            <div class="d-flex align-center ga-2 mb-3">
              <VIcon icon="mdi-domain" color="primary" size="22" />
              <h6 class="text-subtitle-1 font-weight-bold mb-0">Organisasi & Lembaga</h6>
            </div>

            <div class="pa-3 rounded border bg-surface mb-3">
              <span class="text-caption text-medium-emphasis d-block">Nama Lembaga (Tenant)</span>
              <span class="text-body-2 font-weight-bold text-primary">
                {{ user.tenant?.name || 'KMS Learning' }}
              </span>
              <span v-if="user.tenant?.address" class="text-caption text-medium-emphasis d-block mt-1">
                {{ user.tenant.address }}
              </span>
            </div>

            <div class="d-flex align-center justify-space-between text-caption text-medium-emphasis px-1">
              <span>Status Lisensi:</span>
              <VChip size="x-small" color="success" variant="tonal" class="font-weight-semibold">
                Aktif
              </VChip>
            </div>
          </VCard>

          <!-- Security & Password Card -->
          <VCard flat border class="pa-5 rounded-lg">
            <div class="d-flex align-center ga-2 mb-3">
              <VIcon icon="mdi-shield-lock-outline" color="primary" size="22" />
              <h6 class="text-subtitle-1 font-weight-bold mb-0">Keamanan & Sandi</h6>
            </div>

            <p class="text-caption text-medium-emphasis mb-4">
              Lindungi akun Anda secara berkala dengan kata sandi yang kuat dan tidak digunakan di layanan lain.
            </p>

            <div class="d-flex align-center justify-space-between pa-3 rounded border bg-surface mb-4">
              <div class="d-flex align-center ga-2">
                <VIcon icon="mdi-key-outline" size="18" class="text-medium-emphasis" />
                <span class="text-body-2 font-weight-medium">Kata Sandi Akun</span>
              </div>
              <span class="text-caption text-medium-emphasis font-mono">••••••••</span>
            </div>

            <Link :href="route('profile.edit')" class="text-decoration-none d-block">
              <VBtn
                variant="outlined"
                color="primary"
                size="small"
                block
                prepend-icon="mdi-lock-reset"
              >
                Ganti Kata Sandi
              </VBtn>
            </Link>
          </VCard>
        </div>
      </VCol>
    </VRow>

    <!-- Modal Dialog Tambah Keahlian -->
    <VDialog v-model="isAddSkillModalOpen" max-width="500px">
      <VCard rounded="xl" class="pa-2">
        <VCardTitle class="pa-4 d-flex align-center justify-space-between">
          <span class="text-h6 font-weight-bold">Tambah Keahlian Profesional</span>
          <VBtn icon="mdi-close" variant="text" density="compact" @click="isAddSkillModalOpen = false" />
        </VCardTitle>

        <VCardText class="pa-4 pt-0">
          <form @submit.prevent="submitAddSkill">
            <VSelect
              v-model="addSkillForm.skill_id"
              :items="skillCatalog"
              item-title="name"
              item-value="id"
              label="Keahlian dari Taksonomi *"
              placeholder="Pilih bidang keahlian Anda..."
              variant="outlined"
              density="compact"
              rounded="lg"
              class="mb-3"
              :error-messages="addSkillForm.errors.skill_id"
              required
            />

            <VSelect
              v-model="addSkillForm.proficiency_level"
              :items="[
                { title: 'Beginner (Pemula / Memahami Dasar)', value: 'beginner' },
                { title: 'Intermediate (Praktisi Berpengalaman)', value: 'intermediate' },
                { title: 'Advanced (Mahir / Mampu Mengarahkan)', value: 'advanced' },
                { title: 'Expert (Pakar / Problem Solver Utama)', value: 'expert' },
              ]"
              label="Tingkat Kemahiran (Proficiency) *"
              variant="outlined"
              density="compact"
              rounded="lg"
              class="mb-3"
            />
          </form>
        </VCardText>

        <VCardActions class="pa-4 pt-0 d-flex justify-end ga-2">
          <VBtn variant="text" rounded="lg" @click="isAddSkillModalOpen = false">
            Batal
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            rounded="lg"
            :loading="addSkillForm.processing"
            @click="submitAddSkill"
          >
            Simpan Keahlian
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.profile-hero-banner {
  height: 90px;
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.85) 0%, rgba(var(--v-theme-primary), 0.5) 100%);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.25) 0%, transparent 60%);
}

.profile-avatar {
  border: 4px solid rgb(var(--v-theme-surface)) !important;
  margin-top: -42px;
}

.metric-card {
  transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
}
.metric-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
}

.info-item {
  transition: border-color 0.15s ease-in-out;
}
.info-item:hover {
  border-color: rgba(var(--v-theme-primary), 0.35) !important;
}

.font-mono {
  font-family: monospace;
  letter-spacing: 2px;
}
</style>
