<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  attempt: { type: Object, required: true },
  remaining: { type: Number, default: null },
  preventCopyPaste: { type: Boolean, default: false },
  randomizeOptions: { type: Boolean, default: false },
})

const timeLeft = ref(props.remaining ?? 0)
const answers = ref({})
const tabSwitches = ref(0)
const submitting = ref(false)
const maxTabSwitches = 3
let timerInterval = null

const hasTimer = computed(() => props.remaining > 0)

const formattedTime = computed(() => {
  const mins = Math.floor(timeLeft.value / 60)
  const secs = timeLeft.value % 60
  return `${mins}:${String(secs).padStart(2, '0')}`
})

const questions = computed(() => {
  const qs = props.attempt.quiz.questions || []
  if (props.randomizeOptions) {
    return qs.map(q => {
      if (q.options && Array.isArray(q.options)) {
        const shuffled = [...q.options]
        for (let i = shuffled.length - 1; i > 0; i--) {
          const j = Math.floor(Math.random() * (i + 1));
          [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
        }
        return { ...q, options: shuffled }
      }
      return q
    })
  }
  return qs
})

function getOptionKey(q, optIndex) {
  return `${q.id}_${optIndex}`
}

function isSelected(q, optIndex) {
  return answers.value[q.id] === String(optIndex)
}

function selectOption(q, optIndex) {
  answers.value[q.id] = String(optIndex)
}

// Timer countdown
if (hasTimer.value) {
  timerInterval = setInterval(() => {
    timeLeft.value--
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval)
      submitQuiz(true)
    }
  }, 1000)
}

// Tab switch detection
function onVisibilityChange() {
  if (document.hidden) {
    tabSwitches.value++
    if (tabSwitches.value >= maxTabSwitches) {
      alert('You have switched tabs too many times. Your quiz will be submitted.')
      submitQuiz(true)
    }
  }
}

document.addEventListener('visibilitychange', onVisibilityChange)

// Copy-paste prevention
function preventAction(e) {
  if (props.preventCopyPaste) {
    e.preventDefault()
    alert('This action is not allowed during this exam.')
  }
}

function onKeydown(e) {
  if (props.preventCopyPaste && (e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 'a'].includes(e.key.toLowerCase())) {
    e.preventDefault()
  }
}

onMounted(() => {
  if (props.preventCopyPaste) {
    document.addEventListener('copy', preventAction)
    document.addEventListener('paste', preventAction)
    document.addEventListener('cut', preventAction)
    document.addEventListener('contextmenu', preventAction)
    document.addEventListener('keydown', onKeydown)
  }
})

onBeforeUnmount(() => {
  if (timerInterval) clearInterval(timerInterval)
  document.removeEventListener('visibilitychange', onVisibilityChange)
  if (props.preventCopyPaste) {
    document.removeEventListener('copy', preventAction)
    document.removeEventListener('paste', preventAction)
    document.removeEventListener('cut', preventAction)
    document.removeEventListener('contextmenu', preventAction)
    document.removeEventListener('keydown', onKeydown)
  }
})

function submitQuiz(force = false) {
  if (!force && !confirm('Are you sure you want to submit?')) return
  submitting.value = true
  router.post(route('student.quizzes.submit', props.attempt.id), {
    answers: answers.value,
    tab_switches: tabSwitches.value,
  }, {
    preserveScroll: true,
    onError: () => { submitting.value = false },
  })
}
</script>

<template>
  <div class="quiz-take">
    <!-- Timer floating badge -->
    <VCard
      v-if="hasTimer"
      class="quiz-timer"
      flat
      border
    >
      <div class="text-caption text-medium-emphasis">Time Remaining</div>
      <div
        class="text-h5 font-weight-bold"
        :class="timeLeft < 60 ? 'text-error' : 'text-high-emphasis'"
      >
        {{ formattedTime }}
      </div>
    </VCard>

    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold">{{ attempt.quiz.title }}</h4>
      <p class="text-body-2 text-medium-emphasis mt-1">{{ attempt.quiz.section?.title ?? '' }}</p>
    </div>

    <div class="d-flex flex-column ga-6">
      <VCard
        v-for="(question, index) in questions"
        :key="question.id"
        flat
        border
        class="pa-6"
      >
        <div class="d-flex align-start justify-space-between mb-4">
          <h5 class="text-h5">Question {{ index + 1 }}</h5>
          <VChip
            size="small"
            variant="tonal"
            :color="question.question_type === 'multiple_choice' ? 'primary' : question.question_type === 'true_false' ? 'success' : 'purple'"
            label
          >
            {{ question.pivot?.points || question.points }} pts
          </VChip>
        </div>

        <p class="text-body-1 mb-4">{{ question.question_text }}</p>

        <!-- Multiple Choice -->
        <div
          v-if="question.question_type === 'multiple_choice' && question.options?.length"
          class="d-flex flex-column ga-2"
        >
          <VLabel
            v-for="(option, optIndex) in question.options"
            :key="getOptionKey(question, optIndex)"
            class="option-label pa-3 rounded-lg"
            :class="{ 'option-selected': isSelected(question, optIndex) }"
            @click="selectOption(question, optIndex)"
          >
            <VRadio
              :model-value="isSelected(question, optIndex)"
              color="primary"
              hide-details
              density="default"
            />
            <span class="text-body-2 ms-2">{{ option }}</span>
          </VLabel>
        </div>

        <!-- True/False -->
        <div
          v-else-if="question.question_type === 'true_false'"
          class="d-flex ga-4"
        >
          <VLabel
            class="option-label pa-3 rounded-lg flex-grow-1 text-center"
            :class="{ 'option-selected': answers[question.id] === 'true' }"
            @click="selectOption(question, 0); answers[question.id] = 'true'"
          >
            <VRadio
              :model-value="answers[question.id] === 'true'"
              color="primary"
              hide-details
              density="default"
            />
            <span class="text-body-2 ms-2">True</span>
          </VLabel>
          <VLabel
            class="option-label pa-3 rounded-lg flex-grow-1 text-center"
            :class="{ 'option-selected': answers[question.id] === 'false' }"
            @click="selectOption(question, 1); answers[question.id] = 'false'"
          >
            <VRadio
              :model-value="answers[question.id] === 'false'"
              color="primary"
              hide-details
              density="default"
            />
            <span class="text-body-2 ms-2">False</span>
          </VLabel>
        </div>

        <!-- Essay -->
        <VTextarea
          v-else
          v-model="answers[question.id]"
          variant="outlined"
          placeholder="Write your answer..."
          rows="4"
          hide-details
        />
      </VCard>
    </div>

    <div class="d-flex justify-space-between align-center pt-4 mb-8">
      <span class="text-body-2 text-medium-emphasis">{{ questions.length }} questions</span>
      <VBtn
        color="success"
        variant="elevated"
        size="large"
        :loading="submitting"
        @click="submitQuiz(false)"
      >
        <VIcon start icon="mdi-check" />
        Submit Quiz
      </VBtn>
    </div>
  </div>
</template>

<style scoped>
.quiz-take {
  max-width: 720px;
  margin: 0 auto;
}

.quiz-timer {
  position: fixed;
  top: 72px;
  right: 24px;
  z-index: 100;
  padding: 12px 20px;
  text-align: center;
  min-width: 120px;
}

.option-label {
  display: flex;
  align-items: center;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  cursor: pointer;
  transition: background-color 0.15s, border-color 0.15s;
}

.option-label:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
}

.option-selected {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.08);
}
</style>
