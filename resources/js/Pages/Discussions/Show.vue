<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  discussion: { type: Object, required: true },
  course: { type: Object, default: null },
  canVerifySolution: { type: Boolean, default: false },
  canPin: { type: Boolean, default: false },
  canEdit: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
})

const page = usePage()
const replyForm = useForm({ content: '' })

const submitReply = () => {
  replyForm.post(route('discussions.replies.store', props.discussion.id), {
    preserveScroll: true,
    onSuccess: () => replyForm.reset(),
  })
}

const togglePin = () => {
  router.post(route('discussions.pin', props.discussion.id), {}, { preserveScroll: true })
}

const toggleSolution = (replyId) => {
  router.post(route('discussions.replies.solution', [props.discussion.id, replyId]), {}, {
    preserveScroll: true,
  })
}

const deleteDiscussion = () => {
  if (confirm('Apakah Anda yakin ingin menghapus diskusi ini?')) {
    router.delete(route('discussions.destroy', props.discussion.id))
  }
}

const deleteReply = (replyId) => {
  if (confirm('Hapus jawaban ini?')) {
    router.delete(route('discussions.replies.destroy', [props.discussion.id, replyId]), {
      preserveScroll: true,
    })
  }
}

const currentUserId = computed(() => page.props.auth?.user?.id)
const isLocked = computed(() => props.discussion.is_locked)
</script>

<template>
  <div class="discussion-show-page">
    <!-- Top Action Bar -->
    <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
      <Link
        :href="route('discussions.index', { course_id: course?.id })"
        class="text-decoration-none"
      >
        <VBtn variant="tonal" prepend-icon="mdi-arrow-left">
          Kembali ke Daftar Diskusi
        </VBtn>
      </Link>

      <div class="d-flex align-center ga-2">
        <VBtn
          v-if="canPin"
          :color="discussion.is_pinned ? 'primary' : 'default'"
          variant="tonal"
          size="small"
          :prepend-icon="discussion.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'"
          @click="togglePin"
        >
          {{ discussion.is_pinned ? 'Sematkan Aktif' : 'Sematkan' }}
        </VBtn>

        <VBtn
          v-if="canEdit"
          variant="tonal"
          size="small"
          prepend-icon="mdi-pencil"
          :href="route('discussions.edit', discussion.id)"
        >
          Edit
        </VBtn>

        <VBtn
          v-if="canDelete"
          color="error"
          variant="text"
          size="small"
          prepend-icon="mdi-delete"
          @click="deleteDiscussion"
        >
          Hapus
        </VBtn>
      </div>
    </div>

    <!-- Resolved Notice Banner if resolved -->
    <VAlert
      v-if="discussion.is_resolved"
      type="success"
      variant="tonal"
      class="mb-4 font-weight-medium border-success"
      prepend-icon="mdi-check-decagram"
    >
      <div class="d-flex align-center justify-space-between flex-wrap ga-2">
        <div>
          <strong>Pertanyaan Ini Telah Memiliki Solusi Terverifikasi!</strong>
          <span class="d-block text-caption text-medium-emphasis">
            Lihat jawaban dengan tanda centang hijau di bawah yang telah dikonfirmasi menyelesaikan kendala ini.
          </span>
        </div>
      </div>
    </VAlert>

    <!-- Main Discussion / Question Card -->
    <VCard flat border class="pa-6 pa-md-8 mb-6">
      <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
        <div class="d-flex align-center ga-3">
          <VAvatar
            size="44"
            :color="discussion.creator?.avatar_url ? undefined : 'primary'"
            variant="tonal"
          >
            <VImg
              v-if="discussion.creator?.avatar_url"
              :src="discussion.creator.avatar_url"
            />
            <span v-else class="text-uppercase font-weight-bold">
              {{ discussion.creator?.name?.[0] }}
            </span>
          </VAvatar>

          <div>
            <div class="font-weight-bold text-subtitle-1">
              {{ discussion.creator?.name }}
            </div>
            <div class="text-caption text-medium-emphasis">
              Ditanyakan pada {{ new Date(discussion.created_at).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' }) }}
            </div>
          </div>
        </div>

        <div class="d-flex align-center ga-2 flex-wrap">
          <VChip
            v-if="discussion.is_resolved"
            color="success"
            size="small"
            variant="elevated"
            prepend-icon="mdi-check-decagram"
            class="font-weight-bold"
          >
            Solusi Terverifikasi
          </VChip>

          <VChip
            v-if="!discussion.course_id"
            color="teal"
            size="small"
            variant="tonal"
            prepend-icon="mdi-earth"
          >
            Q&A Umum Organisasi
          </VChip>

          <VChip
            v-else
            color="primary"
            size="small"
            variant="tonal"
            prepend-icon="mdi-book-open-outline"
          >
            Kursus: {{ discussion.course?.title }}
          </VChip>

          <VChip
            v-if="discussion.category"
            size="small"
            variant="outlined"
          >
            {{ discussion.category.name }}
          </VChip>

          <VChip
            v-if="discussion.is_pinned"
            color="primary"
            size="small"
            variant="flat"
            prepend-icon="mdi-pin"
          >
            Disematkan
          </VChip>
        </div>
      </div>

      <!-- Question Title -->
      <h3 class="text-h4 font-weight-bold mb-4">
        {{ discussion.title }}
      </h3>

      <!-- Question Body Content -->
      <div
        class="text-body-1 question-body pa-1 rich-content"
        style="line-height: 1.7;"
        v-html="discussion.content"
      />
    </VCard>

    <!-- Replies / Answers Section -->
    <div class="d-flex align-center justify-space-between mb-4">
      <h5 class="text-h5 font-weight-bold mb-0">
        Jawaban & Solusi ({{ discussion.replies?.length ?? 0 }})
      </h5>
    </div>

    <!-- Replies List -->
    <div v-if="discussion.replies?.length" class="d-flex flex-column ga-4 mb-6">
      <VCard
        v-for="reply in discussion.replies"
        :key="reply.id"
        flat
        border
        :class="{
          'solution-card border-success border-2': reply.is_solution,
        }"
        class="pa-5 transition-all"
      >
        <!-- Solution Banner Header on the card -->
        <div
          v-if="reply.is_solution"
          class="d-flex align-center justify-space-between pa-2 px-3 mb-4 rounded bg-success-light text-success font-weight-bold text-caption border border-success"
        >
          <div class="d-flex align-center ga-2">
            <VIcon icon="mdi-check-decagram" size="20" color="success" />
            <span>JAWABAN TERVERIFIKASI SEBAGAI SOLUSI</span>
            <span v-if="reply.verified_by" class="font-weight-regular text-medium-emphasis">
              • Dikonfirmasi oleh <strong>{{ reply.verified_by?.name }}</strong>
            </span>
          </div>
          <span class="text-caption">
            {{ new Date(reply.verified_at).toLocaleDateString('id-ID') }}
          </span>
        </div>

        <div class="d-flex align-start ga-4">
          <!-- Replier Avatar -->
          <VAvatar
            size="40"
            :color="reply.creator?.avatar_url ? undefined : 'secondary'"
            variant="tonal"
            class="flex-shrink-0 mt-1"
          >
            <VImg
              v-if="reply.creator?.avatar_url"
              :src="reply.creator.avatar_url"
            />
            <span v-else class="text-uppercase font-weight-bold">
              {{ reply.creator?.name?.[0] }}
            </span>
          </VAvatar>

          <!-- Reply Body -->
          <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex align-center justify-space-between flex-wrap ga-2 mb-2">
              <div class="d-flex align-center ga-2 flex-wrap">
                <span class="font-weight-bold text-body-1">
                  {{ reply.creator?.name }}
                </span>
                <VChip
                  v-if="reply.creator?.id === discussion.created_by"
                  size="x-small"
                  color="info"
                  variant="tonal"
                >
                  Penanya
                </VChip>
                <span class="text-caption text-medium-emphasis">
                  {{ new Date(reply.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}
                </span>
              </div>

              <!-- Action buttons for this reply -->
              <div class="d-flex align-center ga-1">
                <!-- Verify as Solution Button -->
                <VBtn
                  v-if="canVerifySolution"
                  :color="reply.is_solution ? 'warning' : 'success'"
                  :variant="reply.is_solution ? 'tonal' : 'elevated'"
                  size="small"
                  :prepend-icon="reply.is_solution ? 'mdi-close-circle-outline' : 'mdi-check-circle'"
                  class="font-weight-semibold"
                  @click="toggleSolution(reply.id)"
                >
                  {{ reply.is_solution ? 'Batal Solusi' : 'Tandai Solusi Terverifikasi' }}
                </VBtn>

                <!-- Delete Reply (owner or admin) -->
                <VBtn
                  v-if="currentUserId === reply.created_by || page.props.auth?.user?.roles?.includes('tenant_admin')"
                  icon="mdi-delete"
                  variant="text"
                  color="error"
                  size="small"
                  density="compact"
                  title="Hapus Jawaban"
                  @click="deleteReply(reply.id)"
                />
              </div>
            </div>

            <!-- Content -->
            <div
              class="text-body-1"
              style="white-space: pre-wrap; line-height: 1.6;"
            >
              {{ reply.content }}
            </div>
          </div>
        </div>
      </VCard>
    </div>

    <!-- Empty Replies -->
    <VCard
      v-else
      flat
      border
      class="pa-10 text-center mb-6"
    >
      <VIcon icon="mdi-message-text-outline" size="40" color="disabled" class="mb-2" />
      <p class="text-body-1 font-weight-medium mb-1">Belum Ada Jawaban</p>
      <p class="text-caption text-medium-emphasis mb-0">
        Punya informasi, pengalaman lapangan, atau solusi untuk pertanyaan ini? Bagikan jawaban Anda di bawah!
      </p>
    </VCard>

    <!-- Post Reply Form (Open to all members) -->
    <VCard
      v-if="!isLocked"
      flat
      border
      class="pa-6"
    >
      <div class="d-flex align-center ga-2 mb-3">
        <VIcon icon="mdi-message-plus-outline" size="24" color="primary" />
        <h5 class="text-h6 font-weight-bold mb-0">
          Tuliskan Jawaban / Solusi Anda
        </h5>
      </div>

      <VForm @submit.prevent="submitReply">
        <VTextarea
          v-model="replyForm.content"
          variant="outlined"
          density="comfortable"
          placeholder="Tuliskan penjelasan, rujukan SOP, atau solusi langkah kerja di sini..."
          rows="4"
          :rules="[v => !!v || 'Jawaban tidak boleh kosong']"
          hide-details="auto"
          class="mb-3"
        />

        <div class="d-flex justify-end">
          <VBtn
            type="submit"
            color="primary"
            variant="elevated"
            size="large"
            prepend-icon="mdi-send"
            :loading="replyForm.processing"
            :disabled="replyForm.processing || !replyForm.content"
          >
            Kirim Jawaban
          </VBtn>
        </div>
      </VForm>
    </VCard>
  </div>
</template>

<style scoped>
.solution-card {
  background-color: rgba(16, 185, 129, 0.04) !important;
}

.bg-success-light {
  background-color: rgba(16, 185, 129, 0.12);
}

.border-2 {
  border-width: 2px !important;
}

.border-success {
  border-color: #10b981 !important;
}
</style>
