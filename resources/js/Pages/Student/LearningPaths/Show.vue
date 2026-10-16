<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  learningPath: { type: Object, required: true },
  enrollment: { type: Object, default: null },
  progress: { type: Number, default: 0 },
  nextCourse: { type: Object, default: null },
  completedCourseIds: { type: Array, default: () => [] },
  isLocked: { type: Boolean, default: false },
})

const isEnrolling = ref(false)

function enroll() {
  isEnrolling.value = true
  router.post(route('student.learning-paths.enroll', props.learningPath.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      isEnrolling.value = false
    },
  })
}

const formatDate = dateString => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    })
  } catch (e) {
    return dateString
  }
}

function isCourseUnlocked(index) {
  if (!props.enrollment) return false
  if (index === 0) return true
  const previousCourse = props.learningPath.courses?.[index - 1]
  return previousCourse && props.completedCourseIds.includes(previousCourse.id)
}
</script>

<template>
  <div class="learning-path-detail-container">
    <!-- Back Navigation -->
    <div class="mb-4">
      <VBtn
        variant="text"
        color="primary"
        size="small"
        class="font-weight-medium px-2"
        :href="route('student.learning-paths.index')"
      >
        <VIcon start icon="mdi-arrow-left" size="18" />
        Kembali ke Daftar Jalur Belajar
      </VBtn>
    </div>

    <!-- Hero Card Banner -->
    <VCard flat border class="mb-6 rounded-xl overflow-hidden bg-surface">
      <div class="pa-6 pa-md-8">
        <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-6">
          <div class="flex-grow-1">
            <div class="d-flex align-center ga-2 flex-wrap mb-3">
              <VChip size="small" color="primary" variant="tonal" label class="font-weight-bold">
                <VIcon start icon="mdi-map-marker-path" size="14" />
                Jalur Belajar Terstruktur
              </VChip>
              <VChip size="small" variant="outlined" color="default" label>
                <VIcon start icon="mdi-book-multiple-outline" size="14" />
                {{ learningPath.courses?.length || 0 }} Kursus Terangkai
              </VChip>
              <VChip size="small" variant="outlined" color="default" label>
                <VIcon start icon="mdi-clock-outline" size="14" />
                ~{{ (learningPath.courses?.length || 0) * 4 }} Jam Estimasi
              </VChip>
            </div>

            <h3 class="text-h4 font-weight-bold text-high-emphasis mb-3">
              {{ learningPath.title }}
            </h3>

            <p class="text-body-1 text-medium-emphasis mb-0" style="max-width: 820px; line-height: 1.6;">
              {{ learningPath.description || 'Jalur pembelajaran komprehensif ini dirancang berurutan guna membimbing peserta dari pemahaman dasar hingga penguasaan materi tingkat lanjut secara aplikatif.' }}
            </p>
          </div>

          <!-- Quick Action Card for Hero -->
          <div
            v-if="!enrollment || isLocked"
            class="pa-6 rounded-xl bg-background border text-center"
            style="min-width: 260px;"
          >
            <div class="text-caption text-medium-emphasis font-weight-medium mb-1">Status Jalur</div>
            <div class="text-h6 font-weight-bold text-high-emphasis mb-4">Belum Terdaftar</div>
            <VBtn
              block
              color="primary"
              size="large"
              variant="elevated"
              class="font-weight-bold shadow-sm"
              :loading="isEnrolling"
              @click="enroll"
            >
              <VIcon start icon="mdi-lightning-bolt" size="18" />
              Ikuti Jalur Belajar
            </VBtn>
            <div class="text-caption text-medium-emphasis mt-3">
              Akses bebas tanpa batas ke seluruh materi
            </div>
          </div>
        </div>

        <!-- Enrolled Progress Bar -->
        <template v-if="enrollment && !isLocked">
          <VDivider class="my-6" />

          <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-4">
            <div class="flex-grow-1 w-100">
              <div class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center ga-2">
                  <span class="text-subtitle-2 font-weight-bold">Progres Jalur Belajar:</span>
                  <VChip
                    size="small"
                    :color="progress === 100 ? 'success' : 'primary'"
                    variant="tonal"
                    label
                    class="font-weight-bold"
                  >
                    {{ completedCourseIds.length }} / {{ learningPath.courses?.length || 0 }} Kursus Selesai
                  </VChip>
                </div>
                <span class="text-h6 font-weight-bold" :class="progress === 100 ? 'text-success' : 'text-primary'">
                  {{ progress }}%
                </span>
              </div>
              <VProgressLinear
                :model-value="progress"
                :color="progress === 100 ? 'success' : 'primary'"
                height="10"
                rounded
                bg-opacity="0.15"
              />
            </div>

            <div class="text-caption text-medium-emphasis text-md-end text-nowrap">
              <div v-if="enrollment.completed_at" class="text-success font-weight-bold d-flex align-center ga-1">
                <VIcon icon="mdi-check-decagram" size="16" />
                Tuntas pada {{ formatDate(enrollment.completed_at) }}
              </div>
              <div v-else class="d-flex align-center ga-1">
                <VIcon icon="mdi-calendar-start" size="16" />
                Dimulai sejak {{ formatDate(enrollment.started_at) }}
              </div>
            </div>
          </div>
        </template>
      </div>
    </VCard>

    <!-- Next Course Notification Banner (If Enrolled & Next Course Available) -->
    <VCard
      v-if="enrollment && !isLocked && nextCourse"
      flat
      border
      class="mb-6 rounded-xl pa-5 bg-surface border-primary"
      style="border-left: 5px solid rgb(var(--v-theme-primary)) !important;"
    >
      <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-4">
        <div class="d-flex align-center ga-4">
          <VAvatar color="primary" variant="tonal" size="48" rounded="lg">
            <VIcon icon="mdi-play-circle-outline" size="28" />
          </VAvatar>
          <div>
            <div class="text-caption text-primary font-weight-bold text-uppercase">
              Langkah Kursus Anda Berikutnya
            </div>
            <h5 class="text-h6 font-weight-bold text-high-emphasis mb-0">
              {{ nextCourse.title }}
            </h5>
          </div>
        </div>

        <VBtn
          color="primary"
          variant="elevated"
          class="font-weight-bold px-6"
          :href="route('student.courses.show', nextCourse.slug || nextCourse.id)"
        >
          Lanjutkan Kursus
          <VIcon end icon="mdi-arrow-right" size="18" />
        </VBtn>
      </div>
    </VCard>

    <!-- Completed Congratulation Banner (If 100%) -->
    <VCard
      v-else-if="enrollment && !isLocked && progress === 100"
      flat
      border
      class="mb-6 rounded-xl pa-6 text-center bg-surface border-success"
      style="border-left: 5px solid rgb(var(--v-theme-success)) !important;"
    >
      <div class="d-flex flex-column align-center">
        <VAvatar color="success" variant="tonal" size="56" class="mb-3">
          <VIcon icon="mdi-trophy-variant" size="32" color="success" />
        </VAvatar>
        <h5 class="text-h5 font-weight-bold text-success mb-1">
          Selamat! Seluruh Rangkaian Kursus Telah Tuntas
        </h5>
        <p class="text-body-2 text-medium-emphasis mb-0" style="max-width: 520px;">
          Anda telah berhasil menyelesaikan semua modul pada kurikulum ini. Anda dapat mengulas materi kursus kapan saja.
        </p>
      </div>
    </VCard>

    <!-- Courses Syllabus Roadmap -->
    <VCard flat border class="rounded-xl overflow-hidden bg-surface">
      <div class="pa-5 border-b d-flex justify-space-between align-center bg-background">
        <div>
          <h5 class="text-h6 font-weight-bold mb-0">Rangkaian Kursus Kurikulum</h5>
          <span class="text-caption text-medium-emphasis">Ikuti pembelajaran kursus secara terurut sesuai alur di bawah ini</span>
        </div>
        <VChip size="small" variant="tonal" color="primary" label class="font-weight-medium">
          {{ learningPath.courses?.length || 0 }} Modul Kursus
        </VChip>
      </div>

      <div class="pa-4 pa-md-6">
        <div class="course-timeline-list d-flex flex-column ga-4">
          <div
            v-for="(course, index) in (learningPath.courses || [])"
            :key="course.id"
            class="course-item-card pa-4 pa-md-5 rounded-xl border bg-surface transition-swing"
            :class="{
              'border-success': completedCourseIds.includes(course.id),
              'border-primary': isCourseUnlocked(index) && !completedCourseIds.includes(course.id),
              'opacity-75': !isCourseUnlocked(index),
            }"
          >
            <div class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-4">
              <!-- Left: Step Badge + Info -->
              <div class="d-flex align-center ga-4 flex-grow-1">
                <!-- Step Number Avatar -->
                <VAvatar
                  size="44"
                  rounded="lg"
                  :color="completedCourseIds.includes(course.id) ? 'success' : (isCourseUnlocked(index) ? 'primary' : 'default')"
                  :variant="completedCourseIds.includes(course.id) ? 'flat' : 'tonal'"
                  class="font-weight-bold text-subtitle-1 flex-shrink-0"
                >
                  <VIcon
                    v-if="completedCourseIds.includes(course.id)"
                    icon="mdi-check"
                    size="22"
                    color="white"
                  />
                  <VIcon
                    v-else-if="!isCourseUnlocked(index)"
                    icon="mdi-lock-outline"
                    size="20"
                  />
                  <span v-else>{{ index + 1 }}</span>
                </VAvatar>

                <!-- Course Details -->
                <div class="flex-grow-1">
                  <div class="d-flex align-center ga-2 flex-wrap mb-1">
                    <span class="text-caption font-weight-bold text-uppercase text-medium-emphasis">
                      Langkah {{ index + 1 }}
                    </span>
                    <VChip
                      v-if="completedCourseIds.includes(course.id)"
                      size="x-small"
                      color="success"
                      variant="tonal"
                      label
                      class="font-weight-bold"
                    >
                      Selesai
                    </VChip>
                    <VChip
                      v-else-if="isCourseUnlocked(index)"
                      size="x-small"
                      color="primary"
                      variant="tonal"
                      label
                      class="font-weight-medium"
                    >
                      Siap Dipelajari
                    </VChip>
                    <VChip
                      v-else
                      size="x-small"
                      color="default"
                      variant="tonal"
                      label
                      class="font-weight-medium"
                    >
                      Terkunci
                    </VChip>
                  </div>

                  <h6 class="text-subtitle-1 font-weight-bold text-high-emphasis mb-1">
                    {{ course.title }}
                  </h6>

                  <div class="d-flex align-center ga-4 text-caption text-medium-emphasis">
                    <span v-if="course.sections_count">
                      <VIcon start icon="mdi-view-headline" size="14" />
                      {{ course.sections_count }} Sesi Belajar
                    </span>
                    <span v-if="course.level">
                      <VIcon start icon="mdi-signal" size="14" />
                      Tingkat: {{ course.level }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Right: Action Button -->
              <div class="flex-shrink-0 align-self-stretch align-self-md-center">
                <!-- If completed: review course -->
                <VBtn
                  v-if="completedCourseIds.includes(course.id)"
                  variant="tonal"
                  color="success"
                  class="font-weight-medium w-100 w-md-auto"
                  :href="route('student.courses.show', course.slug || course.id)"
                >
                  <VIcon start icon="mdi-eye-outline" size="18" />
                  Ulas Materi
                </VBtn>

                <!-- If unlocked: start/continue -->
                <VBtn
                  v-else-if="isCourseUnlocked(index)"
                  variant="elevated"
                  color="primary"
                  class="font-weight-medium w-100 w-md-auto"
                  :href="route('student.courses.show', course.slug || course.id)"
                >
                  <VIcon start icon="mdi-play-circle" size="18" />
                  Mulai Kursus
                </VBtn>

                <!-- If locked: tooltip/indicator -->
                <VBtn
                  v-else
                  disabled
                  variant="outlined"
                  color="default"
                  class="font-weight-medium w-100 w-md-auto"
                >
                  <VIcon start icon="mdi-lock" size="16" />
                  Selesaikan Langkah Sebelumnya
                </VBtn>
              </div>
            </div>
          </div>
        </div>
      </div>
    </VCard>
  </div>
</template>

<style scoped>
.course-item-card {
  transition: all 0.2s ease;
}
.course-item-card:hover {
  box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.06);
}
</style>
