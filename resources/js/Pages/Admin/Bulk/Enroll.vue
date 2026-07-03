<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

defineProps({
  courses: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const flash = usePage().props.flash
const search = ref('')

const filteredUsers = computed(() => {
  if (!search.value) return users
  const q = search.value.toLowerCase()
  return users.filter(u => u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q))
})

const form = useForm({
  course_id: '',
  user_ids: [],
})

const submit = () => {
  form.post(route('admin.bulk.enroll'))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Bulk Enroll</h4>
      <p class="text-body-2 text-medium-emphasis">Enroll multiple students in a course at once</p>
    </div>

    <VAlert v-if="flash?.success" type="success" closable :text="flash.success" class="mb-4" />

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
          />

          <div class="text-caption font-weight-medium mb-2">Select Students</div>
          <VTextField v-model="search" label="Search students..." density="default" class="mb-2" />
          <div style="max-height: 300px; overflow-y: auto;">
            <VCheckbox
              v-for="user in filteredUsers"
              :key="user.id"
              :value="user.id"
              :label="user.name + ' (' + user.email + ')'"
              density="default"
              hide-details
              class="mb-1"
            />
          </div>
          <VAlert v-if="form.errors.user_ids" type="error" variant="tonal" dense class="mt-2" :text="form.errors.user_ids" />

          <VBtn type="submit" color="primary" :loading="form.processing" class="mt-4">Enroll Selected</VBtn>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
