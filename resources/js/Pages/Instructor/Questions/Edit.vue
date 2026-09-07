<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  question: {
    type: Object,
    required: true,
  },
})

/*
|--------------------------------------------------------------------------
| Normalize Options
|--------------------------------------------------------------------------
| Pastikan options selalu menjadi array object:
| [
|   {
|     text: 'Option A',
|     is_correct: false
|   },
|   ...
| ]
*/
function normalizeOptions(options) {
  if (!options) {
    return []
  }

  // Jika Laravel/Inertia mengirim JSON string
  if (typeof options === 'string') {
    try {
      options = JSON.parse(options)
    } catch (error) {
      console.error('Failed to parse question.options:', error)
      return []
    }
  }

  if (!Array.isArray(options)) {
    console.warn('question.options is not an array:', options)
    return []
  }

  return options.map(option => {
    // Format lama:
    // "Depok"
    if (typeof option === 'string') {
      return {
        text: option,
        is_correct:
          option === props.question.correct_answer,
      }
    }

    // Format baru:
    // { text: "Depok", is_correct: false }
    return {
      text: String(option?.text ?? ''),
      is_correct:
        option?.is_correct === true ||
        option?.is_correct === 1 ||
        option?.is_correct === '1' ||
        option?.is_correct === 'true' ||
        option?.text === props.question.correct_answer,
    }
  })
}

const normalizedOptions = normalizeOptions(
  props.question.options
)

console.log('EDIT QUESTION:', props.question)
console.log('QUESTION OPTIONS:', props.question.options)
console.log('NORMALIZED OPTIONS:', normalizedOptions)

/*
|--------------------------------------------------------------------------
| Find Correct Answer
|--------------------------------------------------------------------------
*/
const findCorrectAnswerIndex = () => {
  if (
    props.question.question_type !== 'multiple_choice' ||
    !normalizedOptions.length
  ) {
    return null
  }

  // 1. Cari berdasarkan is_correct
  const indexFromFlag = normalizedOptions.findIndex(
    option => option.is_correct === true
  )

  if (indexFromFlag !== -1) {
    return indexFromFlag
  }

  // 2. Jika tidak ada, cari berdasarkan correct_answer
  const correctAnswer = String(
    props.question.correct_answer ?? ''
  )
    .trim()
    .toLowerCase()

  if (correctAnswer) {
    const indexFromAnswer = normalizedOptions.findIndex(
      option =>
        String(option.text ?? '')
          .trim()
          .toLowerCase() === correctAnswer
    )

    if (indexFromAnswer !== -1) {
      return indexFromAnswer
    }
  }

  return null
}

const multipleChoiceAnswer = ref(
  findCorrectAnswerIndex()
)



/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/
const form = useForm({
  question_type: props.question.question_type,
  question_text: props.question.question_text,
  points: props.question.points,
  difficulty_level:
    props.question.difficulty_level || 'medium',

  correct_answer:
    props.question.correct_answer || '',

  options: normalizedOptions,
})

console.log('FORM OPTIONS:', form.options)

/*
|--------------------------------------------------------------------------
| Select Multiple Choice Answer
|--------------------------------------------------------------------------
*/
function selectMultipleChoiceAnswer(index) {
  multipleChoiceAnswer.value = index

  form.options.forEach((option, i) => {
    option.is_correct = i === index
  })

  if (index !== null && index !== undefined) {
    form.correct_answer =
      form.options[index]?.text?.trim() || ''
  } else {
    form.correct_answer = ''
  }

  console.log('========== SELECT ANSWER ==========')
  console.log('Selected Index:', index)
  console.log('Correct Answer:', form.correct_answer)
  console.log('Updated Options:', form.options)
}

/*
|--------------------------------------------------------------------------
| Remove Option
|--------------------------------------------------------------------------
*/
function removeOption(index) {
  form.options.splice(index, 1)

  if (multipleChoiceAnswer.value === index) {
    multipleChoiceAnswer.value = null
    form.correct_answer = ''
  } else if (
    multipleChoiceAnswer.value !== null &&
    multipleChoiceAnswer.value > index
  ) {
    multipleChoiceAnswer.value--
  }

  if (multipleChoiceAnswer.value !== null) {
    const correctOption =
      form.options[multipleChoiceAnswer.value]

    if (correctOption) {
      correctOption.is_correct = true

      form.correct_answer =
        correctOption.text.trim()
    }
  }
}

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/
function submit() {
  console.log('========== SUBMIT EDIT QUESTION ==========')
  console.log('Question ID:', props.question.id)
  console.log('Course ID:', props.course.id)
  console.log('Question Type:', form.question_type)
  console.log('Options BEFORE:', form.options)
  console.log('Correct Index:', multipleChoiceAnswer.value)

  if (form.question_type === 'multiple_choice') {
    form.options = form.options.map(option => ({
      text: String(option.text ?? '').trim(),
      is_correct: Boolean(option.is_correct),
    }))

    // Pastikan ada jawaban benar
    const correctOption = form.options.find(
      option => option.is_correct === true
    )

    if (!correctOption) {
      console.error('NO CORRECT ANSWER SELECTED')

      alert('Silakan pilih jawaban yang benar.')

      return
    }

    form.correct_answer = correctOption.text.trim()
  }
  
  if (form.question_type === 'true_false') {
    console.log('========== TRUE/FALSE DEBUG ==========')
    console.log('Question Type:', form.question_type)
    console.log('Correct Answer:', form.correct_answer)
    console.log('Correct Answer Type:', typeof form.correct_answer)
  }

  const payload = {
    question_type: form.question_type,
    question_text: form.question_text,
    points: form.points,
    difficulty_level: form.difficulty_level,
    options: form.options,
    correct_answer: form.correct_answer,
  }

  console.log('UPDATE PAYLOAD:', payload)
  console.log(
    'PAYLOAD CORRECT ANSWER:',
    payload.correct_answer
  )

  form.put(
    route('instructor.questions.update', [
      props.course.id,
      props.question.id,
    ]),
    {
      preserveScroll: true,

      onStart: () => {
        console.log('UPDATE REQUEST STARTED')
      },

      onSuccess: (page) => {
        console.log('QUESTION UPDATED SUCCESSFULLY')
        console.log('RESPONSE PAGE:', page)
      },

      onError: (errors) => {
        console.error('UPDATE QUESTION ERROR:', errors)

        alert(
          'Update gagal. Silakan cek Console untuk melihat error.'
        )
      },

      onFinish: () => {
        console.log('UPDATE REQUEST FINISHED')
      },
    }
  )
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">
      Edit Question
    </h4>

    <p class="text-body-1 text-medium-emphasis mb-6">
      {{ course.title }}
    </p>

    <VCard
      flat
      border
      class="pa-8"
      max-width="720"
    >
      <VForm
        @submit.prevent="submit"
        class="d-flex flex-column ga-5"
      >
        <!-- Question Type -->
        <VSelect
          v-model="form.question_type"
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
        />

        <!-- Question -->
        <VTextarea
          v-model="form.question_text"
          label="Question Text"
          variant="outlined"
          density="default"
          rows="4"
        />

        <!-- Points -->
        <VTextField
          v-model="form.points"
          label="Points"
          variant="outlined"
          density="default"
          type="number"
          min="1"
        />

        <!-- Difficulty -->
        <VSelect
          v-model="form.difficulty_level"
          label="Difficulty Level"
          variant="outlined"
          density="default"
          :items="
            ['easy', 'medium', 'hard'].map(v => ({
              title: v,
              value: v,
            }))
          "
        />

        <!-- Multiple Choice -->
        <div v-if="form.question_type === 'multiple_choice'">
          <label class="text-body-2 font-weight-bold d-block mb-2">
            Options
          </label>

          <VRadioGroup
            v-model="multipleChoiceAnswer"
            @update:model-value="selectMultipleChoiceAnswer"
            hide-details
          >
            <div
              v-for="(opt, i) in form.options"
              :key="i"
              class="d-flex align-center ga-2 mb-2"
            >
              <!-- Radio jawaban benar -->
              <VRadio
                :value="i"
                hide-details
              />

              <!-- Option text -->
              <VTextField
                v-model="opt.text"
                variant="outlined"
                density="default"
                hide-details
                placeholder="Option"
                class="flex-grow-1"
                @update:model-value="
                  value => {
                    opt.text = value

                    if (multipleChoiceAnswer === i) {
                      form.correct_answer = value
                    }
                  }
                "
              />

              <!-- Remove option -->
              <VBtn
                icon
                variant="text"
                color="error"
                size="small"
                @click="removeOption(i)"
                :disabled="form.options.length <= 2"
              >
                <VIcon icon="mdi-delete" />
              </VBtn>
            </div>
          </VRadioGroup>

          <div class="text-caption text-medium-emphasis mt-2">
            Pilih radio button untuk menentukan jawaban yang benar.
          </div>
        </div>

        <!-- True / False -->
        <VSelect
          v-else-if="form.question_type === 'true_false'"
          v-model="form.correct_answer"
          label="Correct Answer"
          variant="outlined"
          density="default"
          :items="['True', 'False']"
        />

        <!-- Buttons -->
        <div class="d-flex ga-3 pt-2">
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            :loading="form.processing"
          >
            Update Question
          </VBtn>

          <VBtn
            variant="outlined"
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