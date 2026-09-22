<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  tag: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  name: props.tag.name || '',
})

const submit = () => {
  form.put(route('admin.tags.update', props.tag.id))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">
        Edit Tag
      </h4>

      <p class="text-body-2 text-medium-emphasis">
        {{ props.tag.name }}
      </p>
    </div>

    <VCard flat border max-width="500">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VTextField
            v-model="form.name"
            label="Tag Name"
            :error-messages="form.errors.name"
            required
            class="mb-4"
          />

          <div class="d-flex ga-2">
            <VBtn
              type="submit"
              color="primary"
              :loading="form.processing"
            >
              Update
            </VBtn>

            <VBtn
              variant="outlined"
              :href="route('admin.tags.index')"
            >
              Cancel
            </VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>