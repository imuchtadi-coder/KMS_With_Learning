<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({
  category: { type: Object, required: true },
  parents: { type: Array, default: () => [] },
})

const form = useForm({
  name: '',
  description: '',
  parent_id: '',
})

const init = (cat) => {
  form.name = cat.name || ''
  form.description = cat.description || ''
  form.parent_id = cat.parent_id || ''
}

const submit = () => {
  form.put(route('admin.categories.update', category.id))
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold mb-1">Edit Category</h4>
      <p class="text-body-2 text-medium-emphasis">{{ category.name }}</p>
    </div>

    <VCard flat border max-width="600">
      <VCardText>
        <VForm @submit.prevent="submit">
          <VTextField
            v-model="form.name"
            label="Name"
            :error-messages="form.errors.name"
            required
            class="mb-4"
          />
          <VSelect
            v-model="form.parent_id"
            :items="parents.map(p => ({ title: p.name, value: p.id }))"
            label="Parent Category (optional)"
            :error-messages="form.errors.parent_id"
            clearable
            class="mb-4"
          />
          <VTextarea
            v-model="form.description"
            label="Description"
            :error-messages="form.errors.description"
            rows="3"
            class="mb-4"
          />
          <div class="d-flex ga-2">
            <VBtn type="submit" color="primary" :loading="form.processing">Update</VBtn>
            <VBtn variant="outlined" :href="route('admin.categories.index')">Cancel</VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<script>
export default {
  created() {
    this.init(this.$props.category)
  }
}
</script>
