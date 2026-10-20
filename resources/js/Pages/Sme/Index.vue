<script setup>
import { ref } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  experts: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  skillCatalog: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  canVerify: { type: Boolean, default: false },
  currentUserId: { type: Number, required: true },
})

const search = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const verifiedOnly = ref(Boolean(props.filters.verified_only))
const isAddSkillDialogOpen = ref(false)

const addSkillForm = useForm({
  skill_id: null,
  proficiency_level: 'intermediate',
})

const applyFilters = () => {
  router.get(route('experts.index'), {
    search: search.value || undefined,
    category: selectedCategory.value || undefined,
    verified_only: verifiedOnly.value ? 1 : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const filterByCategory = (cat) => {
  selectedCategory.value = selectedCategory.value === cat ? '' : cat
  applyFilters()
}

const toggleVerifiedOnly = () => {
  verifiedOnly.value = !verifiedOnly.value
  applyFilters()
}

const resetFilters = () => {
  search.value = ''
  selectedCategory.value = ''
  verifiedOnly.value = false
  applyFilters()
}

const submitAddSkill = () => {
  addSkillForm.post(route('experts.skills.add'), {
    onSuccess: () => {
      isAddSkillDialogOpen.value = false
      addSkillForm.reset()
    },
  })
}

const endorseSkill = (userSkillId) => {
  router.post(route('experts.endorse', userSkillId), {}, {
    preserveScroll: true,
  })
}

const toggleVerifySkill = (userSkillId) => {
  router.post(route('experts.skills.verify', userSkillId), {}, {
    preserveScroll: true,
  })
}

const hasUserEndorsed = (endorsements) => {
  if (!endorsements || !Array.isArray(endorsements)) return false
  return endorsements.some(e => e.endorser_id === props.currentUserId || e.endorsed_by === props.currentUserId)
}

const getProficiencyColor = (level) => {
  switch (level) {
    case 'expert': return 'primary'
    case 'advanced': return 'success'
    case 'intermediate': return 'info'
    default: return 'secondary'
  }
}
</script>

<template>
  <div class="sme-directory-page pb-8">
    <!-- Hero Header -->
    <VCard
      rounded="xl"
      elevation="0"
      border
      class="pa-5 mb-5 hero-sme-card position-relative overflow-hidden"
    >
      <div class="d-flex align-center justify-space-between flex-wrap ga-4 position-relative">
        <div class="d-flex align-center ga-3">
          <div class="header-icon-box rounded-lg d-flex align-center justify-center">
            <VIcon icon="mdi-account-star-outline" size="28" color="primary" />
          </div>
          <div>
            <div class="d-flex align-center ga-2 mb-1 flex-wrap">
              <h5 class="text-h6 font-weight-bold mb-0 text-high-emphasis">
                Direktori Pakar & Subject Matter Expert (SME)
              </h5>
              <VChip size="x-small" color="primary" variant="tonal" class="font-weight-medium">
                Enterprise Talent & Knowledge Discovery
              </VChip>
            </div>
            <p class="text-caption text-medium-emphasis mb-0" style="max-width: 680px;">
              Temukan rekan kerja dengan keahlian spesifik di bidang operasional, teknis, atau manajerial. Berikan endorsement keahlian atau ajukan pertanyaan langsung untuk menyelesaikan kendala kerja harian.
            </p>
          </div>
        </div>

        <div class="d-flex align-center ga-2">
          <VBtn
            color="primary"
            variant="elevated"
            size="small"
            rounded="lg"
            prepend-icon="mdi-plus"
            class="font-weight-medium px-4"
            @click="isAddSkillDialogOpen = true"
          >
            Daftarkan Keahlian Saya
          </VBtn>
        </div>
      </div>

      <!-- Search & Quick Filters -->
      <div class="mt-4 pt-3 border-t d-flex align-center flex-wrap ga-2">
        <div class="flex-grow-1" style="min-width: 260px;">
          <VTextField
            v-model="search"
            placeholder="Cari pakar berdasarkan nama, departemen, atau kata kunci keahlian..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            rounded="lg"
            clearable
            @keydown.enter="applyFilters"
            @click:clear="resetFilters"
          />
        </div>
        <VBtn
          color="primary"
          variant="tonal"
          size="small"
          rounded="lg"
          class="px-4 font-weight-medium"
          @click="applyFilters"
        >
          Cari Pakar
        </VBtn>
        <VBtn
          :color="verifiedOnly ? 'warning' : 'default'"
          :variant="verifiedOnly ? 'flat' : 'tonal'"
          size="small"
          rounded="lg"
          prepend-icon="mdi-check-decagram"
          class="font-weight-medium"
          @click="toggleVerifiedOnly"
        >
          Pakar Terverifikasi Saja
        </VBtn>
      </div>

      <!-- Categories Pills -->
      <div v-if="categories.length > 0" class="d-flex align-center flex-wrap ga-2 mt-3 pt-2 border-t">
        <span class="text-caption font-weight-bold text-medium-emphasis me-1">Domain Keahlian:</span>
        <VChip
          :color="selectedCategory === '' ? 'primary' : 'default'"
          :variant="selectedCategory === '' ? 'flat' : 'tonal'"
          size="x-small"
          rounded="lg"
          class="cursor-pointer"
          @click="filterByCategory('')"
        >
          Semua Domain
        </VChip>
        <VChip
          v-for="cat in categories"
          :key="cat"
          :color="selectedCategory === cat ? 'primary' : 'default'"
          :variant="selectedCategory === cat ? 'flat' : 'tonal'"
          size="x-small"
          rounded="lg"
          class="cursor-pointer"
          @click="filterByCategory(cat)"
        >
          {{ cat }}
        </VChip>
      </div>
    </VCard>

    <!-- Experts Grid -->
    <div v-if="experts.data && experts.data.length > 0" class="experts-grid">
      <VCard
        v-for="expert in experts.data"
        :key="expert.id"
        rounded="xl"
        elevation="0"
        border
        class="expert-card d-flex flex-column pa-5"
      >
        <!-- Header: Avatar, Name & Department -->
        <div class="d-flex align-start ga-3 mb-4">
          <VAvatar size="48" color="primary" variant="tonal">
            <VImg v-if="expert.avatar_url" :src="expert.avatar_url" />
            <span v-else class="text-h6 font-weight-bold text-uppercase">
              {{ expert.name?.[0] }}
            </span>
          </VAvatar>

          <div class="flex-grow-1 text-truncate">
            <div class="d-flex align-center ga-1">
              <h6 class="text-subtitle-1 font-weight-bold text-high-emphasis text-truncate mb-0">
                {{ expert.name }}
              </h6>
              <VIcon
                v-if="expert.user_skills.some(s => s.is_verified)"
                icon="mdi-check-decagram"
                color="warning"
                size="18"
                title="Verified Subject Matter Expert"
              />
            </div>
            <p class="text-caption text-medium-emphasis mb-0 text-truncate">
              {{ expert.department || 'Divisi Operasional' }} • {{ expert.email }}
            </p>
          </div>
        </div>

        <!-- Skills List -->
        <div class="flex-grow-1 mb-4">
          <div class="text-caption font-weight-bold text-medium-emphasis mb-2">
            Keahlian & Pengakuan Rekan:
          </div>
          <div class="d-flex flex-column ga-2">
            <div
              v-for="userSkill in expert.user_skills"
              :key="userSkill.id"
              class="skill-item-row pa-2 px-3 rounded-lg border d-flex align-center justify-space-between flex-wrap ga-2"
            >
              <div class="d-flex align-center ga-2">
                <span class="font-weight-medium text-caption text-high-emphasis">
                  {{ userSkill.skill?.name || 'Keahlian' }}
                </span>
                <VChip
                  size="x-small"
                  :color="getProficiencyColor(userSkill.proficiency_level)"
                  variant="tonal"
                  class="text-uppercase"
                  style="font-size: 0.65rem;"
                >
                  {{ userSkill.proficiency_level }}
                </VChip>
                <VChip
                  v-if="userSkill.is_verified"
                  size="x-small"
                  color="warning"
                  variant="flat"
                  prepend-icon="mdi-shield-check"
                  style="font-size: 0.65rem;"
                >
                  SME Resmi
                </VChip>
              </div>

              <!-- Endorsement Pill & Action -->
              <div class="d-flex align-center ga-1">
                <VBtn
                  v-if="expert.id !== currentUserId"
                  variant="tonal"
                  size="x-small"
                  rounded="lg"
                  :color="hasUserEndorsed(userSkill.endorsements) ? 'primary' : 'default'"
                  prepend-icon="mdi-thumb-up-outline"
                  class="font-weight-bold px-2"
                  @click="endorseSkill(userSkill.id)"
                >
                  {{ userSkill.endorsements_count || 0 }} Endorse
                </VBtn>
                <VChip v-else size="x-small" variant="tonal" color="default" prepend-icon="mdi-thumb-up">
                  {{ userSkill.endorsements_count || 0 }}
                </VChip>

                <!-- Admin Verify Button -->
                <VBtn
                  v-if="canVerify"
                  icon="mdi-shield-star"
                  size="x-small"
                  variant="text"
                  :color="userSkill.is_verified ? 'warning' : 'secondary'"
                  :title="userSkill.is_verified ? 'Batalkan status verifikasi SME' : 'Verifikasi sebagai SME resmi'"
                  @click="toggleVerifySkill(userSkill.id)"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="pt-3 border-t d-flex ga-2">
          <VBtn
            :href="route('discussions.create', { topic: expert.user_skills[0]?.skill?.name })"
            color="primary"
            variant="tonal"
            size="small"
            rounded="lg"
            block
            prepend-icon="mdi-help-circle-outline"
            class="font-weight-medium"
          >
            Konsultasi / Tanya Kendala
          </VBtn>
        </div>
      </VCard>
    </div>

    <!-- Empty State -->
    <VCard
      v-else
      rounded="xl"
      elevation="0"
      border
      class="pa-10 text-center"
    >
      <VIcon icon="mdi-account-search-outline" size="56" color="secondary" class="mb-3 opacity-60" />
      <h6 class="text-h6 font-weight-bold mb-1">Tidak ada Pakar ditemukan</h6>
      <p class="text-caption text-medium-emphasis mb-4">
        Belum ada pengguna dengan keahlian yang terdaftar sesuai kriteria pencarian ini.
      </p>
      <VBtn
        color="primary"
        variant="elevated"
        size="small"
        rounded="lg"
        prepend-icon="mdi-plus"
        @click="isAddSkillDialogOpen = true"
      >
        Daftarkan Keahlian Anda Sekarang
      </VBtn>
    </VCard>

    <!-- Dialog Add Skill -->
    <VDialog v-model="isAddSkillDialogOpen" max-width="500px">
      <VCard rounded="xl" class="pa-2">
        <VCardTitle class="pa-4 d-flex align-center justify-space-between">
          <span class="text-h6 font-weight-bold">Daftarkan Keahlian Saya</span>
          <VBtn icon="mdi-close" variant="text" density="compact" @click="isAddSkillDialogOpen = false" />
        </VCardTitle>

        <VCardText class="pa-4 pt-0">
          <form @submit.prevent="submitAddSkill">
            <VSelect
              v-model="addSkillForm.skill_id"
              :items="skillCatalog"
              item-title="name"
              item-value="id"
              label="Pilih Keahlian dari Taksonomi *"
              placeholder="Misal: Agronomi, Troubleshooting Jaringan, dsb."
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
                { title: 'Advanced (Mahir / Mampu Mengarahkan Tim)', value: 'advanced' },
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
          <VBtn variant="text" rounded="lg" @click="isAddSkillDialogOpen = false">
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
.hero-sme-card {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, rgba(var(--v-theme-surface), 0.98) 100%);
}

.header-icon-box {
  width: 48px;
  height: 48px;
  background-color: rgba(var(--v-theme-primary), 0.12);
}

.experts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 16px;
}

.expert-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.expert-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.skill-item-row {
  background-color: rgba(var(--v-theme-surface), 1);
}
</style>
