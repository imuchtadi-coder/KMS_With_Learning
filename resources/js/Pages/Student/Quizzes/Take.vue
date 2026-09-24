<script setup>
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
} from 'vue'

import { router } from '@inertiajs/vue3'

const props = defineProps({
  attempt: {
    type: Object,
    required: true,
  },

  remaining: {
    type: Number,
    default: null,
  },

  preventCopyPaste: {
    type: Boolean,
    default: false,
  },

  randomizeOptions: {
    type: Boolean,
    default: false,
  },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const timeLeft = ref(
  props.remaining ?? 0
)

const answers = ref({})

const tabSwitches = ref(0)

const submitting = ref(false)

const maxTabSwitches = 3

let timerInterval = null

/*
|--------------------------------------------------------------------------
| Timer
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Backend sudah menentukan nilai "remaining".
|
| Quiz:
|   remaining = time_limit - elapsed
|
| Exam:
|   remaining = end_date - now
|
| Karena itu Take.vue tidak perlu membedakan
| Quiz dan Exam untuk perhitungan timer.
|
*/

/*
|--------------------------------------------------------------------------
| Has Timer
|--------------------------------------------------------------------------
*/

const hasTimer = computed(() => {
  return timeLeft.value > 0
})

/*
|--------------------------------------------------------------------------
| Formatted Time
|--------------------------------------------------------------------------
*/

const formattedTime = computed(() => {
  const mins = Math.floor(
    timeLeft.value / 60
  )

  const secs =
    timeLeft.value % 60

  return `${mins}:${String(secs).padStart(2, '0')}`
})

/*
|--------------------------------------------------------------------------
| Randomize Options
|--------------------------------------------------------------------------
|
| IMPORTANT:
| - Urutan soal TIDAK diacak di frontend.
| - Urutan soal tetap mengikuti backend / quiz attempt.
| - Hanya options yang bisa diacak.
| - Hasil shuffle disimpan agar tidak berubah
|   ketika component melakukan re-render.
|
*/

const randomizedOptions =
  ref(new Map())

function shuffleArray(array) {
  const shuffled = [...array]

  for (
    let i = shuffled.length - 1;
    i > 0;
    i--
  ) {
    const j = Math.floor(
      Math.random() * (i + 1)
    )

    ;[
      shuffled[i],
      shuffled[j],
    ] = [
      shuffled[j],
      shuffled[i],
    ]
  }

  return shuffled
}

function initializeRandomizedOptions() {
  const qs =
    props.attempt.quiz.questions || []

  const map = new Map()

  if (!props.randomizeOptions) {
    randomizedOptions.value = map
    return
  }

  qs.forEach(question => {
    if (
      Array.isArray(
        question.options
      )
    ) {
      map.set(
        question.id,
        shuffleArray(
          question.options
        )
      )
    }
  })

  randomizedOptions.value = map
}

const questions = computed(() => {
  const qs =
    props.attempt.quiz.questions || []

  if (!props.randomizeOptions) {
    return qs
  }

  return qs.map(question => {
    if (
      !Array.isArray(
        question.options
      )
    ) {
      return question
    }

    return {
      ...question,

      options:
        randomizedOptions.value.get(
          question.id
        ) ?? question.options,
    }
  })
})

/*
|--------------------------------------------------------------------------
| Multiple Choice
|--------------------------------------------------------------------------
*/

function getOptionKey(
  question,
  optionIndex
) {
  return `${question.id}_${optionIndex}`
}

function isSelected(
  question,
  option
) {
  return (
    answers.value[question.id] ===
    String(option)
  )
}

function selectOption(
  question,
  option
) {
  answers.value[question.id] =
    String(option)
}

/*
|--------------------------------------------------------------------------
| True / False
|--------------------------------------------------------------------------
*/

function isTrueSelected(question) {
  return (
    answers.value[question.id] ===
    'true'
  )
}

function isFalseSelected(question) {
  return (
    answers.value[question.id] ===
    'false'
  )
}

function selectTrueFalse(
  question,
  value
) {
  answers.value[question.id] =
    value
}

/*
|--------------------------------------------------------------------------
| Timer
|--------------------------------------------------------------------------
*/

function startTimer() {
  if (!hasTimer.value) {
    return
  }

  timerInterval = setInterval(() => {
    if (submitting.value) {
      return
    }

    timeLeft.value--

    if (timeLeft.value <= 0) {
      timeLeft.value = 0

      if (timerInterval) {
        clearInterval(timerInterval)
        timerInterval = null
      }

      submitQuiz(true)
    }
  }, 1000)
}

/*
|--------------------------------------------------------------------------
| Tab Switch Detection
|--------------------------------------------------------------------------
*/

function onVisibilityChange() {
  if (
    document.hidden &&
    !submitting.value
  ) {
    tabSwitches.value++

    if (
      tabSwitches.value >=
      maxTabSwitches
    ) {
      alert(
        'You have switched tabs too many times. Your quiz will be submitted.'
      )

      submitQuiz(true)
    }
  }
}

/*
|--------------------------------------------------------------------------
| Copy / Paste Prevention
|--------------------------------------------------------------------------
*/

function preventAction(event) {
  if (props.preventCopyPaste) {
    event.preventDefault()

    alert(
      'This action is not allowed during this exam.'
    )
  }
}

function onKeydown(event) {
  if (
    props.preventCopyPaste &&
    (event.ctrlKey ||
      event.metaKey) &&
    [
      'c',
      'v',
      'x',
      'a',
    ].includes(
      event.key.toLowerCase()
    )
  ) {
    event.preventDefault()
  }
}

/*
|--------------------------------------------------------------------------
| Submit Quiz
|--------------------------------------------------------------------------
*/

function submitQuiz(force = false) {
  if (submitting.value) {
    return
  }

  if (
    !force &&
    !confirm(
      'Are you sure you want to submit?'
    )
  ) {
    return
  }

  submitting.value = true

  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }

  router.post(
    route(
      'student.quizzes.submit',
      props.attempt.id
    ),
    {
      answers: answers.value,

      tab_switches:
        tabSwitches.value,
    },
    {
      preserveScroll: true,

      onError: () => {
        submitting.value = false

        /*
        |--------------------------------------------------------------
        | Timer
        |--------------------------------------------------------------
        |
        | Jika request gagal, timer bisa dilanjutkan.
        |
        */
        if (
          timeLeft.value > 0 &&
          !timerInterval
        ) {
          startTimer()
        }
      },
    }
  )
}

/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
  initializeRandomizedOptions()

  startTimer()

  document.addEventListener(
    'visibilitychange',
    onVisibilityChange
  )

  if (props.preventCopyPaste) {
    document.addEventListener(
      'copy',
      preventAction
    )

    document.addEventListener(
      'paste',
      preventAction
    )

    document.addEventListener(
      'cut',
      preventAction
    )

    document.addEventListener(
      'contextmenu',
      preventAction
    )

    document.addEventListener(
      'keydown',
      onKeydown
    )
  }
})

/*
|--------------------------------------------------------------------------
| Before Unmount
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }

  document.removeEventListener(
    'visibilitychange',
    onVisibilityChange
  )

  if (props.preventCopyPaste) {
    document.removeEventListener(
      'copy',
      preventAction
    )

    document.removeEventListener(
      'paste',
      preventAction
    )

    document.removeEventListener(
      'cut',
      preventAction
    )

    document.removeEventListener(
      'contextmenu',
      preventAction
    )

    document.removeEventListener(
      'keydown',
      onKeydown
    )
  }
})
</script>

<template>
  <div class="quiz-take">

    <!-- Timer -->
    <VCard
      v-if="hasTimer"
      class="quiz-timer mb-6"
      flat
      border
    >
      <VCardText
        class="d-flex align-center justify-space-between"
      >
        <div
          class="d-flex align-center ga-2"
        >
          <VIcon icon="tabler-clock" />

          <span
            class="font-weight-medium"
          >
            Time Remaining
          </span>
        </div>

        <span
          class="text-h6 font-weight-bold"
          :class="{
            'text-error':
              timeLeft <= 60
          }"
        >
          {{ formattedTime }}
        </span>
      </VCardText>
    </VCard>

    <!-- Tab Switch Warning -->
    <VAlert
      v-if="tabSwitches > 0"
      type="warning"
      variant="tonal"
      class="mb-6"
    >
      Tab switches:
      <strong>
        {{ tabSwitches }}
      </strong>
      /
      {{ maxTabSwitches }}
    </VAlert>

    <!-- Questions -->
    <div
      class="d-flex flex-column ga-6"
    >
      <VCard
        v-for="(
          question,
          index
        ) in questions"
        :key="question.id"
        class="quiz-question"
        flat
        border
      >
        <VCardText>

          <!-- Question Header -->
          <div
            class="d-flex align-start ga-3 mb-5"
          >
            <VAvatar
              size="36"
              color="primary"
              variant="tonal"
            >
              {{ index + 1 }}
            </VAvatar>

            <div
              class="flex-grow-1"
            >
              <div
                class="text-body-1 font-weight-medium"
                style="white-space: pre-line;"
              >
                {{ question.question_text }}
              </div>

              <div
                v-if="question.points"
                class="text-caption text-medium-emphasis mt-1"
              >
                {{ question.points }}
                point(s)
              </div>
            </div>
          </div>

          <!-- Multiple Choice -->
          <div
            v-if="
              question.question_type ===
                'multiple_choice' &&
              question.options?.length
            "
            class="d-flex flex-column ga-3"
          >
            <VLabel
              v-for="(
                option,
                optIndex
              ) in question.options"
              :key="
                getOptionKey(
                  question,
                  optIndex
                )
              "
              class="option-label pa-3 rounded-lg"
              :class="{
                'option-selected':
                  isSelected(
                    question,
                    option
                  )
              }"
              @click="
                selectOption(
                  question,
                  option
                )
              "
            >
              <VRadio
                :model-value="
                  isSelected(
                    question,
                    option
                  )
                "
                :value="true"
                class="me-2"
              />

              <span>
                {{ option }}
              </span>
            </VLabel>
          </div>

          <!-- True / False -->
          <div
            v-else-if="
              question.question_type ===
              'true_false'
            "
            class="d-flex flex-column ga-3"
          >

            <!-- True -->
            <VLabel
              class="option-label pa-3 rounded-lg"
              :class="{
                'option-selected':
                  isTrueSelected(
                    question
                  )
              }"
              @click="
                selectTrueFalse(
                  question,
                  'true'
                )
              "
            >
              <VRadio
                :model-value="
                  isTrueSelected(
                    question
                  )
                "
                :value="true"
                class="me-2"
              />

              <span>
                True
              </span>
            </VLabel>

            <!-- False -->
            <VLabel
              class="option-label pa-3 rounded-lg"
              :class="{
                'option-selected':
                  isFalseSelected(
                    question
                  )
              }"
              @click="
                selectTrueFalse(
                  question,
                  'false'
                )
              "
            >
              <VRadio
                :model-value="
                  isFalseSelected(
                    question
                  )
                "
                :value="true"
                class="me-2"
              />

              <span>
                False
              </span>
            </VLabel>

          </div>

          <!-- Essay -->
          <VTextarea
            v-else
            v-model="
              answers[question.id]
            "
            label="Your answer"
            placeholder="Type your answer here..."
            rows="5"
            auto-grow
          />

        </VCardText>
      </VCard>
    </div>

    <!-- Submit -->
    <div
      class="d-flex justify-end mt-8"
    >
      <VBtn
        color="primary"
        size="large"
        :loading="submitting"
        :disabled="submitting"
        @click="submitQuiz()"
      >
        <VIcon
          start
          icon="tabler-check"
        />

        Submit Quiz
      </VBtn>
    </div>

  </div>
</template>

<style scoped>
.quiz-take {
  position: relative;
}

.quiz-timer {
  position: sticky;
  top: 16px;
  z-index: 10;
}

.quiz-question {
  overflow: hidden;
}

.option-label {
  cursor: pointer;
  border: 1px solid
    rgba(
      var(--v-border-color),
      var(--v-border-opacity)
    );

  transition:
    background-color 0.2s ease,
    border-color 0.2s ease;
}

.option-label:hover {
  background-color:
    rgba(
      var(--v-theme-primary),
      0.04
    );
}

.option-selected {
  border-color:
    rgb(var(--v-theme-primary));

  background-color:
    rgba(
      var(--v-theme-primary),
      0.08
    );
}
</style>