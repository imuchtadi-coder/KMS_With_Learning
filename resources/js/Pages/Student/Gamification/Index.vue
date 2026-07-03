<script setup>
defineProps({
  gamification: { type: Object, required: true },
  badges: { type: Array, default: () => [] },
  recentPoints: { type: Array, default: () => [] },
  totalPoints: { type: Number, default: 0 },
  nextLevelXp: { type: Number, default: 0 },
  progressInLevel: { type: Number, default: 0 },
})

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatAction(action) {
  return action?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) ?? ''
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-6">My Gamification</h4>

    <VRow class="mb-8">
      <VCol cols="12" md="4">
        <VCard flat border class="pa-6">
          <div class="text-body-2 text-medium-emphasis mb-1">Level</div>
          <div class="text-h2 font-weight-bold text-primary">{{ gamification.level }}</div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard flat border class="pa-6">
          <div class="text-body-2 text-medium-emphasis mb-1">Total XP</div>
          <div class="text-h2 font-weight-bold text-purple">
            {{ Number(totalPoints).toLocaleString() }}
          </div>
          <VProgressLinear
            :model-value="Math.min(100, progressInLevel)"
            color="purple"
            height="8"
            rounded
            class="mt-3"
          />
          <div class="text-caption text-medium-emphasis mt-1">
            {{ Number(gamification.total_xp).toLocaleString() }} / {{ Number(nextLevelXp).toLocaleString() }} XP to next level
          </div>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard flat border class="pa-6">
          <div class="text-body-2 text-medium-emphasis mb-1">Current Streak</div>
          <div class="text-h2 font-weight-bold text-orange">
            {{ gamification.current_streak }}
            <span class="text-h6 text-medium-emphasis">days</span>
          </div>
          <div class="text-caption text-medium-emphasis mt-1">
            Longest: {{ gamification.longest_streak }} days
          </div>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12" md="6">
        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-4">Badges</h5>
          <div
            v-if="badges.length > 0"
            class="grid gap-4"
            style="grid-template-columns: repeat(4, 1fr);"
          >
            <div
              v-for="badge in badges"
              :key="badge.id"
              class="text-center pa-3 bg-white rounded-lg"
              :title="badge.description"
            >
              <VAvatar
                color="warning"
                rounded
                size="48"
                class="mb-2"
              >
                <VIcon icon="mdi-star" size="24" color="white" />
              </VAvatar>
              <div class="text-caption font-weight-bold">{{ badge.name }}</div>
              <div class="text-caption text-medium-emphasis">
                {{ formatDate(badge.pivot?.earned_at) }}
              </div>
            </div>
          </div>
          <p v-else class="text-body-2 text-medium-emphasis">
            No badges earned yet. Complete activities to earn badges!
          </p>
        </VCard>
      </VCol>

      <VCol cols="12" md="6">
        <VCard flat border class="pa-6">
          <h5 class="text-h5 font-weight-bold mb-4">Recent Points Activity</h5>
          <VList v-if="recentPoints.length > 0">
            <VListItem
              v-for="pt in recentPoints"
              :key="pt.id"
              class="border-b"
            >
              <div class="d-flex justify-space-between align-center w-100">
                <div>
                  <div class="text-body-2 font-weight-medium">{{ formatAction(pt.action) }}</div>
                  <div v-if="pt.course" class="text-caption text-medium-emphasis">{{ pt.course.title }}</div>
                </div>
                <VChip color="success" variant="tonal" size="small" label>
                  +{{ pt.points }} XP
                </VChip>
              </div>
            </VListItem>
          </VList>
          <p v-else class="text-body-2 text-medium-emphasis">
            No points earned yet. Start learning to earn XP!
          </p>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
