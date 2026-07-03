<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  title: '',
  slug: '',
  description: '',
  level: '',
  price: '',
  rating: '0',
  thumbnail: null,
})

function generateSlug(title) {
  return title.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-').replace(/--+/g, '-')
}

const onTitleBlur = () => {
  if (!form.slug && form.title) form.slug = generateSlug(form.title)
}
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-bold mb-1">Create New Course</h4>
    <p class="text-body-1 text-medium-emphasis mb-6">Create a new course</p>

    <VCard flat border class="pa-8" max-width="720">
      <VForm @submit.prevent="form.post(route('instructor.courses.store'))" class="d-flex flex-column ga-5">
        <VTextField v-model="form.title" label="Course Title" variant="outlined" density="default"
          :rules="[v => !!v || 'Title is required']" hide-details="auto" @blur="onTitleBlur" />

        <VTextField v-model="form.slug" label="Course URL Slug" variant="outlined" density="default"
          hint="Auto-generated from title if left blank" persistent-hint />

        <VTextarea v-model="form.description" label="Description" variant="outlined" density="default"
          rows="5" :rules="[v => !!v || 'Description is required']" hide-details="auto" />

        <VSelect v-model="form.level" label="Level" variant="outlined" density="default"
          :items="[{ title: 'Select level', value: '' }, { title: 'Beginner', value: 'beginner' }, { title: 'Intermediate', value: 'intermediate' }, { title: 'Advanced', value: 'advanced' }]" />

        <VTextField v-model="form.price" label="Price (Optional)" variant="outlined" density="default"
          type="number" step="0.01" min="0" hint="Leave as 0 for free course" persistent-hint />

        <VTextField v-model="form.rating" label="Initial Rating (Optional)" variant="outlined" density="default"
          type="number" step="0.1" min="0" max="5" hint="0 to 5 stars" persistent-hint />

        <VFileInput v-model="form.thumbnail" label="Thumbnail Image" variant="outlined" density="default"
          accept="image/jpg,image/jpeg,image/png,image/webp"
          prepend-icon="mdi-camera"
          hint="Recommended: 1280x720px, max 2MB" persistent-hint
          :rules="[v => !v || v.size <= 2097152 || 'Max 2MB']" hide-details="auto"
          :error-messages="form.errors?.thumbnail"
          @update:model-value="form.clearErrors('thumbnail')" />

        <VAlert color="info" variant="tonal" density="compact">
          <div class="font-weight-semibold">Course Status</div>
          <p class="text-body-2">New courses are created as <strong>Draft</strong> and unpublished. You'll be able to add sections and lessons after creation.</p>
        </VAlert>

        <div class="d-flex ga-3 pt-2">
          <VBtn type="submit" color="primary" variant="elevated" :loading="form.processing" :disabled="form.processing">Create Course</VBtn>
          <VBtn variant="outlined" :href="route('instructor.courses.index')">Cancel</VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>
