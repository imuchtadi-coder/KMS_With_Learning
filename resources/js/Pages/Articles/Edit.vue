<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import TiptapEditor from '@core/components/TiptapEditor.vue'

const props = defineProps({
  article: { type: Object, required: true },
  spaces: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
  potentialParents: { type: Array, default: () => [] },
  canPublishDirectly: { type: Boolean, default: false },
})

const fileInputRef = ref(null)
const selectedFiles = ref([])

// Compute suggested next version (1.0 -> 1.1)
const suggestNextVersion = () => {
  const cur = props.article.version || '1.0'
  const parts = cur.split('.')
  if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
    return `${parts[0]}.${Number(parts[1]) + 1}`
  }
  return `${cur}.1`
}

const form = useForm({
  _method: 'put',
  title: props.article.title || '',
  space_id: props.article.space_id || null,
  parent_id: props.article.parent_id || null,
  review_cycle_days: props.article.review_cycle_days || 180,
  order: props.article.order || 0,
  new_version: suggestNextVersion(),
  excerpt: props.article.excerpt || '',
  category_id: props.article.category_id || '',
  tag_ids: (props.article.tags || []).map(t => t.id),
  content: props.article.content || '',
  status: props.article.status === 'rejected' ? 'pending_review' : props.article.status,
  change_summary: '',
  attachments: [],
})

const formatBytes = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i]
}

const onFilesSelected = (e) => {
  const files = Array.from(e.target.files || [])
  selectedFiles.value.push(...files)
  form.attachments = selectedFiles.value
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const removeNewFile = (index) => {
  selectedFiles.value.splice(index, 1)
  form.attachments = selectedFiles.value
}

const deleteExistingAttachment = (attachmentId) => {
  if (confirm('Hapus lampiran ini?')) {
    router.delete(route('articles.attachments.destroy', attachmentId), {
      preserveScroll: true,
    })
  }
}

const statusOptions = computed(() => {
  if (props.canPublishDirectly) {
    return [
      { title: 'Resmi Dipublikasikan (SME Approved)', value: 'published' },
      { title: 'Ajukan Peninjauan ke SME (Pending Review)', value: 'pending_review' },
      { title: 'Simpan Sebagai Draft Pribadi', value: 'draft' },
      { title: 'Arsipkan Dokumen', value: 'archived' },
    ]
  }
  return [
    { title: 'Ajukan Peninjauan ke SME (Pending Review)', value: 'pending_review' },
    { title: 'Simpan Sebagai Draft Pribadi', value: 'draft' },
  ]
})

const submit = () => {
  form.post(route('articles.update', props.article.id), {
    forceFormData: true,
  })
}
</script>

<template>
  <div class="article-edit-page">
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
      <div class="d-flex align-center ga-3">
        <Link :href="route('articles.show', article.id)" class="text-decoration-none">
          <VBtn variant="tonal" prepend-icon="mdi-arrow-left" size="small">
            Kembali ke Dokumen
          </VBtn>
        </Link>
        <div>
          <h4 class="text-h4 font-weight-bold mb-0">Revisi Dokumen (Versi Baru)</h4>
          <span class="text-caption text-medium-emphasis">Versi saat ini: <strong>v{{ article.version || '1.0' }}</strong></span>
        </div>
      </div>
    </div>

    <!-- Feedback alert if article was rejected by SME -->
    <VAlert
      v-if="article.status === 'rejected' && article.review_notes"
      type="error"
      variant="tonal"
      class="mb-6 border-error"
      prepend-icon="mdi-alert-circle"
    >
      <h6 class="text-subtitle-1 font-weight-bold mb-1">Catatan Revisi dari Subject Matter Expert (SME):</h6>
      <p class="text-body-2 mb-0">{{ article.review_notes }}</p>
    </VAlert>

    <form @submit.prevent="submit">
      <VRow>
        <!-- Main Content Column -->
        <VCol cols="12" md="8">
          <VCard flat border class="pa-4 mb-4">
            <!-- Article Title -->
            <div class="mb-4">
              <VTextField
                v-model="form.title"
                label="Judul Dokumen / SOP"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-format-title"
                :error-messages="form.errors.title"
                required
              />
            </div>

            <!-- Excerpt -->
            <div class="mb-4">
              <VTextarea
                v-model="form.excerpt"
                label="Ringkasan Singkat (Excerpt)"
                variant="outlined"
                density="compact"
                rows="2"
                counter="500"
                :error-messages="form.errors.excerpt"
              />
            </div>

            <!-- Rich Text Content -->
            <div class="mb-2">
              <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                Konten Dokumen (Rich Text) <span class="text-error">*</span>
              </label>
              <div class="border rounded editor-wrapper">
                <TiptapEditor
                  v-model="form.content"
                  placeholder="Ketik isi dokumen di sini..."
                />
              </div>
              <span v-if="form.errors.content" class="text-caption text-error mt-1 d-block">
                {{ form.errors.content }}
              </span>
            </div>
          </VCard>

          <!-- Attachments Card -->
          <VCard flat border class="pa-4">
            <div class="d-flex align-center justify-space-between mb-3">
              <div>
                <h6 class="text-subtitle-2 font-weight-bold mb-0">
                  <VIcon icon="mdi-paperclip" size="18" class="me-1 text-primary" />
                  Lampiran Dokumen Resmi
                </h6>
                <span class="text-caption text-medium-emphasis">
                  Kelola dokumen yang sudah ada atau tambahkan file lampiran baru.
                </span>
              </div>
              <VBtn
                color="primary"
                variant="tonal"
                size="small"
                prepend-icon="mdi-upload"
                @click="fileInputRef?.click()"
              >
                Tambah File Baru
              </VBtn>
            </div>

            <input
              ref="fileInputRef"
              type="file"
              multiple
              class="d-none"
              @change="onFilesSelected"
            >

            <!-- Existing Attachments -->
            <div v-if="article.attachments && article.attachments.length > 0" class="mb-3">
              <p class="text-caption font-weight-bold text-medium-emphasis mb-1">Lampiran Saat Ini:</p>
              <div class="d-flex flex-column ga-2">
                <VCard
                  v-for="att in article.attachments"
                  :key="att.id"
                  variant="outlined"
                  class="pa-2 px-3 d-flex align-center justify-space-between"
                >
                  <div class="d-flex align-center ga-2 text-truncate">
                    <VIcon icon="mdi-file-check-outline" color="success" size="20" />
                    <div class="text-truncate">
                      <p class="font-weight-medium mb-0 text-truncate text-body-2">
                        {{ att.title }}.{{ att.file_type }}
                      </p>
                      <span class="text-caption text-medium-emphasis">
                        {{ formatBytes(att.file_size) }} • {{ att.downloads_count }} unduhan
                      </span>
                    </div>
                  </div>
                  <div class="d-flex align-center ga-1">
                    <VBtn
                      icon="mdi-download"
                      variant="text"
                      size="small"
                      density="compact"
                      title="Unduh"
                      :href="route('articles.attachments.download', att.id)"
                      target="_blank"
                    />
                    <VBtn
                      icon="mdi-delete"
                      variant="text"
                      color="error"
                      size="small"
                      density="compact"
                      title="Hapus"
                      @click="deleteExistingAttachment(att.id)"
                    />
                  </div>
                </VCard>
              </div>
            </div>

            <!-- Newly Added Files -->
            <div v-if="selectedFiles.length > 0">
              <p class="text-caption font-weight-bold text-primary mb-1">File Baru Siap Diunggah:</p>
              <div class="d-flex flex-column ga-2">
                <VCard
                  v-for="(f, idx) in selectedFiles"
                  :key="idx"
                  variant="tonal"
                  class="pa-2 px-3 d-flex align-center justify-space-between bg-light-primary"
                >
                  <div class="d-flex align-center ga-2 text-truncate">
                    <VIcon icon="mdi-cloud-upload-outline" color="primary" size="20" />
                    <div class="text-truncate">
                      <p class="font-weight-medium mb-0 text-truncate text-body-2">{{ f.name }}</p>
                      <span class="text-caption text-medium-emphasis">{{ formatBytes(f.size) }}</span>
                    </div>
                  </div>
                  <VBtn
                    icon="mdi-close"
                    variant="text"
                    color="error"
                    size="small"
                    density="compact"
                    @click="removeNewFile(idx)"
                  />
                </VCard>
              </div>
            </div>

            <div
              v-if="(!article.attachments || article.attachments.length === 0) && selectedFiles.length === 0"
              class="text-center pa-5 border border-dashed rounded text-medium-emphasis"
            >
              <VIcon icon="mdi-cloud-upload-outline" size="30" class="mb-1 opacity-50" />
              <p class="text-caption mb-0">Belum ada file dilampirkan.</p>
            </div>
          </VCard>
        </VCol>

        <!-- Sidebar Options Column -->
        <VCol cols="12" md="4">
          <!-- Version Control & Publishing Card -->
          <VCard flat border class="pa-4 mb-4">
            <h6 class="text-subtitle-2 font-weight-bold mb-3">Kontrol Versi & Publikasi</h6>

            <div class="mb-3">
              <VTextField
                v-model="form.new_version"
                label="Nomor Versi Hasil Revisi"
                variant="outlined"
                density="compact"
                hint="Auto-incremented dari versi sebelumnya"
                persistent-hint
              />
            </div>

            <div class="mb-3">
              <VTextarea
                v-model="form.change_summary"
                label="Catatan Ringkasan Revisi"
                placeholder="Contoh: Pembaruan dosis dan langkah APD..."
                variant="outlined"
                density="compact"
                rows="2"
                :rules="[v => !!v || 'Catatan perubahan wajib diisi untuk riwayat versi']"
              />
            </div>

            <div class="mb-3">
              <VSelect
                v-model="form.status"
                :items="statusOptions"
                item-title="title"
                item-value="value"
                label="Status Dokumen"
                variant="outlined"
                density="compact"
              />
            </div>

            <div class="d-flex flex-column ga-2 pt-2">
              <VBtn
                type="submit"
                color="primary"
                variant="elevated"
                size="default"
                block
                prepend-icon="mdi-content-save-check"
                :loading="form.processing"
                :disabled="form.processing || !form.title || !form.content"
              >
                Simpan Versi Baru
              </VBtn>

              <VBtn
                variant="outlined"
                block
                size="default"
                :href="route('articles.show', article.id)"
              >
                Batal
              </VBtn>
            </div>
          </VCard>

          <!-- Taxonomy Card -->
          <VCard flat border class="pa-4">
            <h6 class="text-subtitle-2 font-weight-bold mb-3">Taksonomi & Tag</h6>

            <!-- Knowledge Space -->
            <div v-if="spaces.length > 0" class="mb-3">
              <VSelect
                v-model="form.space_id"
                :items="spaces"
                item-title="name"
                item-value="id"
                label="Knowledge Space"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-folder-multiple-outline"
                clearable
                :error-messages="form.errors.space_id"
              />
            </div>

            <!-- Parent Document -->
            <div v-if="potentialParents.length > 0" class="mb-3">
              <VAutocomplete
                v-model="form.parent_id"
                :items="potentialParents"
                item-title="title"
                item-value="id"
                label="Dokumen Induk (Opsional)"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-file-tree-outline"
                clearable
                :error-messages="form.errors.parent_id"
              />
            </div>

            <!-- Review Cycle -->
            <div class="mb-3">
              <VSelect
                v-model="form.review_cycle_days"
                :items="[
                  { title: '3 Bulan (90 Hari) - Kritis/Dinamis', value: 90 },
                  { title: '6 Bulan (180 Hari) - Standar SOP', value: 180 },
                  { title: '1 Tahun (365 Hari) - Regulasi Stabil', value: 365 },
                ]"
                item-title="title"
                item-value="value"
                label="Masa Siklus Review"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-history"
              />
            </div>

            <!-- Category -->
            <div class="mb-3">
              <VSelect
                v-model="form.category_id"
                :items="categories"
                item-title="name"
                item-value="id"
                label="Kategori Pengetahuan"
                variant="outlined"
                density="compact"
                clearable
                :error-messages="form.errors.category_id"
              />
            </div>

            <!-- Tags -->
            <div>
              <VAutocomplete
                v-model="form.tag_ids"
                :items="tags"
                item-title="name"
                item-value="id"
                label="Tag Topik"
                multiple
                chips
                closable-chips
                variant="outlined"
                density="compact"
                :error-messages="form.errors.tag_ids"
              />
            </div>
          </VCard>
        </VCol>
      </VRow>
    </form>
  </div>
</template>

<style scoped>
.editor-wrapper {
  background-color: rgb(var(--v-theme-surface));
  min-height: 250px;
}
</style>
