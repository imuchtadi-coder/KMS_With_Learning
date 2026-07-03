<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  courses: { type: Array, default: () => [] },
})

const flash = usePage().props.flash
const enrolledStudents = ref([])
const loadingStudents = ref(false)

const form = useForm({
  course_id: '',
  user_ids: [],
})

const loadStudents = async (courseId) => {
  if (!courseId) { enrolledStudents.value = []; return }
  loadingStudents.value = true
  try {
    const res = await fetch(route('admin.bulk.enrolled-students', { course_id: courseId }))
    enrolledStudents.value = await res.json()
  } catch (e) {
    enrolledStudents.value = []
  }
  loadingStudents.value = false
}

const selectAll = () => {
  form.user_ids = enrolledStudents.value.map(s => s.id)
}

const deselectAll = () => {
  form.user_ids = []
}

const submit = () => {
  form.post(route('admin.bulk.certificates'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Bulk Certificates</h4>
      <p class="text-body-2 text-medium-emphasis">Generate certificates for multiple students at once</p>
    </div>

    <VAlert v-if="flash?.success" type="success" closable :text="flash.success" class="mb-4" />
    <VAlert v-if="flash?.errors?.length" type="warning" closable class="mb-4">
      <div v-for="err in flash.errors" :key="err">{{ err }}</div>
    </VAlert>

    <VCard flat border max-width="700">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VSelect
            v-model="form.course_id"
            :items="courses.map(c => ({ title: c.title, value: c.id }))"
            label="Course"
            :error-messages="form.errors.course_id"
            required
            class="mb-4"
            @update:model-value="loadStudents"
          />

          <div v-if="enrolledStudents.length" class="d-flex align-center justify-space-between mb-2">
            <span class="text-caption font-weight-medium">{{ enrolledStudents.length }} enrolled student(s)</span>
            <div class="d-flex ga-1">
              <VBtn variant="text" size="x-small" @click="selectAll">Select All</VBtn>
              <VBtn variant="text" size="x-small" @click="deselectAll">Deselect All</VBtn>
            </div>
          </div>

          <div v-if="loadingStudents" class="text-center pa-4">
            <VProgressCircular indeterminate size="24" />
          </div>
          <div v-else-if="enrolledStudents.length" style="max-height: 300px; overflow-y: auto;">
            <VCheckbox
              v-for="student in enrolledStudents"
              :key="student.id"
              :value="student.id"
              :label="student.name + ' (' + student.email + ')'"
              density="default"
              hide-details
              class="mb-1"
            />
          </div>
          <VAlert v-else-if="form.course_id" type="info" variant="tonal" dense class="mt-2" text="No enrolled students found for this course" />

          <VBtn type="submit" color="primary" :loading="form.processing" class="mt-4">Generate Certificates</VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
