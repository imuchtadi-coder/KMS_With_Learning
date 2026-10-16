<script setup>
import { computed, reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  course: { type: Object, required: true },
  isEnrolled: { type: Boolean, default: false },
  enrollmentId: { type: Number, default: null },
  userReview: { type: Object, default: null },
})

const enrolling = ref(false)
const deletingReview = ref(false)
const expandedPanels = ref([0]) // Default open first section
const showSnackbar = ref(false)
const snackbarMessage = ref('')
const isBookmarked = ref(false)

const totalLectures = computed(() => {
  return props.course.content?.reduce((sum, s) => sum + (s.topics?.length ?? 0), 0) ?? props.course.totalLectures ?? 0
})

const outcomes = computed(() => {
  if (!props.course.learningOutcomes) return []
  return String(props.course.learningOutcomes)
    .split('\n')
    .map(s => s.trim())
    .filter(Boolean)
})

const avgRating = computed(() => {
  return Number(props.course.rating ?? 0).toFixed(1)
})

const reviewForm = reactive({
  rating: props.userReview?.rating ?? 5,
  review: props.userReview?.review ?? '',
  processing: false,
})

function submitReview() {
  if (!reviewForm.rating) {
    snackbarMessage.value = 'Silakan pilih bintang penilaian.'
    showSnackbar.value = true
    return
  }
  reviewForm.processing = true
  if (props.userReview) {
    router.put(route('student.reviews.update', [props.course.id, props.userReview.id]), {
      rating: reviewForm.rating,
      review: reviewForm.review,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        snackbarMessage.value = 'Ulasan berhasil diperbarui!'
        showSnackbar.value = true
      },
      onFinish: () => { reviewForm.processing = false },
    })
  } else {
    router.post(route('student.reviews.store', props.course.id), {
      rating: reviewForm.rating,
      review: reviewForm.review,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        snackbarMessage.value = 'Ulasan berhasil dikirim!'
        showSnackbar.value = true
      },
      onFinish: () => { reviewForm.processing = false },
    })
  }
}

function deleteReview() {
  if (!confirm('Hapus ulasan Anda untuk kursus ini?')) return
  deletingReview.value = true
  router.delete(route('student.reviews.destroy', [props.course.id, props.userReview.id]), {
    preserveScroll: true,
    onSuccess: () => {
      snackbarMessage.value = 'Ulasan telah dihapus.'
      showSnackbar.value = true
    },
    onFinish: () => { deletingReview.value = false },
  })
}

function enroll(courseId) {
  enrolling.value = true
  router.post(route('student.courses.enroll', courseId), {}, {
    preserveScroll: true,
    onFinish: () => { enrolling.value = false },
  })
}

function formatPrice(price) {
  return Number(price).toLocaleString('id-ID')
}

function copyCourseLink() {
  if (navigator?.clipboard) {
    navigator.clipboard.writeText(window.location.href)
    snackbarMessage.value = 'Tautan kursus berhasil disalin ke clipboard!'
    showSnackbar.value = true
  }
}

function toggleBookmark() {
  isBookmarked.value = !isBookmarked.value
  snackbarMessage.value = isBookmarked.value ? 'Kursus disimpan ke bookmark!' : 'Kursus dihapus dari bookmark.'
  showSnackbar.value = true
}

function toggleAllPanels() {
  if (expandedPanels.value.length === props.course.content?.length) {
    expandedPanels.value = []
  } else {
    expandedPanels.value = props.course.content?.map((_, i) => i) || []
  }
}

function formatDate(date) {
  if (!date) return ''
  try {
    return new Date(date).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    })
  } catch (e) {
    return date
  }
}
</script>

<template>
  <div class="course-detail-page">
    <!-- Back Navigation -->
    <div class="mb-4 d-flex align-center justify-space-between flex-wrap ga-2">
      <VBtn
        variant="text"
        color="primary"
        size="small"
        class="font-weight-medium px-2"
        :href="route('student.courses.list')"
      >
        <VIcon start icon="mdi-arrow-left" size="18" />
        Kembali ke Daftar Kursus
      </VBtn>

      <div class="d-flex align-center ga-2">
        <VBtn
          variant="tonal"
          color="default"
          size="small"
          prepend-icon="mdi-share-variant-outline"
          class="font-weight-medium"
          @click="copyCourseLink"
        >
          Bagikan
        </VBtn>
        <VBtn
          variant="tonal"
          :color="isBookmarked ? 'warning' : 'default'"
          size="small"
          :prepend-icon="isBookmarked ? 'mdi-bookmark' : 'mdi-bookmark-outline'"
          class="font-weight-medium"
          @click="toggleBookmark"
        >
          {{ isBookmarked ? 'Tersimpan' : 'Simpan' }}
        </VBtn>
      </div>
    </div>

    <!-- Hero Header Banner -->
    <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
      <div class="pa-6 pa-md-8">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start ga-6">
          <div class="flex-grow-1">
            <!-- Badges Row -->
            <div class="d-flex align-center ga-2 flex-wrap mb-3">
              <VChip
                size="small"
                variant="tonal"
                color="primary"
                label
                class="font-weight-bold"
              >
                {{ course.category || course.tags || 'Umum' }}
              </VChip>
              <VChip
                size="small"
                variant="outlined"
                color="default"
                label
              >
                <VIcon start icon="mdi-signal" size="14" />
                {{ course.skillLevel || 'Semua Level' }}
              </VChip>
              <VChip
                v-if="course.language"
                size="small"
                variant="outlined"
                color="default"
                label
              >
                <VIcon start icon="mdi-translate" size="14" />
                {{ course.language }}
              </VChip>
            </div>

            <!-- Course Title -->
            <h3 class="text-h4 font-weight-bold text-high-emphasis mb-3">
              {{ course.courseTitle }}
            </h3>

            <!-- Short Subtitle / About -->
            <p
              v-if="course.about || course.desc"
              class="text-body-1 text-medium-emphasis mb-4 line-clamp-3"
              style="max-width: 860px; line-height: 1.6;"
            >
              {{ course.about || course.desc }}
            </p>

            <!-- Metadata Counters Row -->
            <div class="d-flex align-center ga-5 flex-wrap text-caption text-medium-emphasis pt-1">
              <!-- Rating -->
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-star" color="warning" size="18" />
                <span class="font-weight-bold text-high-emphasis text-body-2">{{ avgRating }}</span>
                <span>({{ course.ratingCount }} ulasan)</span>
              </div>

              <VDivider vertical style="height: 16px;" />

              <!-- Enrolled Students -->
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-account-group-outline" size="18" />
                <span>{{ course.totalStudents }} siswa terdaftar</span>
              </div>

              <VDivider vertical style="height: 16px;" />

              <!-- Total Lessons -->
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-play-box-multiple-outline" size="18" />
                <span>{{ totalLectures }} materi pembelajaran</span>
              </div>

              <VDivider vertical style="height: 16px;" />

              <!-- Total Length -->
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-clock-outline" size="18" />
                <span>{{ course.length || 'Fleksibel' }}</span>
              </div>
            </div>

            <!-- Instructor Snapshot in Hero -->
            <div class="d-flex align-center ga-3 mt-5 pt-3 border-t">
              <VAvatar :image="course.instructorAvatar" size="40" rounded="circle" class="border" />
              <div>
                <div class="text-caption text-medium-emphasis">Instruktur Kursus</div>
                <div class="text-subtitle-2 font-weight-bold text-high-emphasis">
                  {{ course.instructor }}
                  <span class="text-caption text-medium-emphasis font-weight-normal ms-1">
                    • {{ course.instructorPosition }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </VCard>

    <!-- Main Content Layout -->
    <VRow>
      <!-- Left Column: Details, Syllabus, Outcomes, Reviews -->
      <VCol cols="12" md="8">
        <!-- Media / Preview Card -->
        <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
          <div class="course-preview-media position-relative bg-grey-darken-4">
            <template v-if="course.thumbnail">
              <img
                :src="course.thumbnail"
                :alt="course.courseTitle"
                class="w-100 h-100 object-cover"
                style="max-height: 420px; object-fit: cover;"
              />
              <div class="media-overlay d-flex align-center justify-center">
                <div class="text-center pa-4">
                  <VAvatar color="primary" size="64" class="elevation-3 mb-2 cursor-pointer">
                    <VIcon icon="mdi-play" size="36" color="white" />
                  </VAvatar>
                  <div class="text-white text-subtitle-2 font-weight-medium text-shadow">
                    Pratinjau Materi Kursus
                  </div>
                </div>
              </div>
            </template>
            <template v-else>
              <div
                class="d-flex flex-column align-center justify-center pa-8 text-center"
                style="min-height: 280px; background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.15) 0%, rgba(var(--v-theme-surface), 0.95) 100%);"
              >
                <VAvatar color="primary" variant="tonal" size="64" class="mb-3">
                  <VIcon icon="mdi-school-outline" size="36" color="primary" />
                </VAvatar>
                <h6 class="text-h6 font-weight-bold mb-1">{{ course.courseTitle }}</h6>
                <p class="text-caption text-medium-emphasis mb-0">Materi lengkap tersedia di dalam kurikulum interaktif</p>
              </div>
            </template>
          </div>
        </VCard>

        <!-- What You Will Learn (Learning Outcomes) -->
        <VCard
          v-if="outcomes.length > 0"
          flat
          border
          class="mb-6 rounded-xl bg-surface"
        >
          <div class="pa-6">
            <div class="d-flex align-center ga-3 mb-4">
              <VAvatar color="success" variant="tonal" size="36" rounded="lg">
                <VIcon icon="mdi-target" size="20" color="success" />
              </VAvatar>
              <h5 class="text-h6 font-weight-bold mb-0">Target Pembelajaran & Keahlian</h5>
            </div>

            <VRow>
              <VCol
                v-for="(item, idx) in outcomes"
                :key="idx"
                cols="12"
                sm="6"
                class="py-2"
              >
                <div class="d-flex align-start ga-3">
                  <VIcon icon="mdi-check-circle" color="success" size="20" class="mt-1 flex-shrink-0" />
                  <span class="text-body-2 text-high-emphasis" style="line-height: 1.5;">{{ item }}</span>
                </div>
              </VCol>
            </VRow>
          </div>
        </VCard>

        <!-- Course Curriculum & Syllabus -->
        <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
          <div class="pa-5 border-b d-flex flex-column flex-sm-row justify-space-between align-start align-sm-center ga-3 bg-background">
            <div>
              <h5 class="text-h6 font-weight-bold mb-1">Kurikulum & Materi Kursus</h5>
              <div class="text-caption text-medium-emphasis">
                {{ course.content?.length || 0 }} Bagian • {{ totalLectures }} Pelajaran • {{ course.length || 'Fleksibel' }}
              </div>
            </div>
            <VBtn
              variant="text"
              color="primary"
              size="small"
              class="font-weight-medium px-2"
              @click="toggleAllPanels"
            >
              {{ expandedPanels.length === course.content?.length ? 'Tutup Semua' : 'Buka Semua' }}
            </VBtn>
          </div>

          <div class="pa-4 pa-md-6">
            <VExpansionPanels
              v-model="expandedPanels"
              multiple
              variant="accordion"
              class="custom-syllabus-panels"
            >
              <VExpansionPanel
                v-for="(section, sIndex) in (course.content || [])"
                :key="sIndex"
                :value="sIndex"
                elevation="0"
                class="border rounded-lg mb-3 overflow-hidden"
              >
                <VExpansionPanelTitle class="py-3 px-4 bg-background">
                  <div class="d-flex justify-space-between align-center w-100 pe-3">
                    <div class="d-flex align-center ga-3">
                      <VAvatar size="26" color="primary" variant="tonal" class="text-caption font-weight-bold">
                        {{ sIndex + 1 }}
                      </VAvatar>
                      <span class="font-weight-bold text-subtitle-2 text-high-emphasis">
                        {{ section.title }}
                      </span>
                    </div>
                    <div class="text-caption text-medium-emphasis">
                      {{ section.topics?.length || 0 }} materi • {{ section.time }}
                    </div>
                  </div>
                </VExpansionPanelTitle>

                <VExpansionPanelText class="pa-0">
                  <div class="d-flex flex-column divide-y">
                    <div
                      v-for="(topic, tIndex) in (section.topics || [])"
                      :key="tIndex"
                      class="d-flex align-center justify-space-between py-3 px-4 topic-row transition-swing"
                    >
                      <div class="d-flex align-center ga-3 flex-grow-1 pe-2">
                        <VIcon
                          :icon="topic.isCompleted ? 'mdi-check-circle' : 'mdi-play-circle-outline'"
                          :color="topic.isCompleted ? 'success' : 'primary'"
                          size="20"
                          class="flex-shrink-0"
                        />
                        <span class="text-body-2 font-weight-medium text-high-emphasis">
                          {{ topic.title }}
                        </span>
                      </div>
                      <div class="d-flex align-center ga-2 flex-shrink-0">
                        <span class="text-caption text-medium-emphasis">{{ topic.time }}</span>
                        <VIcon
                          v-if="!isEnrolled && tIndex > 0"
                          icon="mdi-lock-outline"
                          size="16"
                          color="disabled"
                        />
                      </div>
                    </div>
                  </div>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </div>
        </VCard>

        <!-- Course Description & Details -->
        <VCard flat border class="mb-6 rounded-xl bg-surface">
          <div class="pa-6">
            <h5 class="text-h6 font-weight-bold mb-4">Deskripsi Lengkap Kursus</h5>
            <div
              class="text-body-1 text-high-emphasis course-description-content"
              style="line-height: 1.7;"
              v-html="course.description"
            />
          </div>
        </VCard>

        <!-- Instructor Profile Card -->
        <VCard flat border class="mb-6 rounded-xl bg-surface">
          <div class="pa-6">
            <h5 class="text-h6 font-weight-bold mb-4">Tentang Instruktur</h5>
            <div class="d-flex flex-column flex-sm-row align-start ga-4">
              <VAvatar :image="course.instructorAvatar" size="64" rounded="circle" class="border" />
              <div>
                <h6 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1">
                  {{ course.instructor }}
                </h6>
                <div class="text-caption text-primary font-weight-medium mb-3">
                  {{ course.instructorPosition }}
                </div>
                <p class="text-body-2 text-medium-emphasis mb-0" style="line-height: 1.6;">
                  Instruktur berdedikasi dengan fokus pada pengembangan keahlian praktis, metode pembelajaran interaktif, dan standar industri terkini.
                </p>
              </div>
            </div>
          </div>
        </VCard>

        <!-- Student Reviews & Ratings -->
        <VCard flat border class="rounded-xl bg-surface">
          <div class="pa-6">
            <div class="d-flex justify-space-between align-center mb-6">
              <h5 class="text-h6 font-weight-bold mb-0">Ulasan & Penilaian Siswa</h5>
              <VChip size="small" variant="tonal" color="primary" label>
                {{ course.ratingCount }} Ulasan
              </VChip>
            </div>

            <!-- Rating Summary Card -->
            <div class="pa-5 rounded-xl bg-background border mb-6">
              <div class="d-flex flex-column flex-sm-row align-center justify-space-between ga-6">
                <div class="text-center text-sm-start d-flex align-center ga-4">
                  <div class="text-h2 font-weight-bold text-primary">{{ avgRating }}</div>
                  <div>
                    <VRating
                      :model-value="Math.round(course.rating)"
                      color="warning"
                      density="compact"
                      size="small"
                      readonly
                    />
                    <div class="text-caption text-medium-emphasis mt-1">
                      Skor dari {{ course.ratingCount }} ulasan peserta
                    </div>
                  </div>
                </div>

                <div class="text-caption text-medium-emphasis text-center text-sm-end">
                  <div class="font-weight-medium text-high-emphasis">100% Ulasan Terverifikasi</div>
                  <div>Semua ulasan berasal dari siswa yang terdaftar</div>
                </div>
              </div>
            </div>

            <!-- Write / Update Review Form (For Enrolled Students) -->
            <div v-if="isEnrolled" class="pa-5 rounded-xl bg-background border mb-6">
              <div class="d-flex align-center justify-space-between mb-3">
                <span class="text-subtitle-2 font-weight-bold text-high-emphasis">
                  {{ userReview ? 'Ubah Ulasan Anda' : 'Beri Ulasan Untuk Kursus Ini' }}
                </span>
                <span v-if="userReview" class="text-caption text-medium-emphasis">
                  Terakhir dikirim {{ formatDate(userReview.created_at) }}
                </span>
              </div>

              <div class="d-flex align-center ga-3 mb-3">
                <span class="text-caption text-medium-emphasis">Penilaian Anda:</span>
                <VRating
                  v-model="reviewForm.rating"
                  color="warning"
                  density="compact"
                  size="28"
                  hover
                />
              </div>

              <VTextarea
                v-model="reviewForm.review"
                variant="outlined"
                density="comfortable"
                placeholder="Tuliskan pengalaman atau masukan Anda mengenai materi kursus ini..."
                rows="3"
                hide-details
                class="mb-3"
              />

              <div class="d-flex justify-end ga-2">
                <VBtn
                  v-if="userReview"
                  color="error"
                  size="small"
                  variant="tonal"
                  :loading="deletingReview"
                  @click="deleteReview"
                >
                  Hapus Ulasan
                </VBtn>
                <VBtn
                  color="primary"
                  size="small"
                  variant="elevated"
                  :loading="reviewForm.processing"
                  @click="submitReview"
                >
                  {{ userReview ? 'Simpan Pembaruan' : 'Kirim Ulasan' }}
                </VBtn>
              </div>
            </div>

            <!-- List of Student Reviews -->
            <div v-if="course.reviews && course.reviews.length > 0" class="d-flex flex-column ga-4">
              <div
                v-for="rev in course.reviews"
                :key="rev.id"
                class="pa-4 rounded-lg bg-background border"
              >
                <div class="d-flex justify-space-between align-start mb-2">
                  <div class="d-flex align-center ga-3">
                    <VAvatar
                      size="36"
                      color="primary"
                      variant="tonal"
                      :image="rev.user?.avatar"
                    >
                      <span v-if="!rev.user?.avatar" class="text-caption font-weight-bold">
                        {{ (rev.user?.name || 'U').charAt(0).toUpperCase() }}
                      </span>
                    </VAvatar>
                    <div>
                      <div class="text-subtitle-2 font-weight-bold text-high-emphasis">
                        {{ rev.user?.name || 'Siswa' }}
                      </div>
                      <div class="text-caption text-medium-emphasis">
                        {{ formatDate(rev.createdAt) }}
                      </div>
                    </div>
                  </div>

                  <VRating
                    :model-value="rev.rating"
                    color="warning"
                    density="compact"
                    size="16"
                    readonly
                  />
                </div>

                <p class="text-body-2 text-high-emphasis mb-0 ps-12" style="line-height: 1.6;">
                  {{ rev.review || 'Pengguna ini hanya memberikan nilai bintang tanpa ulasan teks.' }}
                </p>
              </div>
            </div>

            <div
              v-else
              class="text-center pa-8 rounded-lg bg-background border"
            >
              <VIcon icon="mdi-comment-text-outline" size="40" color="medium-emphasis" class="mb-2" />
              <div class="text-subtitle-2 font-weight-medium mb-1">Belum Ada Ulasan</div>
              <div class="text-caption text-medium-emphasis">
                Jadilah siswa pertama yang memberikan penilaian untuk kursus ini!
              </div>
            </div>
          </div>
        </VCard>
      </VCol>

      <!-- Right Column: Sticky Enrollment Card & Quick Info -->
      <VCol cols="12" md="4">
        <div class="sticky-enrollment-card">
          <VCard flat border class="rounded-xl overflow-hidden bg-surface shadow-sm">
            <!-- Price Display Header -->
            <div class="pa-6 border-b bg-background">
              <div class="text-caption text-medium-emphasis font-weight-medium mb-1">
                Biaya Investasi Belajar
              </div>
              <div class="d-flex align-baseline ga-2">
                <span class="text-h4 font-weight-bold text-high-emphasis">
                  {{ course.price > 0 ? `Rp ${formatPrice(course.price)}` : 'Gratis' }}
                </span>
                <VChip
                  v-if="course.price <= 0"
                  size="small"
                  color="success"
                  variant="flat"
                  label
                  class="font-weight-bold"
                >
                  Akses Terbuka
                </VChip>
              </div>
            </div>

            <!-- Action Button -->
            <div class="pa-6">
              <VBtn
                v-if="isEnrolled"
                color="success"
                variant="elevated"
                size="large"
                block
                class="font-weight-bold shadow-sm mb-3"
                :href="route('student.enrollments.show', enrollmentId ?? course.id)"
              >
                <VIcon start icon="mdi-play-circle" size="20" />
                Lanjutkan Belajar
              </VBtn>

              <VBtn
                v-else
                color="primary"
                variant="elevated"
                size="large"
                block
                class="font-weight-bold shadow-sm mb-3"
                :loading="enrolling"
                @click="enroll(course.id)"
              >
                <VIcon start icon="mdi-lightning-bolt" size="20" />
                Daftar Kursus Sekarang
              </VBtn>

              <div class="text-caption text-center text-medium-emphasis mb-6">
                {{ isEnrolled ? 'Anda telah terdaftar di kursus ini' : 'Dapatkan akses langsung ke seluruh video & materi' }}
              </div>

              <VDivider class="mb-5" />

              <!-- Course Inclusions Checklist -->
              <div class="text-caption font-weight-bold text-uppercase text-medium-emphasis mb-3">
                Fasilitas Kursus:
              </div>

              <div class="d-flex flex-column ga-3 text-body-2 text-medium-emphasis">
                <div class="d-flex align-center ga-3">
                  <VIcon icon="mdi-play-box-multiple-outline" color="primary" size="20" />
                  <span class="text-high-emphasis font-weight-medium">{{ totalLectures }} Materi Pembelajaran</span>
                </div>
                <div class="d-flex align-center ga-3">
                  <VIcon icon="mdi-clock-outline" color="primary" size="20" />
                  <span>{{ course.length || 'Akses Fleksibel' }} Total Durasi</span>
                </div>
                <div class="d-flex align-center ga-3">
                  <VIcon icon="mdi-devices" color="primary" size="20" />
                  <span>Akses via Laptop, Tablet & Ponsel</span>
                </div>
                <div class="d-flex align-center ga-3">
                  <VIcon icon="mdi-certificate-outline" color="primary" size="20" />
                  <span>Sertifikat Penyelesaian Resmi</span>
                </div>
                <div class="d-flex align-center ga-3">
                  <VIcon icon="mdi-infinity" color="primary" size="20" />
                  <span>Akses Materi Seumur Hidup</span>
                </div>
              </div>
            </div>
          </VCard>
        </div>
      </VCol>
    </VRow>

    <!-- Feedback Snackbar -->
    <VSnackbar
      v-model="showSnackbar"
      location="top end"
      :timeout="3000"
      color="surface"
      elevation="4"
    >
      <div class="d-flex align-center ga-2 text-body-2 text-high-emphasis font-weight-medium">
        <VIcon icon="mdi-check-circle" color="success" size="20" />
        <span>{{ snackbarMessage }}</span>
      </div>
    </VSnackbar>
  </div>
</template>

<style scoped>
.sticky-enrollment-card {
  position: sticky;
  top: 90px;
}

.course-preview-media {
  max-height: 420px;
  overflow: hidden;
}

.media-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(2px);
  transition: background 0.2s ease;
}

.media-overlay:hover {
  background: rgba(0, 0, 0, 0.35);
}

.text-shadow {
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.7);
}

.topic-row {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.topic-row:last-child {
  border-bottom: none;
}

.topic-row:hover {
  background-color: rgba(var(--v-theme-primary), 0.04);
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.course-description-content :deep(p) {
  margin-bottom: 1rem;
}

.course-description-content :deep(h1),
.course-description-content :deep(h2),
.course-description-content :deep(h3),
.course-description-content :deep(h4) {
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  font-weight: 700;
}

.course-description-content :deep(ul),
.course-description-content :deep(ol) {
  padding-left: 1.5rem;
  margin-bottom: 1rem;
}
</style>
