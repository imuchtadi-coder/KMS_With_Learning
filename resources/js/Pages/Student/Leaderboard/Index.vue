<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  leaderboard: { type: Array, default: () => [] },
  courseId: { type: [String, Number], default: null },
  period: { type: String, default: 'all' },
})

const selectedPeriod = ref(props.period)

function applyFilter() {
  router.get(route('student.leaderboard.index'), {
    period: selectedPeriod.value,
    course_id: props.courseId || undefined,
  }, { preserveState: true, replace: true })
}

function getInitials(name) {
  return (name || '?')[0].toUpperCase()
}

function rankDisplay(index) {
  if (index === 0) return '🥇'
  if (index === 1) return '🥈'
  if (index === 2) return '🥉'
  return String(index + 1)
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-6">Leaderboard</h4>

    <VCard flat border class="pa-4 mb-6">
      <div class="d-flex align-end ga-4">
        <div style="min-width: 200px;">
          <VLabel class="text-caption font-weight-medium mb-1">Period</VLabel>
          <VSelect
            v-model="selectedPeriod"
            :items="[
              { title: 'All Time', value: 'all' },
              { title: 'This Week', value: 'weekly' },
              { title: 'This Month', value: 'monthly' },
            ]"
            density="default"
            variant="outlined"
            hide-details
          />
        </div>
        <VBtn color="primary" variant="elevated" size="small" @click="applyFilter">
          Filter
        </VBtn>
      </div>
    </VCard>

    <VCard flat border>
      <VTable class="leaderboard-table">
        <thead>
          <tr>
            <th class="text-left" style="width: 60px;">#</th>
            <th class="text-left">Student</th>
            <th class="text-left">Level</th>
            <th class="text-left">Total XP</th>
            <th class="text-left">Streak</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(entry, index) in leaderboard"
            :key="entry.user_id || index"
            :class="{ 'bg-primary-light': entry.user_id === $page.props.auth.user.id }"
          >
            <td>
              <span v-if="index < 3" class="text-h6">{{ rankDisplay(index) }}</span>
              <span v-else class="font-weight-bold text-medium-emphasis">{{ index + 1 }}</span>
            </td>
            <td>
              <div class="d-flex align-center ga-3">
                <VAvatar
                  :color="entry.user_id === $page.props.auth.user.id ? 'primary' : 'secondary'"
                  size="32"
                  variant="tonal"
                >
                  <span class="text-caption font-weight-bold">{{ getInitials(entry.user?.name) }}</span>
                </VAvatar>
                <span class="font-weight-medium">{{ entry.user?.name ?? 'Unknown' }}</span>
              </div>
            </td>
            <td>
              <VChip size="x-small" color="primary" variant="tonal" label>
                Lv.{{ entry.level }}
              </VChip>
            </td>
            <td class="font-weight-bold">{{ Number(entry.total_xp).toLocaleString() }}</td>
            <td class="text-medium-emphasis">{{ entry.current_streak }} days</td>
          </tr>
          <tr v-if="leaderboard.length === 0">
            <td colspan="5" class="text-center pa-8 text-medium-emphasis">No data yet.</td>
          </tr>
        </tbody>
      </VTable>
    </VCard>
  </div>
</template>

<style scoped>
.leaderboard-table th {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}
</style>
