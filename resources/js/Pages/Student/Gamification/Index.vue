<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  gamification: { type: Object, required: true },
  badges: { type: Array, default: () => [] },
  allBadges: { type: Array, default: () => [] },
  recentPoints: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: 0 },
  currentLevelXp: { type: Number, default: 0 },
  nextLevelXp: { type: Number, default: 0 },
  xpRemaining: { type: Number, default: 0 },
  progressInLevel: { type: Number, default: 0 },
  userRank: { type: Number, default: 1 },
  streakMilestones: { type: Array, default: () => [7, 14, 30, 60, 100] },
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user || {})

const selectedBadgeFilter = ref('all') // 'all', 'earned', 'locked'

const displayedBadges = computed(() => {
  const list = props.allBadges?.length > 0 ? props.allBadges : props.badges.map(b => ({
    ...b,
    is_earned: true,
    earned_at: b.pivot?.earned_at,
  }))

  if (selectedBadgeFilter.value === 'earned') {
    return list.filter(b => b.is_earned)
  }
  if (selectedBadgeFilter.value === 'locked') {
    return list.filter(b => !b.is_earned)
  }
  return list
})

const earnedBadgesCount = computed(() => {
  if (props.allBadges?.length > 0) {
    return props.allBadges.filter(b => b.is_earned).length
  }
  return props.badges.length
})

function getLevelTitle(level) {
  if (level <= 1) return 'Pemula Penjelajah'
  if (level === 2) return 'Pembelajar Aktif'
  if (level === 3) return 'Pejuang Ilmu'
  if (level === 4) return 'Spesialis Berbakat'
  if (level === 5) return 'Master Cendekia'
  return 'Legenda Pembelajar'
}

function formatDate(date) {
  if (!date) return ''
  try {
    return new Date(date).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })
  } catch (e) {
    return date
  }
}

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID')
}

function formatAction(action) {
  const map = {
    lesson_completed: 'Pelajaran Selesai',
    quiz_passed: 'Lulus Kuis',
    quiz_perfect: 'Nilai Kuis Sempurna',
    course_completed: 'Menuntaskan Kursus',
    streak_bonus: 'Bonus Streak Rutin',
  }
  return map[action] || action?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Aktivitas Belajar'
}

function getActionIcon(action) {
  if (action?.includes('lesson')) return 'mdi-play-circle-outline'
  if (action?.includes('quiz_perfect')) return 'mdi-star-circle-outline'
  if (action?.includes('quiz')) return 'mdi-check-circle-outline'
  if (action?.includes('course')) return 'mdi-school-outline'
  if (action?.includes('streak')) return 'mdi-fire'
  return 'mdi-lightning-bolt-outline'
}

function getCriteriaLabel(type, value) {
  const map = {
    lesson_complete: `${value} Materi Selesai`,
    course_complete: `${value} Kursus Tuntas`,
    quiz_perfect_score: `${value}x Kuis Nilai 100`,
    points_milestone: `Raih ${formatNumber(value)} XP`,
    consecutive_days: `${value} Hari Belajar Rutin`,
  }
  return map[type] || `Target: ${value}`
}
</script>

<template>
  <div class="gamification-page pb-12">
    <!-- Kahoot-style Hero Profile Banner -->
    <VCard flat border class="mb-6 rounded-2xl overflow-hidden kahoot-hero-card text-white">
      <div class="pa-6 pa-md-8 position-relative">
        <!-- Floating Ambient Shapes for Kahoot vibe -->
        <div class="kahoot-shape shape-triangle" />
        <div class="kahoot-shape shape-circle" />
        <div class="kahoot-shape shape-square" />

        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-6 position-relative z-1">
          <!-- Left: User Avatar & Kahoot Title -->
          <div class="d-flex align-center ga-4">
            <div class="position-relative">
              <VAvatar
                size="80"
                class="border-gold elevation-4"
                :image="authUser.avatar_url"
                color="deep-purple-darken-3"
              >
                <span v-if="!authUser.avatar_url" class="text-h4 font-weight-bold text-white">
                  {{ (authUser.name || 'S').charAt(0).toUpperCase() }}
                </span>
              </VAvatar>
              <div class="level-crown-badge">
                Lv. {{ gamification.level }}
              </div>
            </div>

            <div>
              <div class="d-flex align-center ga-2 flex-wrap mb-1">
                <h3 class="text-h4 font-weight-bold text-white mb-0">
                  {{ authUser.name }}
                </h3>
                <VChip
                  size="small"
                  color="warning"
                  variant="flat"
                  label
                  class="font-weight-bold text-dark"
                >
                  Peringkat #{{ userRank }}
                </VChip>
              </div>

              <div class="text-subtitle-1 text-white opacity-90 font-weight-medium">
                {{ getLevelTitle(gamification.level) }}
              </div>

              <div class="d-flex align-center ga-3 mt-1 text-caption text-white opacity-80">
                <span>Platform Pembelajaran Digital</span>
                <span>•</span>
                <span>{{ earnedBadgesCount }} Lencana Diraih</span>
              </div>
            </div>
          </div>

          <!-- Right: Total XP & Leaderboard Button -->
          <div class="d-flex flex-column align-start align-md-end ga-2">
            <div class="text-caption text-white opacity-80 text-uppercase font-weight-bold tracking-wider">
              Total Skor Akumulasi
            </div>
            <div class="d-flex align-baseline ga-2">
              <span class="text-h2 font-weight-black text-warning xp-counter-glow">
                {{ formatNumber(totalPoints) }}
              </span>
              <span class="text-h6 font-weight-bold text-white opacity-90">XP</span>
            </div>

            <VBtn
              color="white"
              variant="elevated"
              size="default"
              rounded="pill"
              class="text-primary font-weight-bold shadow-md mt-2"
              :href="route('student.leaderboard.index')"
            >
              <VIcon start icon="mdi-trophy-variant" size="18" color="warning" />
              Papan Peringkat Lengkap
            </VBtn>
          </div>
        </div>

        <!-- Level Progress Bar Roadmap -->
        <div class="mt-8 pt-6 border-t border-white-subtle position-relative z-1">
          <div class="d-flex justify-space-between align-center text-caption font-weight-bold text-white mb-2">
            <div class="d-flex align-center ga-2">
              <span class="level-pill-badge">Level {{ gamification.level }}</span>
              <span class="opacity-80">Perjalanan Tingkat</span>
            </div>
            <div>
              <span class="text-warning font-weight-bold">{{ xpRemaining }} XP</span>
              <span class="opacity-80"> menuju Level {{ gamification.level + 1 }}</span>
            </div>
          </div>

          <div class="kahoot-progress-track rounded-pill pa-1">
            <div
              class="kahoot-progress-fill rounded-pill"
              :style="{ width: `${progressInLevel}%` }"
            />
          </div>

          <div class="d-flex justify-space-between align-center text-caption text-white opacity-75 mt-2">
            <span>{{ formatNumber(currentLevelXp) }} XP</span>
            <span class="font-weight-bold">{{ progressInLevel }}% Tercapai</span>
            <span>{{ formatNumber(nextLevelXp) }} XP</span>
          </div>
        </div>
      </div>
    </VCard>

    <!-- 2 Main Cards: Daily Streak Flame & Kahoot Quest XP Guide -->
    <VRow class="mb-6">
      <!-- 1. Daily Streak Flame Quest -->
      <VCol cols="12" md="5">
        <VCard flat border class="h-100 rounded-2xl bg-surface pa-6 d-flex flex-column justify-space-between shadow-sm">
          <div>
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="d-flex align-center ga-3">
                <div class="flame-avatar-wrapper pa-2 rounded-xl bg-warning-subtle">
                  <VIcon icon="mdi-fire" size="32" color="warning" class="flame-animated" />
                </div>
                <div>
                  <h5 class="text-h6 font-weight-bold text-high-emphasis mb-0">
                    Streak Belajar Harian
                  </h5>
                  <span class="text-caption text-medium-emphasis">Konsistensi membuka bonus XP</span>
                </div>
              </div>

              <VChip size="small" color="warning" variant="tonal" label class="font-weight-bold">
                Aktif
              </VChip>
            </div>

            <!-- Big Flame Number -->
            <div class="d-flex align-baseline ga-2 my-4">
              <span class="text-h2 font-weight-black text-warning">
                {{ gamification.current_streak }}
              </span>
              <span class="text-subtitle-1 font-weight-bold text-medium-emphasis">
                Hari Berturut-turut
              </span>
            </div>

            <p class="text-body-2 text-medium-emphasis mb-4">
              Pertahankan api belajar Anda menyala dengan menyelesaikan minimal 1 aktivitas materi atau kuis setiap hari.
            </p>
          </div>

          <!-- Streak Milestone Roadmap -->
          <div>
            <div class="text-caption font-weight-bold text-uppercase text-medium-emphasis mb-3">
              Target Pencapaian Streak:
            </div>

            <div class="d-flex align-center justify-space-between ga-2">
              <div
                v-for="target in streakMilestones"
                :key="target"
                class="streak-milestone-step text-center flex-grow-1 pa-2 rounded-lg border transition-swing"
                :class="{
                  'milestone-reached': gamification.current_streak >= target,
                  'milestone-pending': gamification.current_streak < target,
                }"
              >
                <VIcon
                  :icon="gamification.current_streak >= target ? 'mdi-fire' : 'mdi-lock-outline'"
                  size="20"
                  :color="gamification.current_streak >= target ? 'warning' : 'medium-emphasis'"
                  class="mb-1"
                />
                <div class="text-caption font-weight-bold">
                  {{ target }} H
                </div>
              </div>
            </div>

            <div class="text-caption text-medium-emphasis text-end mt-2">
              Rekor Terpanjang: <strong>{{ gamification.longest_streak }} Hari</strong>
            </div>
          </div>
        </VCard>
      </VCol>

      <!-- 2. Kahoot Quest: Cara Mendapatkan Poin XP -->
      <VCol cols="12" md="7">
        <VCard flat border class="h-100 rounded-2xl bg-surface pa-6 shadow-sm">
          <div class="d-flex align-center justify-space-between mb-4">
            <div class="d-flex align-center ga-3">
              <VAvatar color="primary" variant="tonal" size="42" rounded="xl">
                <VIcon icon="mdi-gamepad-variant-outline" size="24" color="primary" />
              </VAvatar>
              <div>
                <h5 class="text-h6 font-weight-bold text-high-emphasis mb-0">
                  Misi Perolehan Poin XP
                </h5>
                <span class="text-caption text-medium-emphasis">Selesaikan aktivitas belajar untuk melesat di papan peringkat</span>
              </div>
            </div>
          </div>

          <!-- 4 Kahoot-style Colorful Quest Tiles -->
          <VRow dense>
            <!-- Red Tile: Pelajaran -->
            <VCol cols="12" sm="6" class="pa-2">
              <div class="kahoot-tile tile-red pa-4 rounded-xl d-flex align-center justify-space-between text-white">
                <div class="d-flex align-center ga-3">
                  <VAvatar color="white" variant="flat" size="36" rounded="lg">
                    <VIcon icon="mdi-play-circle" color="red-darken-1" size="22" />
                  </VAvatar>
                  <div>
                    <div class="text-subtitle-2 font-weight-bold">Tuntaskan Pelajaran</div>
                    <div class="text-caption opacity-90">Satu materi video/teks</div>
                  </div>
                </div>
                <div class="text-subtitle-1 font-weight-black">+10 XP</div>
              </div>
            </VCol>

            <!-- Blue Tile: Lulus Kuis -->
            <VCol cols="12" sm="6" class="pa-2">
              <div class="kahoot-tile tile-blue pa-4 rounded-xl d-flex align-center justify-space-between text-white">
                <div class="d-flex align-center ga-3">
                  <VAvatar color="white" variant="flat" size="36" rounded="lg">
                    <VIcon icon="mdi-checkbox-marked-circle" color="blue-darken-2" size="22" />
                  </VAvatar>
                  <div>
                    <div class="text-subtitle-2 font-weight-bold">Lulus Ujian / Kuis</div>
                    <div class="text-caption opacity-90">Mencapai batas kelulusan</div>
                  </div>
                </div>
                <div class="text-subtitle-1 font-weight-black">+30 XP</div>
              </div>
            </VCol>

            <!-- Yellow Tile: Nilai 100 -->
            <VCol cols="12" sm="6" class="pa-2">
              <div class="kahoot-tile tile-yellow pa-4 rounded-xl d-flex align-center justify-space-between text-dark">
                <div class="d-flex align-center ga-3">
                  <VAvatar color="white" variant="flat" size="36" rounded="lg">
                    <VIcon icon="mdi-star" color="amber-darken-3" size="22" />
                  </VAvatar>
                  <div>
                    <div class="text-subtitle-2 font-weight-bold">Skor Sempurna 100%</div>
                    <div class="text-caption opacity-80">Semua jawaban kuis benar</div>
                  </div>
                </div>
                <div class="text-subtitle-1 font-weight-black">+50 XP</div>
              </div>
            </VCol>

            <!-- Green Tile: Tamat Kursus -->
            <VCol cols="12" sm="6" class="pa-2">
              <div class="kahoot-tile tile-green pa-4 rounded-xl d-flex align-center justify-space-between text-white">
                <div class="d-flex align-center ga-3">
                  <VAvatar color="white" variant="flat" size="36" rounded="lg">
                    <VIcon icon="mdi-school" color="teal-darken-2" size="22" />
                  </VAvatar>
                  <div>
                    <div class="text-subtitle-2 font-weight-bold">Menuntaskan Kursus</div>
                    <div class="text-caption opacity-90">100% kurikulum selesai</div>
                  </div>
                </div>
                <div class="text-subtitle-1 font-weight-black">+100 XP</div>
              </div>
            </VCol>
          </VRow>
        </VCard>
      </VCol>
    </VRow>

    <!-- Badges Showcase & Recent XP Feed Row -->
    <VRow>
      <!-- Left: Badges & Achievements (Kahoot Trophy Showcase) -->
      <VCol cols="12" lg="7">
        <VCard flat border class="rounded-2xl bg-surface pa-6 shadow-sm mb-6">
          <div class="d-flex flex-column flex-sm-row justify-space-between align-start align-sm-center ga-3 mb-6">
            <div>
              <h5 class="text-h6 font-weight-bold text-high-emphasis mb-1">
                Koleksi Lencana & Pencapaian
              </h5>
              <span class="text-caption text-medium-emphasis">
                {{ earnedBadgesCount }} dari {{ allBadges.length || badges.length }} Lencana Terbuka
              </span>
            </div>

            <!-- Filter Buttons -->
            <div class="d-flex align-center ga-1 bg-background pa-1 rounded-pill border">
              <VBtn
                size="small"
                :variant="selectedBadgeFilter === 'all' ? 'flat' : 'text'"
                :color="selectedBadgeFilter === 'all' ? 'primary' : 'default'"
                rounded="pill"
                class="text-capitalize px-3"
                @click="selectedBadgeFilter = 'all'"
              >
                Semua
              </VBtn>
              <VBtn
                size="small"
                :variant="selectedBadgeFilter === 'earned' ? 'flat' : 'text'"
                :color="selectedBadgeFilter === 'earned' ? 'primary' : 'default'"
                rounded="pill"
                class="text-capitalize px-3"
                @click="selectedBadgeFilter = 'earned'"
              >
                Diraih
              </VBtn>
              <VBtn
                size="small"
                :variant="selectedBadgeFilter === 'locked' ? 'flat' : 'text'"
                :color="selectedBadgeFilter === 'locked' ? 'primary' : 'default'"
                rounded="pill"
                class="text-capitalize px-3"
                @click="selectedBadgeFilter = 'locked'"
              >
                Terkunci
              </VBtn>
            </div>
          </div>

          <!-- Badges Grid -->
          <VRow v-if="displayedBadges.length > 0">
            <VCol
              v-for="badge in displayedBadges"
              :key="badge.id"
              cols="12"
              sm="6"
              md="4"
            >
              <div
                class="badge-card pa-4 rounded-xl border text-center transition-swing h-100 d-flex flex-column align-center justify-between"
                :class="{
                  'badge-card-earned': badge.is_earned,
                  'badge-card-locked': !badge.is_earned,
                }"
              >
                <div>
                  <div class="position-relative mb-3">
                    <VAvatar
                      size="64"
                      :color="badge.is_earned ? 'warning' : 'default'"
                      :variant="badge.is_earned ? 'flat' : 'tonal'"
                      class="badge-avatar elevation-2"
                      :class="{ 'glowing-badge': badge.is_earned }"
                    >
                      <VIcon
                        :icon="badge.is_earned ? 'mdi-star-four-points' : 'mdi-lock-outline'"
                        :size="badge.is_earned ? 32 : 26"
                        :color="badge.is_earned ? 'white' : 'medium-emphasis'"
                      />
                    </VAvatar>

                    <div
                      v-if="badge.is_earned"
                      class="badge-check-icon"
                    >
                      <VIcon icon="mdi-check-circle" color="success" size="18" />
                    </div>
                  </div>

                  <h6 class="text-subtitle-2 font-weight-bold text-high-emphasis mb-1">
                    {{ badge.name }}
                  </h6>

                  <p class="text-caption text-medium-emphasis mb-3 line-clamp-2">
                    {{ badge.description || getCriteriaLabel(badge.criteria_type, badge.criteria_value) }}
                  </p>
                </div>

                <div class="mt-auto w-100">
                  <VChip
                    v-if="badge.is_earned"
                    size="x-small"
                    color="success"
                    variant="tonal"
                    label
                    class="font-weight-medium w-100 justify-center"
                  >
                    Diraih: {{ formatDate(badge.earned_at) }}
                  </VChip>
                  <VChip
                    v-else
                    size="x-small"
                    color="default"
                    variant="outlined"
                    label
                    class="font-weight-medium w-100 justify-center"
                  >
                    {{ getCriteriaLabel(badge.criteria_type, badge.criteria_value) }}
                  </VChip>
                </div>
              </div>
            </VCol>
          </VRow>

          <!-- Empty Badges State -->
          <div
            v-else
            class="text-center pa-10 text-medium-emphasis"
          >
            <VIcon icon="mdi-medal-outline" size="48" class="mb-2 text-medium-emphasis" />
            <div class="text-subtitle-2 font-weight-bold">Tidak Ada Lencana yang Sesuai</div>
            <div class="text-caption">Coba ubah filter untuk melihat lencana lainnya.</div>
          </div>
        </VCard>
      </VCol>

      <!-- Right: Recent Points Activity Stream -->
      <VCol cols="12" lg="5">
        <VCard flat border class="rounded-2xl bg-surface pa-6 shadow-sm mb-6">
          <div class="d-flex align-center justify-space-between mb-4">
            <h5 class="text-h6 font-weight-bold text-high-emphasis mb-0">
              Riwayat Perolehan XP
            </h5>
            <span class="text-caption text-medium-emphasis">20 Aktivitas Terakhir</span>
          </div>

          <div
            v-if="recentPoints.length > 0"
            class="xp-activity-list d-flex flex-column ga-3"
            style="max-height: 480px; overflow-y: auto;"
          >
            <div
              v-for="pt in recentPoints"
              :key="pt.id"
              class="d-flex align-center justify-space-between pa-3 rounded-xl bg-background border transition-swing"
            >
              <div class="d-flex align-center ga-3">
                <VAvatar
                  size="36"
                  color="primary"
                  variant="tonal"
                  rounded="lg"
                >
                  <VIcon :icon="getActionIcon(pt.action)" size="18" color="primary" />
                </VAvatar>

                <div>
                  <div class="text-body-2 font-weight-bold text-high-emphasis">
                    {{ formatAction(pt.action) }}
                  </div>
                  <div class="text-caption text-medium-emphasis text-truncate" style="max-width: 200px;">
                    {{ pt.course?.title || formatDate(pt.created_at) }}
                  </div>
                </div>
              </div>

              <VChip
                color="success"
                variant="flat"
                size="small"
                label
                class="font-weight-black shadow-xs"
              >
                +{{ pt.points }} XP
              </VChip>
            </div>
          </div>

          <!-- Empty Activity State -->
          <div
            v-else
            class="text-center pa-10 text-medium-emphasis"
          >
            <VIcon icon="mdi-lightning-bolt-outline" size="48" class="mb-2 text-medium-emphasis" />
            <div class="text-subtitle-2 font-weight-bold">Belum Ada Riwayat XP</div>
            <div class="text-caption">
              Selesaikan modul pelajaran pertama Anda untuk mulai mengumpulkan poin XP!
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
/* Kahoot Hero Banner */
.kahoot-hero-card {
  background: linear-gradient(135deg, #46178f 0%, #702cb5 50%, #e21b3c 100%);
  box-shadow: 0 10px 30px -5px rgba(70, 23, 143, 0.4) !important;
}

.kahoot-shape {
  position: absolute;
  pointer-events: none;
  opacity: 0.12;
}

.shape-triangle {
  top: 10px;
  right: 25%;
  width: 80px;
  height: 80px;
  clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
  background-color: #ffd700;
}

.shape-circle {
  bottom: -20px;
  right: 10%;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background-color: #1368ce;
}

.shape-square {
  top: -10px;
  right: 5%;
  width: 60px;
  height: 60px;
  transform: rotate(25deg);
  background-color: #26890c;
}

.border-gold {
  border: 3px solid #ffd700 !important;
}

.level-crown-badge {
  position: absolute;
  bottom: -4px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #ffd700;
  color: #1a1a1a;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 1px 8px;
  border-radius: 9999px;
  white-space: nowrap;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.xp-counter-glow {
  text-shadow: 0 0 16px rgba(255, 215, 0, 0.6);
}

.level-pill-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 2px 10px;
  border-radius: 9999px;
}

.border-white-subtle {
  border-color: rgba(255, 255, 255, 0.18) !important;
}

.kahoot-progress-track {
  background-color: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
  height: 14px;
}

.kahoot-progress-fill {
  background: linear-gradient(90deg, #ffd700 0%, #ff8c00 100%);
  height: 100%;
  box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
  transition: width 0.5s ease-in-out;
}

/* Flame animation */
.flame-animated {
  animation: pulse-flame 1.5s ease-in-out infinite alternate;
}

@keyframes pulse-flame {
  0% { transform: scale(1); filter: drop-shadow(0 0 2px #ff8c00); }
  100% { transform: scale(1.15); filter: drop-shadow(0 0 8px #ff4500); }
}

/* Milestone Steps */
.milestone-reached {
  background-color: rgba(var(--v-theme-warning), 0.12);
  border-color: rgba(var(--v-theme-warning), 0.4) !important;
  color: rgb(var(--v-theme-warning));
}

.milestone-pending {
  background-color: rgba(var(--v-theme-on-surface), 0.03);
  opacity: 0.65;
}

/* 4 Kahoot Quest Tiles */
.kahoot-tile {
  box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.1);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.kahoot-tile:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px -3px rgba(0, 0, 0, 0.15);
}

.tile-red {
  background: linear-gradient(135deg, #e21b3c 0%, #b80d28 100%);
}

.tile-blue {
  background: linear-gradient(135deg, #1368ce 0%, #0a4fa8 100%);
}

.tile-yellow {
  background: linear-gradient(135deg, #ffa602 0%, #e69100 100%);
}

.tile-green {
  background: linear-gradient(135deg, #26890c 0%, #1a6606 100%);
}

/* Badges Showcase */
.badge-card {
  background-color: rgb(var(--v-theme-background));
}

.badge-card-earned {
  border-color: rgba(var(--v-theme-warning), 0.4) !important;
  background: linear-gradient(180deg, rgba(var(--v-theme-warning), 0.05) 0%, rgb(var(--v-theme-surface)) 100%);
}

.badge-card-locked {
  opacity: 0.75;
}

.glowing-badge {
  background: linear-gradient(135deg, #ffd700 0%, #ff8c00 100%) !important;
  box-shadow: 0 4px 16px rgba(255, 165, 0, 0.45);
}

.badge-check-icon {
  position: absolute;
  bottom: 0;
  right: 0;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
