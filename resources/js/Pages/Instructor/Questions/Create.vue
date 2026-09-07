<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
})

// Menyimpan index jawaban benar
const multipleChoiceAnswer = ref(null)
const trueFalseAnswer = ref(null)

const form = useForm({
  type: 'multiple_choice',
  question_text: '',
  points: 1,
  options: [
    {
      text: '',
      is_correct: false,
    },
    {
      text: '',
      is_correct: false,
    },
  ],
})

/**
 * Tambah option Multiple Choice
 */
function addOption() {
  form.options.push({
    text: '',
    is_correct: false,
  })
}

/**
 * Hapus option Multiple Choice
 * Minimal tetap 2 option
 */
function removeOption(index) {
  if (form.options.length <= 2) {
    return
  }

  form.options.splice(index, 1)

  // Jika jawaban yang benar ikut dihapus
  if (multipleChoiceAnswer.value === index) {
    multipleChoiceAnswer.value = null

    form.options.forEach(option => {
      option.is_correct = false
    })

    return
  }

  // Jika option sebelum jawaban benar dihapus,
  // sesuaikan index jawaban benar
  if (
    multipleChoiceAnswer.value !== null &&
    multipleChoiceAnswer.value > index
  ) {
    multipleChoiceAnswer.value--
  }
}

/**
 * Ganti jenis pertanyaan
 */
function changeQuestionType(value) {
  // =========================
  // MULTIPLE CHOICE
  // =========================
  if (value === 'multiple_choice') {
    form.options = [
      {
        text: '',
        is_correct: false,
      },
      {
        text: '',
        is_correct: false,
      },
    ]

    multipleChoiceAnswer.value = null
    trueFalseAnswer.value = null
  }

  // =========================
  // TRUE / FALSE
  // =========================
  if (value === 'true_false') {
    form.options = [
      {
        text: 'True',
        is_correct: false,
      },
      {
        text: 'False',
        is_correct: false,
      },
    ]

    multipleChoiceAnswer.value = null
    trueFalseAnswer.value = null
  }

  // =========================
  // ESSAY
  // =========================
  if (value === 'essay') {
    form.options = []

    multipleChoiceAnswer.value = null
    trueFalseAnswer.value = null
  }
}

/**
 * Pilih jawaban benar Multiple Choice
 */
function selectMultipleChoiceAnswer(index) {
  multipleChoiceAnswer.value = index

  form.options.forEach((option, i) => {
    option.is_correct = i === index
  })

  console.log(
    'MULTIPLE CHOICE ANSWER INDEX:',
    index
  )

  console.log(
    'OPTIONS AFTER SELECT:',
    JSON.stringify(form.options, null, 2)
  )
}

/**
 * Pilih jawaban True / False
 */
function selectTrueFalseAnswer(index) {
  trueFalseAnswer.value = index

  form.options.forEach((option, i) => {
    option.is_correct = i === index
  })

  console.log(
    'TRUE/FALSE ANSWER INDEX:',
    index
  )

  console.log(
    'OPTIONS AFTER SELECT:',
    JSON.stringify(form.options, null, 2)
  )
}

/**
 * Submit question
 */
function submit() {
  const payload = {
    type: form.type,

    question_text: String(
      form.question_text ?? ''
    ).trim(),

    points: Number(form.points),

    options: Array.isArray(form.options)
      ? form.options.map(option => ({
          text: String(
            option.text ?? ''
          ).trim(),

          is_correct: Boolean(
            option.is_correct
          ),
        }))
      : [],
  }

  console.log(
    'SUBMIT QUESTION PAYLOAD:',
    payload
  )

  console.log(
    'OPTIONS JSON:',
    JSON.stringify(
      payload.options,
      null,
      2
    )
  )

  form
    .transform(() => payload)
    .post(
      route(
        'instructor.questions.store',
        props.course.id
      ),
      {
        preserveScroll: true,

        onSuccess: page => {
          console.log(
            'QUESTION CREATED SUCCESSFULLY'
          )

          console.log(
            'REDIRECT URL:',
            page.url
          )
        },

        onError: errors => {
          console.error(
            'QUESTION CREATE ERRORS:',
            errors
          )
        },

        onFinish: () => {
          console.log(
            'QUESTION REQUEST FINISHED'
          )
        },
      }
    )
}
</script>

<template>
  <div>
    <!-- ========================= -->
    <!-- PAGE HEADER -->
    <!-- ========================= -->
    <h4 class="text-h4 font-weight-bold mb-1">
      Create Question
    </h4>

    <p class="text-body-1 text-medium-emphasis mb-6">
      {{ course.title }}
    </p>

    <!-- ========================= -->
    <!-- FORM CARD -->
    <!-- ========================= -->
    <VCard
      flat
      border
      class="pa-8"
      max-width="720"
    >
      <VForm
        class="d-flex flex-column ga-5"
        @submit.prevent="submit"
      >
        <!-- ========================= -->
        <!-- QUESTION TYPE -->
        <!-- ========================= -->
        <VSelect
          v-model="form.type"
          label="Question Type"
          variant="outlined"
          density="default"
          :items="[
            {
              title: 'Multiple Choice',
              value: 'multiple_choice',
            },
            {
              title: 'True/False',
              value: 'true_false',
            },
            {
              title: 'Essay',
              value: 'essay',
            },
          ]"
          @update:model-value="changeQuestionType"
        />

        <!-- ========================= -->
        <!-- QUESTION TEXT -->
        <!-- ========================= -->
        <VTextarea
          v-model="form.question_text"
          label="Question Text"
          variant="outlined"
          density="default"
          rows="4"
          :rules="[
            v =>
              !!v ||
              'Question text is required',
          ]"
          hide-details="auto"
        />

        <!-- Backend Question Error -->
        <div
          v-if="form.errors.question_text"
          class="text-caption text-error mt-n3"
        >
          {{ form.errors.question_text }}
        </div>

        <!-- ========================= -->
        <!-- POINTS -->
        <!-- ========================= -->
        <VTextField
          v-model="form.points"
          label="Points"
          variant="outlined"
          density="default"
          type="number"
          min="1"
          :rules="[
            v =>
              !!v ||
              'Points are required',

            v =>
              Number(v) >= 1 ||
              'Points must be at least 1',
          ]"
          hide-details="auto"
        />

        <!-- ========================= -->
        <!-- ANSWER OPTIONS -->
        <!-- ========================= -->
        <div v-if="form.type !== 'essay'">

          <!-- Backend Validation Error -->
          <div
            v-if="form.errors.options"
            class="text-caption text-error mb-2"
          >
            {{ form.errors.options }}
          </div>

          <!-- Answer Options Header -->
          <div
            class="d-flex align-center justify-space-between mb-3"
          >
            <label class="text-body-2 font-weight-bold">
              Answer Options
            </label>

            <!-- Add Option -->
            <VBtn
              v-if="
                form.type ===
                'multiple_choice'
              "
              size="x-small"
              variant="tonal"
              color="primary"
              type="button"
              @click="addOption"
            >
              + Add Option
            </VBtn>
          </div>

          <!-- ========================= -->
          <!-- MULTIPLE CHOICE -->
          <!-- ========================= -->
          <VRadioGroup
            v-if="
              form.type ===
              'multiple_choice'
            "
            :model-value="
              multipleChoiceAnswer
            "
            hide-details
            @update:model-value="
              selectMultipleChoiceAnswer
            "
          >
            <div
              v-for="(option, i) in form.options"
              :key="i"
              class="d-flex align-center ga-3 mb-2"
            >
              <!-- Correct Answer Radio -->
              <VRadio
                :value="i"
                hide-details
              />

              <!-- Option Text -->
              <VTextField
                v-model="option.text"
                variant="outlined"
                density="default"
                hide-details
                placeholder="Option text"
                class="flex-grow-1"
              />

              <!-- Remove Option -->
              <VBtn
                v-if="
                  form.options.length > 2
                "
                variant="text"
                color="error"
                size="x-small"
                icon
                type="button"
                @click="removeOption(i)"
              >
                <VIcon
                  icon="mdi-close"
                />
              </VBtn>
            </div>
          </VRadioGroup>

          <!-- Multiple Choice Validation -->
          <div
            v-if="
              form.type ===
                'multiple_choice' &&
              !form.options.some(
                option =>
                  option.is_correct
              )
            "
            class="text-caption text-error mt-1"
          >
            Select a correct answer
          </div>

          <!-- ========================= -->
          <!-- TRUE / FALSE -->
          <!-- ========================= -->
          <VRadioGroup
            v-if="
              form.type ===
              'true_false'
            "
            :model-value="
              trueFalseAnswer
            "
            hide-details
            @update:model-value="
              selectTrueFalseAnswer
            "
          >
            <VRadio
              v-for="(option, i) in form.options"
              :key="i"
              :label="option.text"
              :value="i"
            />
          </VRadioGroup>

          <!-- True / False Validation -->
          <div
            v-if="
              form.type ===
                'true_false' &&
              !form.options.some(
                option =>
                  option.is_correct
              )
            "
            class="text-caption text-error mt-1"
          >
            Select the correct answer
          </div>

        </div>

        <!-- ========================= -->
        <!-- BUTTONS -->
        <!-- ========================= -->
        <div class="d-flex ga-3 pt-2">

          <!-- Create -->
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            :loading="
              form.processing
            "
          >
            Create Question
          </VBtn>

          <!-- Cancel -->
          <VBtn
            variant="outlined"
            type="button"
            :href="
              route(
                'instructor.questions.index',
                course.id
              )
            "
          >
            Cancel
          </VBtn>

        </div>
      </VForm>
    </VCard>
  </div>
</template>