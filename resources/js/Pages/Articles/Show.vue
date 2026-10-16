<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  article: { type: Object, required: true },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  canReview: { type: Boolean, default: false },
  canSubmitReview: { type: Boolean, default: false },
  viewingVersion: { type: Object, default: null },
  relatedArticles: { type: Array, default: () => [] },
})

const page = usePage()
const reviewNotes = ref('')
const isReviewing = ref(false)

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  })
}

const formatBytes = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i]
}

const getFileIcon = (ext) => {
  const e = (ext || '').toLowerCase()
  if (['pdf'].includes(e)) return { icon: 'mdi-file-pdf-box', color: 'error' }
  if (['doc', 'docx'].includes(e)) return { icon: 'mdi-file-word-box', color: 'info' }
  if (['ppt', 'pptx'].includes(e)) return { icon: 'mdi-file-powerpoint-box', color: 'warning' }
  if (['xls', 'xlsx', 'csv'].includes(e)) return { icon: 'mdi-file-excel-box', color: 'success' }
  if (['zip', 'rar', '7z', 'tar'].includes(e)) return { icon: 'mdi-folder-zip-outline', color: 'secondary' }
  if (['png', 'jpg', 'jpeg', 'webp'].includes(e)) return { icon: 'mdi-file-image', color: 'primary' }
  return { icon: 'mdi-file-document-outline', color: 'default' }
}

const approveDocument = () => {
  if (confirm('Setujui dokumen ini dan terbitkan secara resmi untuk organisasi?')) {
    isReviewing.value = true
    router.post(route('articles.approve', props.article.id), {
      review_notes: reviewNotes.value || undefined,
    }, {
      onFinish: () => isReviewing.value = false,
    })
  }
}

const rejectDocument = () => {
  if (!reviewNotes.value || reviewNotes.value.trim().length < 5) {
    alert('Harap masukkan catatan revisi yang jelas untuk penulis (minimal 5 karakter).')
    return
  }
  if (confirm('Kirimkan permintaan revisi ke penulis?')) {
    isReviewing.value = true
    router.post(route('articles.reject', props.article.id), {
      review_notes: reviewNotes.value,
    }, {
      onFinish: () => isReviewing.value = false,
    })
  }
}

const submitForReview = () => {
  if (confirm('Ajukan dokumen ini ke Subject Matter Expert (SME) untuk peninjauan?')) {
    router.post(route('articles.submit-review', props.article.id))
  }
}

const restoreHistoricalVersion = (verId) => {
  if (confirm('Apakah Anda yakin ingin memulihkan dokumen ke versi ini? Versi baru akan dibuat dengan konten dari versi ini.')) {
    router.post(route('articles.versions.restore', [props.article.id, verId]))
  }
}

const deleteArticle = () => {
  if (confirm('Apakah Anda yakin ingin menghapus dokumen pengetahuan ini?')) {
    router.delete(route('articles.destroy', props.article.id))
  }
}

// Display content from historical version if viewing snapshot, otherwise current
const displayTitle = computed(() => props.viewingVersion ? props.viewingVersion.title : props.article.title)
const displayExcerpt = computed(() => props.viewingVersion ? props.viewingVersion.excerpt : props.article.excerpt)
const displayContent = computed(() => props.viewingVersion ? props.viewingVersion.content : props.article.content)
const displayVersion = computed(() => props.viewingVersion ? props.viewingVersion.version : (props.article.version || '1.0'))
</script>

<template>
  <div class="article-show-page">
    <!-- Historical Version Notice Banner -->
    <VAlert
      v-if="viewingVersion"
      type="info"
      variant="elevated"
      class="mb-6 font-weight-medium"
      prepend-icon="mdi-history"
    >
      <div class="d-flex align-center justify-space-between flex-wrap ga-3">
        <div>
          <strong>Arsip Riwayat Versi:</strong> Anda sedang melihat snapshot <strong>Versi {{ viewingVersion.version }}</strong> yang dibuat pada {{ formatDateTime(viewingVersion.created_at) }}.
          <div class="text-caption opacity-90 mt-1">
            Catatan revisi: "{{ viewingVersion.change_summary || 'Tidak ada catatan' }}"
          </div>
        </div>
        <div class="d-flex align-center ga-2">
          <VBtn
            v-if="canEdit"
            color="white"
            variant="flat"
            size="small"
            class="text-info font-weight-bold"
            prepend-icon="mdi-restore"
            @click="restoreHistoricalVersion(viewingVersion.id)"
          >
            Pulihkan Versi Ini
          </VBtn>
          <VBtn
            variant="tonal"
            color="white"
            size="small"
            :href="route('articles.show', article.id)"
          >
            Kembali ke Versi Terkini
          </VBtn>
        </div>
      </div>
    </VAlert>

    <!-- Top Action Bar -->
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-3">
      <Link :href="route('articles.index')" class="text-decoration-none">
        <VBtn variant="tonal" prepend-icon="mdi-arrow-left">
          Kembali ke Knowledge Base
        </VBtn>
      </Link>

      <div class="d-flex align-center ga-2 flex-wrap">
        <VBtn
          v-if="canSubmitReview && (article.status === 'draft' || article.status === 'rejected')"
          color="warning"
          variant="elevated"
          prepend-icon="mdi-send-check"
          class="font-weight-bold"
          @click="submitForReview"
        >
          Ajukan ke SME
        </VBtn>

        <VBtn
          v-if="canEdit"
          color="primary"
          variant="tonal"
          prepend-icon="mdi-pencil"
          :href="route('articles.edit', article.id)"
        >
          Revisi Versi Baru
        </VBtn>

        <VBtn
          v-if="canDelete"
          color="error"
          variant="text"
          prepend-icon="mdi-delete"
          @click="deleteArticle"
        >
          Hapus
        </VBtn>
      </div>
    </div>

    <!-- Governance Status Banner -->
    <div class="mb-6">
      <!-- 1. Pending Review Alert & SME Action Panel -->
      <VCard
        v-if="article.status === 'pending_review'"
        flat
        border
        class="pa-5 border-warning bg-warning-subtle"
      >
        <div class="d-flex align-start ga-3 mb-3">
          <VIcon icon="mdi-clipboard-text-clock" color="warning" size="28" class="mt-1" />
          <div>
            <h6 class="text-subtitle-1 font-weight-bold text-warning-dark mb-1">
              Dokumen Sedang Menunggu Peninjauan oleh Subject Matter Expert (SME)
            </h6>
            <p class="text-body-2 text-medium-emphasis mb-0">
              Dokumen ini belum resmi dipublikasikan untuk publik organisasi hingga disetujui oleh pakar/instruktur berwenang.
            </p>
          </div>
        </div>

        <!-- SME Review Actions Form (If user is authorized SME) -->
        <div v-if="canReview" class="mt-4 pt-3 border-t">
          <p class="text-subtitle-2 font-weight-bold mb-2">Panel Validasi Pakar (SME Reviewer):</p>
          <VTextarea
            v-model="reviewNotes"
            label="Catatan Evaluasi / Umpan Balik (Wajib diisi jika meminta revisi)"
            placeholder="Ketik catatan validasi atau poin koreksi untuk penulis di sini..."
            variant="outlined"
            density="comfortable"
            rows="2"
            class="mb-3"
          />

          <div class="d-flex align-center ga-2 flex-wrap">
            <VBtn
              color="success"
              variant="elevated"
              prepend-icon="mdi-check-decagram"
              class="font-weight-bold"
              :loading="isReviewing"
              @click="approveDocument"
            >
              Setujui & Publikasikan (Approve)
            </VBtn>

            <VBtn
              color="error"
              variant="tonal"
              prepend-icon="mdi-alert-circle"
              class="font-weight-bold"
              :loading="isReviewing"
              @click="rejectDocument"
            >
              Minta Perbaikan (Reject/Needs Revision)
            </VBtn>
          </div>
        </div>
      </VCard>

      <!-- 2. Needs Revision / Rejected Alert -->
      <VAlert
        v-else-if="article.status === 'rejected'"
        type="error"
        variant="tonal"
        class="border-error"
        prepend-icon="mdi-alert-circle"
      >
        <h6 class="text-subtitle-1 font-weight-bold mb-1">Dokumen Memerlukan Revisi dari Penulis</h6>
        <p class="text-body-2 mb-2">
          Subject Matter Expert telah meninjau dokumen ini dan memberikan catatan berikut:
        </p>
        <div class="pa-3 rounded bg-surface border text-body-2 font-weight-medium mb-3">
          "{{ article.review_notes || 'Silakan perbaiki isi dokumen sesuai standar operasional.' }}"
        </div>
        <div v-if="canEdit" class="d-flex ga-2">
          <VBtn
            color="primary"
            variant="elevated"
            size="small"
            prepend-icon="mdi-pencil"
            :href="route('articles.edit', article.id)"
          >
            Lakukan Revisi Sekarang
          </VBtn>
          <VBtn
            v-if="canSubmitReview"
            color="warning"
            variant="tonal"
            size="small"
            prepend-icon="mdi-send-check"
            @click="submitForReview"
          >
            Ajukan Ulang Peninjauan
          </VBtn>
        </div>
      </VAlert>

      <!-- 3. Draft Alert -->
      <VAlert
        v-else-if="article.status === 'draft'"
        type="info"
        variant="tonal"
        prepend-icon="mdi-file-edit-outline"
      >
        <div class="d-flex align-center justify-space-between flex-wrap ga-2">
          <div>
            <strong>Status: Draft Pribadi</strong> — Dokumen ini hanya dapat dilihat oleh Anda dan administrator.
          </div>
          <VBtn
            v-if="canSubmitReview"
            color="warning"
            variant="elevated"
            size="small"
            prepend-icon="mdi-send-check"
            class="font-weight-bold"
            @click="submitForReview"
          >
            Ajukan ke SME Sekarang
          </VBtn>
        </div>
      </VAlert>
    </div>

    <VRow>
      <!-- Main Article Column -->
      <VCol cols="12" lg="8">
        <VCard flat border class="pa-6 pa-md-8 mb-6">
          <!-- Category, Version & Status Chips -->
          <div class="d-flex align-center ga-2 mb-3 flex-wrap">
            <VChip
              v-if="article.category"
              color="primary"
              size="small"
              variant="tonal"
              class="font-weight-semibold"
            >
              {{ article.category.name }}
            </VChip>

            <!-- Version Badge -->
            <VChip
              color="default"
              size="small"
              variant="flat"
              class="font-weight-bold"
            >
              Versi {{ displayVersion }}
            </VChip>

            <VChip
              v-if="article.status === 'published'"
              color="success"
              size="small"
              variant="elevated"
              prepend-icon="mdi-shield-check"
              class="font-weight-bold"
            >
              Resmi / Terverifikasi SME
            </VChip>

            <VChip
              v-else-if="article.status === 'pending_review'"
              color="warning"
              size="small"
              variant="elevated"
            >
              Menunggu Review
            </VChip>

            <VChip
              v-else-if="article.status === 'rejected'"
              color="error"
              size="small"
              variant="elevated"
            >
              Perlu Revisi
            </VChip>

            <VChip
              v-else-if="article.status === 'draft'"
              color="secondary"
              size="small"
              variant="tonal"
            >
              Draft
            </VChip>
          </div>

          <!-- Title -->
          <h1 class="text-h4 font-weight-bold mb-4 line-height-tight">
            {{ displayTitle }}
          </h1>

          <!-- Author & SME Validation Metadata bar -->
          <div class="d-flex align-center justify-space-between flex-wrap ga-4 py-3 border-y mb-6">
            <div class="d-flex align-center ga-3">
              <VAvatar size="40" color="primary" variant="tonal">
                <VImg v-if="article.author?.avatar_url" :src="article.author.avatar_url" />
                <span v-else class="text-uppercase font-weight-bold">
                  {{ article.author?.name?.[0] }}
                </span>
              </VAvatar>
              <div>
                <p class="font-weight-bold mb-0 text-body-2">{{ article.author?.name }}</p>
                <span class="text-caption text-medium-emphasis">
                  Dibuat {{ formatDate(article.published_at || article.created_at) }}
                </span>
              </div>
            </div>

            <!-- SME Reviewer Stamp if available -->
            <div v-if="article.reviewer" class="d-flex align-center ga-2 text-caption pa-2 px-3 rounded bg-success-subtle border-success-subtle">
              <VIcon icon="mdi-shield-check" color="success" size="18" />
              <div>
                <span class="font-weight-bold text-success d-block">Divalidasi oleh SME:</span>
                <span class="text-high-emphasis">{{ article.reviewer.name }} ({{ formatDate(article.reviewed_at) }})</span>
              </div>
            </div>

            <div class="d-flex align-center ga-4 text-caption text-medium-emphasis">
              <span class="d-flex align-center ga-1">
                <VIcon icon="mdi-clock-outline" size="16" />
                {{ article.reading_time || 2 }} menit baca
              </span>
              <span class="d-flex align-center ga-1">
                <VIcon icon="mdi-eye-outline" size="16" />
                {{ article.views_count || 1 }} dilihat
              </span>
            </div>
          </div>

          <!-- Excerpt summary box if exists -->
          <div v-if="displayExcerpt" class="summary-box pa-4 rounded-lg mb-6 bg-light-primary border-l-4">
            <p class="text-body-1 font-weight-medium mb-0 text-medium-emphasis font-italic">
              "{{ displayExcerpt }}"
            </p>
          </div>

          <!-- Rich Text Article Body Content -->
          <div class="article-content-body py-2" v-html="displayContent" />

          <!-- Tags at bottom of article -->
          <div v-if="article.tags && article.tags.length > 0" class="mt-8 pt-4 border-t d-flex align-center flex-wrap ga-2">
            <span class="text-caption font-weight-bold text-medium-emphasis me-1">Topik Terkait:</span>
            <VChip
              v-for="tag in article.tags"
              :key="tag.id"
              size="small"
              variant="outlined"
            >
              #{{ tag.name }}
            </VChip>
          </div>
        </VCard>

        <!-- Attachments Section -->
        <VCard flat border class="pa-5 mb-5 rounded-xl">
          <div class="d-flex align-center ga-2 mb-3">
            <VIcon icon="mdi-paperclip" size="22" color="primary" />
            <h6 class="text-subtitle-1 font-weight-bold mb-0">
              Dokumen & File Lampiran Resmi ({{ article.attachments?.length || 0 }})
            </h6>
          </div>

          <div v-if="article.attachments && article.attachments.length > 0" class="d-flex flex-column ga-3">
            <VCard
              v-for="att in article.attachments"
              :key="att.id"
              variant="outlined"
              class="pa-4 d-flex align-center justify-space-between flex-wrap ga-3 attachment-card"
            >
              <div class="d-flex align-center ga-3 text-truncate">
                <VAvatar size="44" :color="getFileIcon(att.file_type).color" variant="tonal">
                  <VIcon :icon="getFileIcon(att.file_type).icon" size="24" />
                </VAvatar>
                <div class="text-truncate">
                  <h6 class="text-subtitle-1 font-weight-bold mb-0 text-truncate">
                    {{ att.title }}.{{ att.file_type }}
                  </h6>
                  <span class="text-caption text-medium-emphasis">
                    {{ formatBytes(att.file_size) }} • {{ att.downloads_count }} kali diunduh
                  </span>
                </div>
              </div>

              <VBtn
                color="primary"
                variant="elevated"
                size="small"
                prepend-icon="mdi-download"
                :href="route('articles.attachments.download', att.id)"
                target="_blank"
              >
                Unduh Berkas
              </VBtn>
            </VCard>
          </div>

          <div v-else class="text-caption text-medium-emphasis text-center py-4">
            Tidak ada dokumen file yang dilampirkan pada artikel ini.
          </div>
        </VCard>
      </VCol>

      <!-- Sidebar Column (Version History & Info) -->
      <VCol cols="12" lg="4">
        <!-- Version Control History Card -->
        <VCard flat border class="pa-5 mb-5">
          <div class="d-flex align-center justify-space-between mb-3">
            <div class="d-flex align-center ga-2">
              <VIcon icon="mdi-history" color="primary" size="22" />
              <h6 class="text-h6 font-weight-bold mb-0">Riwayat Versi (Changelog)</h6>
            </div>
            <VChip size="x-small" variant="tonal" color="primary">
              {{ article.versions?.length || 1 }} Versi
            </VChip>
          </div>
          <p class="text-caption text-medium-emphasis mb-3">
            Daftar snapshot riwayat perubahan isi dokumen ini.
          </p>

          <div v-if="article.versions && article.versions.length > 0" class="d-flex flex-column ga-2">
            <VCard
              v-for="ver in article.versions"
              :key="ver.id"
              variant="outlined"
              :class="{
                'bg-light-primary border-primary': viewingVersion ? viewingVersion.id === ver.id : (!viewingVersion && ver.version === article.version),
              }"
              class="pa-3 text-caption version-card transition-all"
            >
              <div class="d-flex align-center justify-space-between mb-1">
                <span class="font-weight-bold text-subtitle-2 text-primary">
                  Versi {{ ver.version }}
                </span>
                <span class="text-disabled text-caption">
                  {{ formatDate(ver.created_at) }}
                </span>
              </div>

              <p class="mb-2 text-medium-emphasis text-caption">
                {{ ver.change_summary || 'Revisi konten dokumen' }}
              </p>

              <div class="d-flex align-center justify-space-between border-t pt-2 mt-1">
                <span class="text-disabled">Oleh: {{ ver.creator?.name || 'Kontributor' }}</span>

                <div class="d-flex ga-1">
                  <!-- View Snapshot -->
                  <Link
                    v-if="!viewingVersion || viewingVersion.id !== ver.id"
                    :href="route('articles.show', { article: article.id, version_id: ver.id })"
                    class="text-decoration-none"
                  >
                    <VBtn variant="text" size="x-small" color="primary">
                      Lihat Snapshot
                    </VBtn>
                  </Link>

                  <VChip v-else size="x-small" color="primary" variant="flat">
                    Sedang Dilihat
                  </VChip>
                </div>
              </div>
            </VCard>
          </div>
        </VCard>

        <!-- Quick Meta Card -->
        <VCard flat border class="pa-5 mb-5">
          <h6 class="text-h6 font-weight-bold mb-3">Tata Kelola Dokumen</h6>
          <div class="d-flex flex-column ga-3 text-body-2">
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Versi Aktif:</span>
              <span class="font-weight-bold text-primary">v{{ article.version || '1.0' }}</span>
            </div>
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Status:</span>
              <span class="font-weight-semibold text-uppercase text-caption">{{ article.status }}</span>
            </div>
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Kategori:</span>
              <span class="font-weight-medium">{{ article.category?.name || 'Umum' }}</span>
            </div>
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">SME Verifikator:</span>
              <span class="font-weight-medium">{{ article.reviewer?.name || 'Belum diverifikasi' }}</span>
            </div>
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Terakhir Disetujui:</span>
              <span class="font-weight-medium">{{ formatDate(article.reviewed_at) }}</span>
            </div>
          </div>
        </VCard>

        <!-- Related Articles -->
        <VCard v-if="relatedArticles && relatedArticles.length > 0" flat border class="pa-5">
          <h6 class="text-h6 font-weight-bold mb-4">Dokumen Terkait</h6>
          <div class="d-flex flex-column ga-3">
            <div
              v-for="rel in relatedArticles"
              :key="rel.id"
              class="pb-3 border-b last:border-b-0"
            >
              <Link
                :href="route('articles.show', rel.id)"
                class="text-decoration-none text-high-emphasis font-weight-semibold text-body-2 hover-primary d-block mb-1"
              >
                {{ rel.title }}
              </Link>
              <div class="d-flex align-center ga-3 text-caption text-disabled">
                <span>{{ formatDate(rel.created_at) }}</span>
                <span>•</span>
                <span>{{ rel.views_count || 0 }} views</span>
              </div>
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.border-l-4 {
  border-left: 4px solid rgb(var(--v-theme-primary)) !important;
}

.bg-light-primary {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.bg-warning-subtle {
  background-color: rgba(245, 158, 11, 0.06);
}

.bg-success-subtle {
  background-color: rgba(16, 185, 129, 0.08);
}

.border-success-subtle {
  border: 1px solid rgba(16, 185, 129, 0.25);
}

.text-warning-dark {
  color: #b45309;
}

.line-height-tight {
  line-height: 1.3 !important;
}

.attachment-card {
  transition: all 0.2s ease;
}

.attachment-card:hover {
  background-color: rgba(var(--v-theme-primary), 0.02);
}

.version-card:hover {
  border-color: rgb(var(--v-theme-primary)) !important;
}

.hover-primary:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

/* Rich Text Content Styling */
:deep(.article-content-body) {
  font-size: 1rem;
  line-height: 1.75;
  color: rgba(var(--v-theme-on-surface), 0.87);
}

:deep(.article-content-body h1),
:deep(.article-content-body h2),
:deep(.article-content-body h3),
:deep(.article-content-body h4) {
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  font-weight: 700;
  line-height: 1.3;
}

:deep(.article-content-body p) {
  margin-bottom: 1.25rem;
}

:deep(.article-content-body ul),
:deep(.article-content-body ol) {
  margin-bottom: 1.25rem;
  padding-left: 1.5rem;
}

:deep(.article-content-body li) {
  margin-bottom: 0.35rem;
}

:deep(.article-content-body blockquote) {
  border-left: 4px solid rgb(var(--v-theme-primary));
  padding-left: 1rem;
  margin-left: 0;
  margin-right: 0;
  font-style: italic;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

:deep(.article-content-body table) {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 1.5rem;
}

:deep(.article-content-body th),
:deep(.article-content-body td) {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  padding: 0.75rem;
  text-align: left;
}

:deep(.article-content-body th) {
  background-color: rgba(var(--v-theme-surface-variant), 0.3);
  font-weight: 600;
}

:deep(.article-content-body img) {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 1rem 0;
}
</style>
