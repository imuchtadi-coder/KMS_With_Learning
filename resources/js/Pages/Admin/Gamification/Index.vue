<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  xpConfigs: { type: Object, default: () => ({}) },
  levelConfigs: { type: Array, default: () => [] },
  streakMilestones: { type: Array, default: () => [] },
})

const actionLabels = {
  lesson_completed: 'Lesson Completed',
  quiz_passed: 'Quiz Passed',
  quiz_perfect: 'Quiz Perfect Score',
  course_completed: 'Course Completed',
  streak_bonus: 'Streak Bonus',
}

const xpForm = useForm({
  configs: Object.entries(props.xpConfigs).map(([action, cfg]) => ({
    action,
    points: cfg.points,
  })),
})

function submitXp() {
  xpForm.post(route('admin.gamification.update-xp'), { preserveScroll: true })
}

function resetXp(action) {
  if (!confirm(`Reset "${actionLabels[action]}" to default value?`)) return
  router.post(route('admin.gamification.reset-xp'), { action }, { preserveScroll: true })
}

const levelForm = useForm({
  levels: props.levelConfigs.length
    ? props.levelConfigs.map(l => ({ level: l.level, xp_required: l.xp_required, title: l.title || '' }))
    : Array.from({ length: 10 }, (_, i) => ({ level: i + 1, xp_required: i === 0 ? 0 : (i ** 2) * 100, title: '' })),
})

function addLevel() {
  const next = levelForm.levels.length + 1
  levelForm.levels.push({ level: next, xp_required: (next - 1) ** 2 * 100, title: '' })
}

function removeLevel(index) {
  if (levelForm.levels.length <= 1) return
  levelForm.levels.splice(index, 1)
}

function submitLevels() {
  levelForm.levels.forEach((l, i) => { l.level = i + 1 })
  levelForm.post(route('admin.gamification.update-levels'), { preserveScroll: true })
}

const streakForm = useForm({
  streak_milestones: [...props.streakMilestones],
})

function addMilestone() {
  streakForm.streak_milestones.push(7)
}

function removeMilestone(index) {
  if (streakForm.streak_milestones.length <= 1) return
  streakForm.streak_milestones.splice(index, 1)
}

function submitStreak() {
  streakForm.post(route('admin.gamification.update-settings'), { preserveScroll: true })
}

function resetStreak() {
  if (!confirm('Reset streak milestones to default?')) return
  router.post(route('admin.gamification.reset-settings'), { key: 'streak_milestones' }, { preserveScroll: true })
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Gamification Settings</h4>
        <p class="text-body-2 text-medium-emphasis">Configure XP points, level thresholds, and streak milestones</p>
      </div>
    </div>

    <VRow>
      <!-- XP Configuration -->
      <VCol cols="12" md="6">
        <VCard flat border title="XP Points Configuration">
          <VCardText>
            <p class="text-caption text-medium-emphasis mb-4">Set how many XP points are awarded for each action.</p>
          </VCardText>
          <VTable density="compact">
            <thead>
              <tr>
                <th>Action</th>
                <th style="width: 100px">Points</th>
                <th style="width: 60px">Default</th>
                <th style="width: 48px"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(cfg, i) in xpForm.configs" :key="cfg.action">
                <td>
                  <span class="text-body-2 font-weight-medium">{{ actionLabels[cfg.action] }}</span>
                </td>
                <td>
                  <VTextField
                    v-model="cfg.points"
                    type="number"
                    density="compact"
                    variant="outlined"
                    min="0"
                    max="9999"
                    hide-details
                    class="xp-input"
                  />
                </td>
                <td>
                  <VChip size="x-small" variant="tonal" label class="mt-1">
                    {{ props.xpConfigs[cfg.action]?.default }}
                  </VChip>
                </td>
                <td>
                  <VBtn
                    icon
                    size="x-small"
                    color="warning"
                    variant="tonal"
                    title="Reset to default"
                    @click="resetXp(cfg.action)"
                  >
                    <VIcon icon="mdi-restore" size="16" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
          <VCardText class="d-flex justify-end pt-2">
            <VBtn
              color="primary"
              variant="elevated"
              size="small"
              :loading="xpForm.processing"
              @click="submitXp"
            >
              Save XP Config
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Level Configuration -->
      <VCol cols="12" md="6">
        <VCard flat border title="Level Thresholds">
          <VCardText>
            <p class="text-caption text-medium-emphasis mb-4">Define XP required for each level. Leave empty to use formula-based calculation.</p>
          </VCardText>
          <div style="max-height: 400px; overflow-y: auto">
            <VTable density="compact">
              <thead>
                <tr>
                  <th style="width: 60px">Level</th>
                  <th>XP Required</th>
                  <th>Title</th>
                  <th style="width: 48px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(level, i) in levelForm.levels" :key="i">
                  <td class="text-center font-weight-medium">{{ i + 1 }}</td>
                  <td>
                    <VTextField
                      v-model="level.xp_required"
                      type="number"
                      density="compact"
                      variant="outlined"
                      min="0"
                      hide-details
                    />
                  </td>
                  <td>
                    <VTextField
                      v-model="level.title"
                      density="compact"
                      variant="outlined"
                      placeholder="e.g. Bronze"
                      hide-details
                    />
                  </td>
                  <td>
                    <VBtn
                      icon
                      size="x-small"
                      color="error"
                      variant="tonal"
                      :disabled="levelForm.levels.length <= 1"
                      @click="removeLevel(i)"
                    >
                      <VIcon icon="mdi-close" size="16" />
                    </VBtn>
                  </td>
                </tr>
              </tbody>
            </VTable>
          </div>
          <VCardText class="d-flex justify-space-between pt-2">
            <VBtn color="secondary" variant="tonal" size="small" @click="addLevel">
              <VIcon icon="mdi-plus" size="16" class="mr-1" /> Add Level
            </VBtn>
            <VBtn
              color="primary"
              variant="elevated"
              size="small"
              :loading="levelForm.processing"
              @click="submitLevels"
            >
              Save Levels
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Streak Milestones -->
      <VCol cols="12" md="6">
        <VCard flat border title="Streak Milestones">
          <VCardText>
            <p class="text-caption text-medium-emphasis mb-4">Consecutive day streaks that trigger a bonus XP reward.</p>
          </VCardText>
          <VTable density="compact">
            <thead>
              <tr>
                <th style="width: 60px">#</th>
                <th>Day Milestone</th>
                <th style="width: 48px"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(milestone, i) in streakForm.streak_milestones" :key="i">
                <td class="text-center font-weight-medium">{{ i + 1 }}</td>
                <td>
                  <VTextField
                    v-model.number="streakForm.streak_milestones[i]"
                    type="number"
                    density="compact"
                    variant="outlined"
                    min="1"
                    hide-details
                  />
                </td>
                <td>
                  <VBtn
                    icon
                    size="x-small"
                    color="error"
                    variant="tonal"
                    :disabled="streakForm.streak_milestones.length <= 1"
                    @click="removeMilestone(i)"
                  >
                    <VIcon icon="mdi-close" size="16" />
                  </VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
          <VCardText class="d-flex justify-space-between pt-2">
            <VBtn color="secondary" variant="tonal" size="small" @click="addMilestone">
              <VIcon icon="mdi-plus" size="16" class="mr-1" /> Add Milestone
            </VBtn>
            <div class="d-flex ga-2">
              <VBtn color="warning" variant="tonal" size="small" @click="resetStreak">
                <VIcon icon="mdi-restore" size="16" class="mr-1" /> Reset
              </VBtn>
              <VBtn
                color="primary"
                variant="elevated"
                size="small"
                :loading="streakForm.processing"
                @click="submitStreak"
              >
                Save Milestones
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.xp-input {
  max-width: 80px;
}
</style>
