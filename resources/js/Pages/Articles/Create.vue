<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import TiptapEditor from '@core/components/TiptapEditor.vue'

const props = defineProps({
  categories: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
  canPublishDirectly: { type: Boolean, default: false },
})

const fileInputRef = ref(null)
const selectedFiles = ref([])
const isDragging = ref(false)

const form = useForm({
  title: '',
  version: '1.0',
  excerpt: '',
  category_id: null,
  tag_ids: [],
  content: '',
  status: props.canPublishDirectly ? 'published' : 'pending_review',
  change_summary: 'Versi awal dokumen SOP / pengetahuan',
  attachments: [],
})

const formatBytes = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i]
}

const getFileIcon = (filename) => {
  const ext = filename?.split('.').pop()?.toLowerCase()
  if (['pdf'].includes(ext)) return { icon: 'mdi-file-pdf-box', color: 'error' }
  if (['doc', 'docx'].includes(ext)) return { icon: 'mdi-file-word-box', color: 'info' }
  if (['xls', 'xlsx', 'csv'].includes(ext)) return { icon: 'mdi-file-excel-box', color: 'success' }
  if (['ppt', 'pptx'].includes(ext)) return { icon: 'mdi-file-powerpoint-box', color: 'warning' }
  if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) return { icon: 'mdi-file-image', color: 'primary' }
  return { icon: 'mdi-file-document-outline', color: 'primary' }
}

const onFilesSelected = (e) => {
  const files = Array.from(e.target.files || [])
  selectedFiles.value.push(...files)
  form.attachments = selectedFiles.value
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const onDrop = (e) => {
  isDragging.value = false
  const files = Array.from(e.dataTransfer?.files || [])
  if (files.length) {
    selectedFiles.value.push(...files)
    form.attachments = selectedFiles.value
  }
}

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1)
  form.attachments = selectedFiles.value
}

const statusOptions = computed(() => {
  if (props.canPublishDirectly) {
    return [
      {
        title: 'Resmi Dipublikasikan (SME Approved)',
        value: 'published',
        subtitle: 'Langsung berlaku umum',
        icon: 'mdi-check-decagram',
        color: 'success',
      },
      {
        title: 'Ajukan Review ke SME (Pending Review)',
        value: 'pending_review',
        subtitle: 'Masuk antrean validasi pakar',
        icon: 'mdi-clock-time-four-outline',
        color: 'warning',
      },
      {
        title: 'Simpan Sebagai Draft Pribadi',
        value: 'draft',
        subtitle: 'Hanya dapat dilihat oleh Anda',
        icon: 'mdi-file-edit-outline',
        color: 'secondary',
      },
    ]
  }
  return [
    {
      title: 'Ajukan Review ke Subject Matter Expert (SME)',
      value: 'pending_review',
      subtitle: 'Masuk antrean validasi standar',
      icon: 'mdi-clock-time-four-outline',
      color: 'warning',
    },
    {
      title: 'Simpan Sebagai Draft Pribadi',
      value: 'draft',
      subtitle: 'Simpan sementara tanpa publikasi',
      icon: 'mdi-file-edit-outline',
      color: 'secondary',
    },
  ]
})

const currentStatusInfo = computed(() => {
  if (form.status === 'published') {
    return {
      color: 'success',
      icon: 'mdi-check-decagram',
      label: 'Langsung Berlaku & Terbit Umum',
      desc: 'Dokumen akan langsung dapat diakses sebagai rujukan resmi organisasi.',
    }
  }
  if (form.status === 'pending_review') {
    return {
      color: 'warning',
      icon: 'mdi-clock-time-four-outline',
      label: 'Menunggu Validasi SME',
      desc: 'Dokumen akan dikurasi oleh Subject Matter Expert sebelum disahkan.',
    }
  }
  return {
    color: 'secondary',
    icon: 'mdi-file-edit-outline',
    label: 'Draft Internal Penulis',
    desc: 'Hanya Anda yang dapat melihat dan menyunting dokumen ini.',
  }
})

const submit = () => {
  form.post(route('articles.store'), {
    forceFormData: true,
  })
}
</script>

<template>
  <div class="article-create-page pb-8">
    <!-- Breadcrumb & Top Bar Navigation -->
    <div class="d-flex align-center justify-space-between mb-3 flex-wrap ga-2">
      <div class="d-flex align-center ga-2">
        <Link :href="route('articles.index')" class="text-decoration-none">
          <VBtn
            variant="tonal"
            color="secondary"
            size="small"
            rounded="lg"
            prepend-icon="mdi-chevron-left"
            density="compact"
          >
            Knowledge Base
          </VBtn>
        </Link>
        <span class="text-caption text-disabled">/</span>
        <span class="text-caption text-medium-emphasis">
          Formulir SOP & Panduan
        </span>
      </div>

      <VChip
        color="primary"
        variant="tonal"
        size="x-small"
        class="font-weight-medium"
      >
        <VIcon start icon="mdi-pencil-box-outline" size="14" />
        Mode Pembuatan Dokumen Baru
      </VChip>
    </div>

    <!-- Compact Header Card -->
    <VCard
      rounded="xl"
      elevation="0"
      border
      class="pa-4 mb-4 header-card position-relative overflow-hidden"
    >
      <div class="header-card-glow" />
      <div class="d-flex align-center justify-space-between flex-wrap ga-3 position-relative">
        <div class="d-flex align-center ga-3">
          <div class="header-icon-box rounded-lg d-flex align-center justify-center">
            <VIcon icon="mdi-file-certificate-outline" size="24" color="primary" />
          </div>
          <div>
            <h5 class="text-h6 font-weight-bold mb-0 text-high-emphasis">
              Tulis Dokumen / SOP Baru
            </h5>
            <span class="text-caption text-medium-emphasis">
              Dokumentasi terstruktur, standar operasional, dan materi pengetahuan dengan kontrol versi dan alur review SME.
            </span>
          </div>
        </div>

        <div class="d-flex align-center ga-2">
          <VBtn
            variant="outlined"
            color="secondary"
            size="small"
            rounded="lg"
            :href="route('articles.index')"
          >
            Batal
          </VBtn>

          <VBtn
            color="primary"
            variant="elevated"
            size="small"
            rounded="lg"
            class="px-4 font-weight-medium"
            :loading="form.processing"
            :disabled="form.processing || !form.title.trim() || !form.content.trim()"
            @click="submit"
          >
            <VIcon
              start
              :icon="form.status === 'pending_review' ? 'mdi-send-check-outline' : (form.status === 'published' ? 'mdi-check-circle-outline' : 'mdi-content-save-outline')"
              size="16"
            />
            {{ form.status === 'pending_review' ? 'Ajukan ke SME' : (form.status === 'published' ? 'Terbitkan' : 'Simpan Draft') }}
          </VBtn>
        </div>
      </div>
    </VCard>

    <form @submit.prevent="submit">
      <VRow dense>
        <!-- Kolom Kiri: Konten Dokumen Utama -->
        <VCol cols="12" md="8">
          <!-- Card 1: Identitas & Rangkuman Dokumen -->
          <VCard
            rounded="xl"
            elevation="0"
            border
            class="mb-4 overflow-hidden"
          >
            <div class="pa-3 px-4 border-b bg-surface-subtle d-flex align-center ga-2">
              <VIcon icon="mdi-information-outline" size="18" color="primary" />
              <h6 class="text-subtitle-2 font-weight-bold mb-0 text-high-emphasis">
                Identitas Dokumen
              </h6>
            </div>

            <div class="pa-4">
              <!-- Judul Dokumen -->
              <div class="mb-3">
                <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-1">
                  Judul Dokumen / Prosedur SOP <span class="text-error">*</span>
                </label>
                <VTextField
                  v-model="form.title"
                  placeholder="Misal: Standar Operasional Prosedur Penanganan Hama Terpadu"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-format-title"
                  :error-messages="form.errors.title"
                  counter="255"
                  required
                />
              </div>

              <!-- Excerpt / Ringkasan Singkat -->
              <div>
                <div class="d-flex align-center justify-space-between mb-1">
                  <label class="text-caption font-weight-bold text-medium-emphasis">
                    Ringkasan Singkat (Executive Summary)
                  </label>
                  <span class="text-caption text-disabled" style="font-size: 0.7rem;">Opsional</span>
                </div>
                <VTextarea
                  v-model="form.excerpt"
                  placeholder="Tuliskan ikhtisar 1-2 kalimat mengenai ruang lingkup panduan ini agar mudah dicari..."
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-text-short"
                  rows="2"
                  counter="500"
                  :error-messages="form.errors.excerpt"
                />
              </div>
            </div>
          </VCard>

          <!-- Card 2: Lembar Kerja Isi Dokumen (Rich Text Editor) -->
          <VCard
            rounded="xl"
            elevation="0"
            border
            class="mb-4 overflow-hidden"
          >
            <div class="pa-3 px-4 border-b bg-surface-subtle d-flex align-center justify-space-between flex-wrap ga-2">
              <div class="d-flex align-center ga-2">
                <VIcon icon="mdi-file-document-edit-outline" size="18" color="primary" />
                <h6 class="text-subtitle-2 font-weight-bold mb-0 text-high-emphasis">
                  Isi Dokumen & Prosedur Operasional <span class="text-error">*</span>
                </h6>
              </div>

              <VChip size="x-small" color="secondary" variant="tonal">
                Rich Text
              </VChip>
            </div>

            <div class="pa-4">
              <div class="editor-container-wrapper border rounded-lg overflow-hidden">
                <TiptapEditor
                  v-model="form.content"
                  placeholder="Ketik isi SOP, tahapan operasional, atau ketentuan rujukan teknis di sini..."
                />
              </div>

              <span v-if="form.errors.content" class="text-caption text-error mt-1 d-block">
                {{ form.errors.content }}
              </span>

              <div class="d-flex align-center justify-space-between mt-2 text-caption text-medium-emphasis">
                <span class="d-flex align-center ga-1" style="font-size: 0.72rem;">
                  <VIcon icon="mdi-information-outline" size="14" />
                  Gunakan tombol <strong>Numbered List</strong> pada toolbar untuk urutan langkah kerja.
                </span>
              </div>
            </div>
          </VCard>

          <!-- Card 3: Berkas Lampiran & Dokumen Resmi (Attachments) -->
          <VCard
            rounded="xl"
            elevation="0"
            border
            class="overflow-hidden"
          >
            <div class="pa-3 px-4 border-b bg-surface-subtle d-flex align-center justify-space-between flex-wrap ga-2">
              <div class="d-flex align-center ga-2">
                <VIcon icon="mdi-paperclip" size="18" color="primary" />
                <h6 class="text-subtitle-2 font-weight-bold mb-0 text-high-emphasis">
                  Berkas Lampiran Pendukung
                </h6>
              </div>

              <VChip
                v-if="selectedFiles.length > 0"
                size="x-small"
                color="primary"
                variant="tonal"
              >
                {{ selectedFiles.length }} Berkas
              </VChip>
            </div>

            <div class="pa-4">
              <!-- Compact Interactive Drag & Drop Area -->
              <div
                class="dropzone-box text-center pa-4 border border-dashed rounded-lg transition-all"
                :class="{ 'is-dragging': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="onDrop"
              >
                <VIcon icon="mdi-cloud-upload-outline" size="26" color="primary" class="mb-1" />
                <div class="text-subtitle-2 font-weight-medium mb-0 text-high-emphasis" style="font-size: 0.8rem;">
                  Tarik & letakkan berkas lampiran ke area ini
                </div>
                <div class="text-caption text-medium-emphasis mb-2" style="font-size: 0.7rem;">
                  PDF, DOCX, XLSX, PPTX (maks. 50MB per file)
                </div>
                <VBtn
                  color="primary"
                  variant="tonal"
                  size="small"
                  rounded="lg"
                  density="compact"
                  prepend-icon="mdi-folder-upload-outline"
                  @click="fileInputRef?.click()"
                >
                  Pilih File
                </VBtn>

                <input
                  ref="fileInputRef"
                  type="file"
                  multiple
                  class="d-none"
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                  @change="onFilesSelected"
                >
              </div>

              <!-- List of Selected Files -->
              <div v-if="selectedFiles.length > 0" class="mt-3 d-flex flex-column ga-2">
                <div
                  v-for="(f, idx) in selectedFiles"
                  :key="idx"
                  class="file-item-card pa-2 px-3 border rounded-lg d-flex align-center justify-space-between transition-all"
                >
                  <div class="d-flex align-center ga-2 text-truncate">
                    <VIcon
                      :icon="getFileIcon(f.name).icon"
                      :color="getFileIcon(f.name).color"
                      size="20"
                    />
                    <div class="text-truncate">
                      <span class="font-weight-medium text-caption text-high-emphasis d-block text-truncate">
                        {{ f.name }}
                      </span>
                      <span class="text-caption text-disabled" style="font-size: 0.68rem;">
                        {{ formatBytes(f.size) }}
                      </span>
                    </div>
                  </div>

                  <VBtn
                    icon="mdi-delete-outline"
                    variant="text"
                    color="error"
                    size="small"
                    density="compact"
                    title="Hapus file"
                    @click="removeFile(idx)"
                  />
                </div>
              </div>
            </div>
          </VCard>
        </VCol>

        <!-- Kolom Kanan: Pengaturan Tata Kelola & Taksonomi -->
        <VCol cols="12" md="4">
          <!-- Card 1: Tata Kelola & Alur Publikasi -->
          <VCard
            rounded="xl"
            elevation="0"
            border
            class="mb-4 overflow-hidden"
          >
            <div class="pa-3 px-4 border-b bg-surface-subtle d-flex align-center ga-2">
              <VIcon icon="mdi-shield-check-outline" size="18" color="primary" />
              <h6 class="text-subtitle-2 font-weight-bold mb-0 text-high-emphasis">
                Tata Kelola & Alur Review
              </h6>
            </div>

            <div class="pa-4">
              <!-- Compact Status Banner Info -->
              <div
                class="status-info-banner pa-2 px-3 rounded-lg mb-3 border d-flex align-start ga-2"
                :class="`bg-${currentStatusInfo.color}-subtle border-${currentStatusInfo.color}`"
              >
                <VIcon
                  :icon="currentStatusInfo.icon"
                  :color="currentStatusInfo.color"
                  size="18"
                  class="mt-1"
                />
                <div>
                  <div class="text-caption font-weight-bold" :class="`text-${currentStatusInfo.color}`">
                    {{ currentStatusInfo.label }}
                  </div>
                  <div class="text-caption text-medium-emphasis" style="font-size: 0.72rem; line-height: 1.3;">
                    {{ currentStatusInfo.desc }}
                  </div>
                </div>
              </div>

              <!-- Status Selection -->
              <div class="mb-3">
                <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-1">
                  Status Penerbitan
                </label>
                <VSelect
                  v-model="form.status"
                  :items="statusOptions"
                  item-title="title"
                  item-value="value"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-file-check-outline"
                >
                  <template #item="{ props: itemProps, item }">
                    <VListItem v-bind="itemProps" :title="item.raw.title" :subtitle="item.raw.subtitle">
                      <template #prepend>
                        <VIcon :icon="item.raw.icon" :color="item.raw.color" size="18" class="me-2" />
                      </template>
                    </VListItem>
                  </template>
                </VSelect>
              </div>

              <!-- Version Input -->
              <div class="mb-3">
                <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-1">
                  Nomor Versi Standar
                </label>
                <VTextField
                  v-model="form.version"
                  placeholder="1.0"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-source-branch"
                  hint="Format versi dokumen: 1.0, 1.1"
                  persistent-hint
                />
              </div>

              <!-- Changelog / Release Notes -->
              <div class="mb-4">
                <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-1">
                  Catatan Rilis / Perubahan
                </label>
                <VTextField
                  v-model="form.change_summary"
                  placeholder="Ringkasan catatan versi"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-note-text-outline"
                />
              </div>

              <!-- Action Buttons -->
              <div class="d-flex flex-column ga-2 pt-2 border-t">
                <VBtn
                  type="submit"
                  color="primary"
                  variant="elevated"
                  size="default"
                  rounded="lg"
                  block
                  class="font-weight-medium"
                  :loading="form.processing"
                  :disabled="form.processing || !form.title.trim() || !form.content.trim()"
                >
                  <VIcon
                    start
                    :icon="form.status === 'pending_review' ? 'mdi-send-check-outline' : (form.status === 'published' ? 'mdi-check-circle-outline' : 'mdi-content-save-outline')"
                    size="16"
                  />
                  {{ form.status === 'pending_review' ? 'Ajukan ke SME' : (form.status === 'published' ? 'Terbitkan Sekarang' : 'Simpan Draft') }}
                </VBtn>

                <VBtn
                  variant="outlined"
                  color="secondary"
                  size="small"
                  rounded="lg"
                  block
                  :href="route('articles.index')"
                >
                  Batalkan
                </VBtn>
              </div>
            </div>
          </VCard>

          <!-- Card 2: Taksonomi & Pengelompokan -->
          <VCard
            rounded="xl"
            elevation="0"
            border
            class="mb-4 overflow-hidden"
          >
            <div class="pa-3 px-4 border-b bg-surface-subtle d-flex align-center ga-2">
              <VIcon icon="mdi-tag-multiple-outline" size="18" color="primary" />
              <h6 class="text-subtitle-2 font-weight-bold mb-0 text-high-emphasis">
                Klasifikasi & Topik
              </h6>
            </div>

            <div class="pa-4">
              <!-- Kategori -->
              <div class="mb-3">
                <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-1">
                  Kategori Pengetahuan
                </label>
                <VSelect
                  v-model="form.category_id"
                  :items="categories"
                  item-title="name"
                  item-value="id"
                  placeholder="Pilih Kategori"
                  variant="outlined"
                  density="compact"
                  prepend-inner-icon="mdi-folder-outline"
                  clearable
                  :error-messages="form.errors.category_id"
                />
              </div>

              <!-- Tags -->
              <div>
                <label class="text-caption font-weight-bold text-medium-emphasis d-block mb-1">
                  Tag Kata Kunci
                </label>
                <VAutocomplete
                  v-model="form.tag_ids"
                  :items="tags"
                  item-title="name"
                  item-value="id"
                  placeholder="Cari atau pilih tag..."
                  prepend-inner-icon="mdi-tag-outline"
                  multiple
                  chips
                  closable-chips
                  variant="outlined"
                  density="compact"
                  :error-messages="form.errors.tag_ids"
                />
              </div>
            </div>
          </VCard>

          <!-- Card 3: Panduan Standar SOP (Best Practices Widget) -->
          <VCard
            rounded="xl"
            variant="tonal"
            color="primary"
            class="pa-3 px-4 border"
          >
            <div class="d-flex align-center ga-2 mb-2">
              <VIcon icon="mdi-lightbulb-on-outline" size="18" color="primary" />
              <h6 class="text-caption font-weight-bold mb-0 text-primary">
                Pedoman Penulisan SOP Standar
              </h6>
            </div>
            <ul class="text-caption ps-3 mb-0 text-medium-emphasis" style="font-size: 0.72rem; line-height: 1.5;">
              <li class="mb-1">
                <strong>Spesifik:</strong> Kalimat instruksi lugas tanpa multitafsir.
              </li>
              <li class="mb-1">
                <strong>Berurutan:</strong> Gunakan numbered list (1, 2, 3) untuk alur tahapan.
              </li>
              <li class="mb-1">
                <strong>Penanggung Jawab:</strong> Sebutkan peran yang bertugas di tiap instruksi.
              </li>
              <li>
                <strong>Formulir Baku:</strong> Lampirkan blanko format resmi jika ada.
              </li>
            </ul>
          </VCard>
        </VCol>
      </VRow>
    </form>
  </div>
</template>

<style scoped>
.header-card {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.04) 0%, rgba(var(--v-theme-surface), 1) 100%);
}

.header-card-glow {
  position: absolute;
  top: -30px;
  right: -30px;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(var(--v-theme-primary), 0.1) 0%, transparent 70%);
  pointer-events: none;
}

.header-icon-box {
  width: 42px;
  height: 42px;
  background-color: rgba(var(--v-theme-primary), 0.1);
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
  flex-shrink: 0;
}

.bg-surface-subtle {
  background-color: rgba(var(--v-theme-on-surface), 0.02);
}

.editor-container-wrapper {
  background-color: rgb(var(--v-theme-surface));
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.editor-container-wrapper:focus-within {
  border-color: rgb(var(--v-theme-primary)) !important;
  box-shadow: 0 0 0 1px rgb(var(--v-theme-primary));
}

.dropzone-box {
  background-color: rgba(var(--v-theme-on-surface), 0.015);
  border-color: rgba(var(--v-border-color), var(--v-border-opacity)) !important;
  cursor: pointer;
}

.dropzone-box.is-dragging,
.dropzone-box:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
  border-color: rgb(var(--v-theme-primary)) !important;
}

.file-item-card {
  background-color: rgba(var(--v-theme-on-surface), 0.015);
  border-color: rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

.file-item-card:hover {
  background-color: rgba(var(--v-theme-on-surface), 0.03);
}

.bg-primary-subtle { background-color: rgba(var(--v-theme-primary), 0.08) !important; }
.bg-success-subtle { background-color: rgba(var(--v-theme-success), 0.08) !important; }
.bg-warning-subtle { background-color: rgba(var(--v-theme-warning), 0.08) !important; }
.bg-error-subtle { background-color: rgba(var(--v-theme-error), 0.08) !important; }
.bg-info-subtle { background-color: rgba(var(--v-theme-info), 0.08) !important; }
.bg-secondary-subtle { background-color: rgba(var(--v-theme-secondary), 0.08) !important; }
</style>
