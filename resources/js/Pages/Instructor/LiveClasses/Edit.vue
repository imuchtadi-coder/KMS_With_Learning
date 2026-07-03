<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  liveClass: { type: Object, required: true },
})

const form = useForm({
  title: props.liveClass.title,
  description: props.liveClass.description || '',
  provider: props.liveClass.provider,
  meeting_url: props.liveClass.meeting_url || '',
  meeting_id: props.liveClass.meeting_id || '',
  password: props.liveClass.password || '',
  start_time: props.liveClass.start_time ? new Date(props.liveClass.start_time).toISOString().slice(0, 16) : '',
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Edit Live Class</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">{{ course.title }}</p>

    <VCard flat border class="pa-8" max-width="640">
      <VForm @submit.prevent="form.put(route('instructor.live-classes.update', [course.id, liveClass.id]))" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Session Title" variant="outlined" density="default" />
        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default" rows="3" />
        <VSelect v-model="form.provider" label="Provider" variant="outlined" density="default" :items="['zoom', 'google_meet', 'microsoft_teams', 'other'].map(v => ({ title: v.replace(/_/g, ' '), value: v }))" />
        <VTextField v-model="form.meeting_url" label="Meeting URL" variant="outlined" density="default" type="url" />
        <VTextField v-model="form.meeting_id" label="Meeting ID" variant="outlined" density="default" />
        <VTextField v-model="form.password" label="Password" variant="outlined" density="default" />
        <VTextField v-model="form.start_time" label="Start Time" variant="outlined" density="default" type="datetime-local" />
        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing">Update Session</VBtn>
          <VBtn variant="outlined" :href="route('instructor.live-classes.index', course.id)">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
