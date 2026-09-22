<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
})

const form = useForm({
  title: '',
  description: '',
  provider: 'zoom',
  meeting_url: '',
  meeting_id: '',
  password: '',
  start_time: '',
  end_time: '',
  section_id: null,
  recurring: false,
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Create Live Class</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="640">
        <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Session Title" variant="outlined" density="default" :rules="[v => !!v || 'Title is required']" hide-details="auto" />
        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="3" hide-details="auto" />
        <VSelect v-model="form.provider" label="Provider" variant="outlined" density="default" :items="['zoom', 'google_meet', 'jitsi', 'microsoft_teams', 'other'].map(v => ({ title: v.replace(/_/g, ' '), value: v }))" />
        <VTextField v-model="form.meeting_url" label="Meeting URL" variant="outlined" density="default" type="url" placeholder="https://..." />
        <VTextField v-model="form.meeting_id" label="Meeting ID (Optional)" variant="outlined" density="default" />
        <VTextField v-model="form.password" label="Password (Optional)" variant="outlined" density="default" />
        <VTextField v-model="form.start_time" label="Start Time" variant="outlined" density="default" type="datetime-local" :rules="[v => !!v || 'Start time is required']" hide-details="auto" />
        <VTextField v-model="form.end_time" label="End Time" variant="outlined" density="default" type="datetime-local" :rules="[v => !!v || 'End time is required']" hide-details="auto"/>
        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Create Session</VBtn>
          <VBtn variant="outlined" :href="route('instructor.live-classes.index', course.id)">Cancel</VBtn>
        </div>
        <div v-if="Object.keys(form.errors).length" class="mt-4">
          <VAlert
            type="error"
            variant="tonal"
            title="Unable to create live class"
          >
            <ul class="pl-5">
              <li
                v-for="(error, field) in form.errors"
                :key="field"
              >
                {{ error }}
              </li>
            </ul>
          </VAlert>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
