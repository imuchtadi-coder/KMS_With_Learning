<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  leaderboard: { type: Array, default: () => [] },
  courseId: { type: [String, Number], default: null },
  period: { type: String, default: 'all' },
  userStats: {
    type: Object,
    default: () => ({
      rank: null,
      total_xp: 0,
      level: 1,
      current_streak: 0,
      longest_streak: 0,
    }),
  },
  courses: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user || {})

const selectedPeriod = ref(props.period || 'all')
const selectedCourse = ref(props.courseId ? Number(props.courseId) : '')
const searchQuery = ref('')

function handlePeriodChange(newPeriod) {
  selectedPeriod.value = newPeriod
  applyFilter()
}

function applyFilter() {
  router.get(
    route('student.leaderboard.index'),
    {
      period: selectedPeriod.value,
      course_id: selectedCourse.value || undefined,
    },
    { preserveState: true, replace: true }
  )
}

function clearFilters() {
  selectedPeriod.value = 'all'
  selectedCourse.value = ''
  searchQuery.value = ''
  applyFilter()
}

// Podium Top 3
const topThree = computed(() => {
  return props.leaderboard.slice(0, 3)
})

const rankOne = computed(() => topThree.value[0] || null)
const rankTwo = computed(() => topThree.value[1] || null)
const rankThree = computed(() => topThree.value[2] || null)

// Table List (Remaining or Filtered)
const filteredLeaderboard = computed(() => {
  let list = props.leaderboard || []

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(entry =>
      entry.user?.name?.toLowerCase().includes(q)
    )
  }

  return list
})

function getInitials(name) {
  if (!name) return '?'
  return name.charAt(0).toUpperCase()
}

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID')
}
</script>

<template>
  <div class="leaderboard-page pb-12">
    <!-- Hero Header Banner -->
    <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
      <div class="pa-6 pa-md-8">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-4">
          <div class="d-flex align-center ga-4">
            <VAvatar
              size="56"
              color="warning"
              variant="tonal"
              rounded="lg"
              class="elevation-1"
            >
              <VIcon icon="mdi-trophy-variant" size="32" color="warning" />
            </VAvatar>
            <div>
              <div class="d-flex align-center ga-2 flex-wrap">
                <h4 class="text-h4 font-weight-bold text-high-emphasis mb-0">
                  Papan Peringkat
                </h4>
                <VChip size="small" color="warning" variant="tonal" label class="font-weight-medium">
                  Gamifikasi & XP
                </VChip>
              </div>
              <p class="text-body-2 text-medium-emphasis mt-1 mb-0">
                Kumpulkan poin XP dari setiap modul dan kuis, bangun streak belajar harian, dan raih posisi teratas!
              </p>
            </div>
          </div>

          <!-- Quick Navigation Pill for Period -->
          <div class="d-flex align-center ga-1 bg-background pa-1 rounded-pill border">
            <VBtn
              size="small"
              :variant="selectedPeriod === 'all' ? 'flat' : 'text'"
              :color="selectedPeriod === 'all' ? 'primary' : 'default'"
              rounded="pill"
              class="text-capitalize px-4"
              @click="handlePeriodChange('all')"
            >
              Semua Waktu
            </VBtn>
            <VBtn
              size="small"
              :variant="selectedPeriod === 'monthly' ? 'flat' : 'text'"
              :color="selectedPeriod === 'monthly' ? 'primary' : 'default'"
              rounded="pill"
              class="text-capitalize px-4"
              @click="handlePeriodChange('monthly')"
            >
              Bulan Ini
            </VBtn>
            <VBtn
              size="small"
              :variant="selectedPeriod === 'weekly' ? 'flat' : 'text'"
              :color="selectedPeriod === 'weekly' ? 'primary' : 'default'"
              rounded="pill"
              class="text-capitalize px-4"
              @click="handlePeriodChange('weekly')"
            >
              Minggu Ini
            </VBtn>
          </div>
        </div>

        <!-- Personal User Standing Card -->
        <VDivider class="my-6" />

        <div class="pa-4 rounded-xl bg-background border">
          <div class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between ga-4">
            <!-- User Info & Current Rank -->
            <div class="d-flex align-center ga-4">
              <VAvatar
                size="48"
                color="primary"
                variant="tonal"
                class="font-weight-bold"
                :image="authUser.avatar_url"
              >
                <span v-if="!authUser.avatar_url">{{ getInitials(authUser.name) }}</span>
              </VAvatar>

              <div>
                <div class="d-flex align-center ga-2 flex-wrap">
                  <span class="text-subtitle-1 font-weight-bold text-high-emphasis">
                    {{ authUser.name }}
                  </span>
                  <VChip size="x-small" color="primary" variant="flat" label class="font-weight-bold">
                    Anda
                  </VChip>
                  <VChip size="x-small" color="info" variant="tonal" label class="font-weight-medium">
                    Level {{ userStats.level }}
                  </VChip>
                </div>

                <div class="d-flex align-center ga-2 text-caption text-medium-emphasis mt-1">
                  <VIcon icon="mdi-shield-star-outline" size="16" color="primary" />
                  <span>
                    Posisi Anda Saat Ini:
                    <strong class="text-high-emphasis">
                      {{ userStats.rank ? `Peringkat #${userStats.rank}` : 'Belum Masuk 50 Besar' }}
                    </strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- User 3 Metrics -->
            <div class="d-flex align-center ga-4 flex-wrap">
              <div class="text-center px-3 py-1 rounded bg-surface border">
                <div class="text-caption text-medium-emphasis">Total XP</div>
                <div class="text-h6 font-weight-bold text-primary">
                  {{ formatNumber(userStats.total_xp) }}
                </div>
              </div>

              <div class="text-center px-3 py-1 rounded bg-surface border">
                <div class="text-caption text-medium-emphasis">Streak Aktif</div>
                <div class="text-h6 font-weight-bold text-warning d-flex align-center justify-center ga-1">
                  <VIcon icon="mdi-fire" size="18" color="warning" />
                  {{ userStats.current_streak }} Hari
                </div>
              </div>

              <div class="text-center px-3 py-1 rounded bg-surface border">
                <div class="text-caption text-medium-emphasis">Rekor Streak</div>
                <div class="text-h6 font-weight-bold text-success d-flex align-center justify-center ga-1">
                  <VIcon icon="mdi-trophy-outline" size="18" color="success" />
                  {{ userStats.longest_streak }} Hari
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </VCard>

    <!-- Top 3 Podium Showcase -->
    <VCard
      v-if="topThree.length > 0"
      flat
      border
      class="mb-6 rounded-xl bg-surface pa-6 pa-md-8"
    >
      <div class="text-center mb-6">
        <h5 class="text-h5 font-weight-bold text-high-emphasis mb-1">
          Panggung Juara
        </h5>
        <p class="text-caption text-medium-emphasis mb-0">
          Peserta didik dengan perolehan XP tertinggi pada periode ini
        </p>
      </div>

      <div class="podium-wrapper d-flex align-end justify-center ga-3 ga-md-6 pt-6">
        <!-- Rank 2: Silver (Left) -->
        <div
          v-if="rankTwo"
          class="podium-item podium-two d-flex flex-column align-center text-center"
        >
          <div class="position-relative mb-2">
            <VAvatar
              size="64"
              color="secondary"
              variant="tonal"
              class="border-silver elevation-1"
              :image="rankTwo.user?.avatar_url"
            >
              <span v-if="!rankTwo.user?.avatar_url" class="text-h6 font-weight-bold">
                {{ getInitials(rankTwo.user?.name) }}
              </span>
            </VAvatar>
            <div class="podium-badge badge-silver">
              🥈 2
            </div>
          </div>

          <div class="text-subtitle-2 font-weight-bold text-high-emphasis text-truncate max-w-160 mb-1">
            {{ rankTwo.user?.name }}
          </div>
          <VChip size="x-small" color="secondary" variant="tonal" label class="mb-2">
            Lv. {{ rankTwo.level }}
          </VChip>

          <!-- Pedestal 2 -->
          <div class="pedestal pedestal-step-two pa-3 rounded-t-xl w-100 d-flex flex-column justify-center align-center">
            <span class="text-subtitle-1 font-weight-bold text-high-emphasis">
              {{ formatNumber(rankTwo.total_xp) }}
            </span>
            <span class="text-caption text-medium-emphasis">XP</span>
          </div>
        </div>

        <!-- Rank 1: Gold (Center - Elevated) -->
        <div
          v-if="rankOne"
          class="podium-item podium-one d-flex flex-column align-center text-center"
        >
          <div class="position-relative mb-2">
            <VIcon icon="mdi-crown" size="32" color="warning" class="crown-icon" />
            <VAvatar
              size="80"
              color="warning"
              variant="tonal"
              class="border-gold elevation-3"
              :image="rankOne.user?.avatar_url"
            >
              <span v-if="!rankOne.user?.avatar_url" class="text-h5 font-weight-bold">
                {{ getInitials(rankOne.user?.name) }}
              </span>
            </VAvatar>
            <div class="podium-badge badge-gold">
              🥇 1
            </div>
          </div>

          <div class="text-subtitle-1 font-weight-bold text-high-emphasis text-truncate max-w-180 mb-1">
            {{ rankOne.user?.name }}
          </div>
          <VChip size="x-small" color="warning" variant="flat" label class="font-weight-bold mb-2">
            Juara Utama • Lv. {{ rankOne.level }}
          </VChip>

          <!-- Pedestal 1 -->
          <div class="pedestal pedestal-step-one pa-4 rounded-t-xl w-100 d-flex flex-column justify-center align-center">
            <span class="text-h6 font-weight-bold text-warning">
              {{ formatNumber(rankOne.total_xp) }}
            </span>
            <span class="text-caption font-weight-bold text-warning">XP</span>
          </div>
        </div>

        <!-- Rank 3: Bronze (Right) -->
        <div
          v-if="rankThree"
          class="podium-item podium-three d-flex flex-column align-center text-center"
        >
          <div class="position-relative mb-2">
            <VAvatar
              size="60"
              color="warning"
              variant="tonal"
              class="border-bronze elevation-1"
              :image="rankThree.user?.avatar_url"
            >
              <span v-if="!rankThree.user?.avatar_url" class="text-subtitle-1 font-weight-bold">
                {{ getInitials(rankThree.user?.name) }}
              </span>
            </VAvatar>
            <div class="podium-badge badge-bronze">
              🥉 3
            </div>
          </div>

          <div class="text-subtitle-2 font-weight-bold text-high-emphasis text-truncate max-w-160 mb-1">
            {{ rankThree.user?.name }}
          </div>
          <VChip size="x-small" color="secondary" variant="tonal" label class="mb-2">
            Lv. {{ rankThree.level }}
          </VChip>

          <!-- Pedestal 3 -->
          <div class="pedestal pedestal-step-three pa-3 rounded-t-xl w-100 d-flex flex-column justify-center align-center">
            <span class="text-subtitle-1 font-weight-bold text-high-emphasis">
              {{ formatNumber(rankThree.total_xp) }}
            </span>
            <span class="text-caption text-medium-emphasis">XP</span>
          </div>
        </div>
      </div>
    </VCard>

    <!-- Toolbar: Course Filter & Search -->
    <VCard flat border class="mb-6 rounded-xl pa-4 bg-surface">
      <div class="d-flex flex-column flex-md-row justify-space-between align-stretch align-md-center ga-3">
        <!-- Scope Course Filter -->
        <div style="min-width: 260px; max-width: 380px;">
          <VSelect
            v-model="selectedCourse"
            :items="[
              { id: '', title: 'Seluruh Platform (Semua Kursus)' },
              ...courses
            ]"
            item-title="title"
            item-value="id"
            placeholder="Pilih Lingkup Kursus"
            density="compact"
            variant="outlined"
            hide-details
            @update:model-value="applyFilter"
          />
        </div>

        <!-- Search Student Name -->
        <div style="min-width: 240px; max-width: 320px;" class="flex-grow-1">
          <VTextField
            v-model="searchQuery"
            placeholder="Cari nama peserta..."
            density="compact"
            variant="outlined"
            hide-details
            clearable
            prepend-inner-icon="mdi-magnify"
          />
        </div>
      </div>
    </VCard>

    <!-- Full Leaderboard Table -->
    <VCard flat border class="rounded-xl overflow-hidden bg-surface mb-6 shadow-sm">
      <div class="pa-4 border-b bg-background d-flex justify-space-between align-center">
        <div class="d-flex align-center ga-2">
          <VIcon icon="mdi-format-list-numbered" size="20" color="primary" />
          <span class="text-subtitle-2 font-weight-bold text-high-emphasis">
            Daftar Peringkat Lengkap
          </span>
        </div>
        <span class="text-caption text-medium-emphasis">
          Menampilkan {{ filteredLeaderboard.length }} Peserta Terbaik
        </span>
      </div>

      <VTable class="leaderboard-table">
        <thead>
          <tr class="bg-background">
            <th class="text-center font-weight-bold" style="width: 70px;">Peringkat</th>
            <th class="text-left font-weight-bold">Peserta Didik</th>
            <th class="text-center font-weight-bold" style="width: 120px;">Tingkat</th>
            <th class="text-end font-weight-bold" style="width: 150px;">Total Perolehan XP</th>
            <th class="text-center font-weight-bold" style="width: 130px;">Streak Belajar</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(entry, index) in filteredLeaderboard"
            :key="entry.user_id || index"
            class="transition-swing"
            :class="{
              'current-user-row': entry.user_id === authUser.id,
              'top-rank-row': index < 3,
            }"
          >
            <!-- Rank Column -->
            <td class="text-center py-3">
              <span v-if="index === 0" class="rank-badge-pill rank-gold">🥇 1</span>
              <span v-else-if="index === 1" class="rank-badge-pill rank-silver">🥈 2</span>
              <span v-else-if="index === 2" class="rank-badge-pill rank-bronze">🥉 3</span>
              <span v-else class="font-weight-bold text-medium-emphasis">#{{ index + 1 }}</span>
            </td>

            <!-- User Info Column -->
            <td class="py-3">
              <div class="d-flex align-center ga-3">
                <VAvatar
                  :color="entry.user_id === authUser.id ? 'primary' : 'default'"
                  size="36"
                  variant="tonal"
                  class="font-weight-bold"
                  :image="entry.user?.avatar_url"
                >
                  <span v-if="!entry.user?.avatar_url">{{ getInitials(entry.user?.name) }}</span>
                </VAvatar>

                <div>
                  <div class="d-flex align-center ga-2 flex-wrap">
                    <span class="font-weight-bold text-body-2 text-high-emphasis">
                      {{ entry.user?.name ?? 'Peserta' }}
                    </span>
                    <VChip
                      v-if="entry.user_id === authUser.id"
                      size="x-small"
                      color="primary"
                      variant="flat"
                      label
                      class="font-weight-bold"
                    >
                      Anda
                    </VChip>
                  </div>
                  <div class="text-caption text-medium-emphasis">
                    {{ entry.user?.email ? entry.user.email.replace(/(.{2})(.*)(@.*)/, '$1***$3') : 'Siswa LMS' }}
                  </div>
                </div>
              </div>
            </td>

            <!-- Level Column -->
            <td class="text-center py-3">
              <VChip size="small" color="primary" variant="tonal" label class="font-weight-bold">
                Level {{ entry.level || 1 }}
              </VChip>
            </td>

            <!-- Total XP Column -->
            <td class="text-end py-3">
              <div class="font-weight-bold text-subtitle-2 text-primary">
                {{ formatNumber(entry.total_xp) }} <span class="text-caption text-medium-emphasis font-weight-normal">XP</span>
              </div>
            </td>

            <!-- Streak Column -->
            <td class="text-center py-3">
              <VChip
                size="small"
                :color="entry.current_streak > 0 ? 'warning' : 'default'"
                variant="tonal"
                class="font-weight-medium"
              >
                <VIcon
                  :icon="entry.current_streak > 0 ? 'mdi-fire' : 'mdi-fire-off'"
                  size="16"
                  class="me-1"
                />
                {{ entry.current_streak || 0 }} Hari
              </VChip>
            </td>
          </tr>

          <!-- Empty State Row -->
          <tr v-if="filteredLeaderboard.length === 0">
            <td colspan="5" class="text-center pa-10 text-medium-emphasis">
              <VIcon icon="mdi-trophy-off-outline" size="40" class="mb-2 text-medium-emphasis" />
              <div class="text-subtitle-2 font-weight-medium">Belum Ada Data Peringkat</div>
              <div class="text-caption">
                {{ searchQuery ? 'Tidak ada peserta yang cocok dengan nama tersebut.' : 'Jadilah yang pertama mengumpulkan XP pada periode ini!' }}
              </div>
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Gamification Guide Info Card -->
    <VCard flat border class="rounded-xl pa-6 bg-surface">
      <div class="d-flex align-center ga-3 mb-4">
        <VAvatar color="primary" variant="tonal" size="36" rounded="lg">
          <VIcon icon="mdi-information-outline" size="20" color="primary" />
        </VAvatar>
        <h6 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-0">
          Panduan Cara Mengumpulkan Poin XP
        </h6>
      </div>

      <VRow>
        <VCol cols="12" sm="6" md="3">
          <div class="pa-3 rounded-lg bg-background border d-flex align-center ga-3">
            <VAvatar color="primary" variant="tonal" size="36" rounded="lg">
              <VIcon icon="mdi-play-circle" size="20" />
            </VAvatar>
            <div>
              <div class="text-caption text-medium-emphasis">Menyelesaikan Pelajaran</div>
              <div class="text-subtitle-2 font-weight-bold text-primary">+10 XP / materi</div>
            </div>
          </div>
        </VCol>

        <VCol cols="12" sm="6" md="3">
          <div class="pa-3 rounded-lg bg-background border d-flex align-center ga-3">
            <VAvatar color="info" variant="tonal" size="36" rounded="lg">
              <VIcon icon="mdi-check-circle" size="20" />
            </VAvatar>
            <div>
              <div class="text-caption text-medium-emphasis">Lulus Kuis</div>
              <div class="text-subtitle-2 font-weight-bold text-info">+30 XP / kuis</div>
            </div>
          </div>
        </VCol>

        <VCol cols="12" sm="6" md="3">
          <div class="pa-3 rounded-lg bg-background border d-flex align-center ga-3">
            <VAvatar color="warning" variant="tonal" size="36" rounded="lg">
              <VIcon icon="mdi-star" size="20" />
            </VAvatar>
            <div>
              <div class="text-caption text-medium-emphasis">Nilai Kuis Sempurna</div>
              <div class="text-subtitle-2 font-weight-bold text-warning">+50 XP / 100%</div>
            </div>
          </div>
        </VCol>

        <VCol cols="12" sm="6" md="3">
          <div class="pa-3 rounded-lg bg-background border d-flex align-center ga-3">
            <VAvatar color="success" variant="tonal" size="36" rounded="lg">
              <VIcon icon="mdi-certificate" size="20" />
            </VAvatar>
            <div>
              <div class="text-caption text-medium-emphasis">Menuntaskan Kursus</div>
              <div class="text-subtitle-2 font-weight-bold text-success">+100 XP / kursus</div>
            </div>
          </div>
        </VCol>
      </VRow>
    </VCard>
  </div>
</template>

<style scoped>
.podium-wrapper {
  min-height: 260px;
}

.podium-item {
  width: 140px;
}

@media (min-width: 600px) {
  .podium-item {
    width: 170px;
  }
}

.crown-icon {
  position: absolute;
  top: -24px;
  left: 50%;
  transform: translateX(-50%);
  animation: float 2.5s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateX(-50%) translateY(0); }
  50% { transform: translateX(-50%) translateY(-6px); }
}

.podium-badge {
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  padding: 2px 10px;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.badge-gold {
  background: linear-gradient(135deg, #ffd700, #ffae00);
  color: #1a1a1a;
}
.badge-silver {
  background: linear-gradient(135deg, #e0e0e0, #b0bec5);
  color: #1a1a1a;
}
.badge-bronze {
  background: linear-gradient(135deg, #cd7f32, #b87333);
  color: #ffffff;
}

.border-gold {
  border: 3px solid #ffd700 !important;
}
.border-silver {
  border: 3px solid #cfd8dc !important;
}
.border-bronze {
  border: 3px solid #cd7f32 !important;
}

.pedestal {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-bottom: none;
}

.pedestal-step-one {
  height: 140px;
  background: linear-gradient(180deg, rgba(var(--v-theme-warning), 0.15) 0%, rgba(var(--v-theme-surface), 0.9) 100%);
}

.pedestal-step-two {
  height: 105px;
  background: linear-gradient(180deg, rgba(var(--v-theme-secondary), 0.12) 0%, rgba(var(--v-theme-surface), 0.9) 100%);
}

.pedestal-step-three {
  height: 80px;
  background: linear-gradient(180deg, rgba(205, 127, 50, 0.12) 0%, rgba(var(--v-theme-surface), 0.9) 100%);
}

.rank-badge-pill {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 700;
}

.rank-gold {
  background-color: rgba(255, 215, 0, 0.18);
  color: #b78103;
}
.rank-silver {
  background-color: rgba(176, 190, 197, 0.25);
  color: #546e7a;
}
.rank-bronze {
  background-color: rgba(205, 127, 50, 0.18);
  color: #a0522d;
}

.leaderboard-table th {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}

.current-user-row {
  background-color: rgba(var(--v-theme-primary), 0.08) !important;
  border-left: 4px solid rgb(var(--v-theme-primary)) !important;
}

.max-w-160 {
  max-width: 150px;
}
.max-w-180 {
  max-width: 170px;
}
</style>
