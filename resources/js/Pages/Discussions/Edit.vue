<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import TiptapEditor from '@core/components/TiptapEditor.vue'

const props = defineProps({
  discussion: { type: Object, required: true },
  course: { type: Object, default: null },
})

const form = useForm({
  title: props.discussion.title,
  content: props.discussion.content,
})

const submit = () => {
  form.put(route('discussions.update', props.discussion.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="discussion-edit-page">
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div class="d-flex align-center ga-3">
        <Link
          :href="route('discussions.show', discussion.id)"
          class="text-decoration-none"
        >
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="mdi-arrow-left"
          >
            Kembali
          </VBtn>
        </Link>
        <div>
          <h4 class="text-h4 font-weight-bold mb-0">Edit Pertanyaan Diskusi</h4>
          <span v-if="course" class="text-caption text-medium-emphasis">
            Terkait Kursus: {{ course.title }}
          </span>
        </div>
      </div>
    </div>

    <VCard flat border class="pa-5 pa-md-6 rounded-lg">
      <VForm @submit.prevent="submit" class="d-flex flex-column ga-5">
        <div>
          <label class="text-subtitle-2 font-weight-medium d-block mb-1">
            Judul Pertanyaan / Masalah <span class="text-error">*</span>
          </label>
          <VTextField
            v-model="form.title"
            variant="outlined"
            density="compact"
            prepend-inner-icon="mdi-format-title"
            :rules="[v => !!v || 'Judul pertanyaan wajib diisi']"
            hide-details="auto"
          />
        </div>

        <div>
          <label class="text-subtitle-2 font-weight-medium d-block mb-1">
            Rincian Pertanyaan / Deskripsi Kondisi <span class="text-error">*</span>
          </label>
          <div class="rich-editor-box border rounded">
            <TiptapEditor
              v-model="form.content"
              placeholder="Jelaskan detail kendala, gejala di lapangan, atau konteks pertanyaan..."
            />
          </div>
        </div>

        <div class="d-flex align-center ga-3 pt-3 border-t">
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            size="default"
            prepend-icon="mdi-content-save-check"
            :loading="form.processing"
            :disabled="form.processing || !form.title?.trim() || !form.content?.trim()"
          >
            Simpan Perubahan
          </VBtn>

          <Link
            :href="route('discussions.show', discussion.id)"
            class="text-decoration-none"
          >
            <VBtn
              variant="outlined"
              size="default"
            >
              Batal
            </VBtn>
          </Link>
        </div>
      </VForm>
    </VCard>
  </div>
</template>

<style scoped>
.rich-editor-box {
  background-color: rgb(var(--v-theme-surface));
  min-height: 220px;
}

.border-t {
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}
</style>
