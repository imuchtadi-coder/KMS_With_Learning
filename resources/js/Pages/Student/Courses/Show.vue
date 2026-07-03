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
const panelStatus = ref(0)

const totalLectures = computed(() => {
  return props.course.content?.reduce((sum, s) => sum + (s.topics?.length ?? 0), 0) ?? 0
})

const outcomes = computed(() => {
  if (!props.course.learningOutcomes) return []
  return String(props.course.learningOutcomes).split('\n').filter(Boolean)
})

const avgRating = computed(() => {
  return Number(props.course.rating ?? 0).toFixed(1)
})

const reviewForm = reactive({
  rating: props.userReview?.rating ?? 0,
  review: props.userReview?.review ?? '',
  processing: false,
})

function submitReview() {
  reviewForm.processing = true
  if (props.userReview) {
    router.put(route('student.reviews.update', [props.course.id, props.userReview.id]), {
      rating: reviewForm.rating,
      review: reviewForm.review,
    }, {
      preserveScroll: true,
      onFinish: () => { reviewForm.processing = false },
    })
  } else {
    router.post(route('student.reviews.store', props.course.id), {
      rating: reviewForm.rating,
      review: reviewForm.review,
    }, {
      preserveScroll: true,
      onFinish: () => { reviewForm.processing = false },
    })
  }
}

function deleteReview() {
  if (!confirm('Remove your review?')) return
  deletingReview.value = true
  router.delete(route('student.reviews.destroy', [props.course.id, props.userReview.id]), {
    preserveScroll: true,
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

function timeAgo(date) {
  if (!date) return ''
  const diff = Date.now() - new Date(date).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 30) return `${days}d ago`
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}
</script>

<template>
  <VRow>
    <VCol cols="12" md="8">
      <VCard>
        <VCardItem
          :title="course.courseTitle"
          class="pb-6"
        >
          <template #subtitle>
            <div class="text-body-1">
              Prof. <span class="text-h6 d-inline-block">{{ course.instructor }}</span>
            </div>
          </template>
          <template #append>
            <div class="d-flex gap-4 align-center">
              <VChip variant="tonal" color="error" size="small">
                {{ course.tags || course.category }}
              </VChip>
              <VIcon size="24" class="cursor-pointer" icon="mdi-share-variant" />
              <VIcon size="24" class="cursor-pointer" icon="mdi-bookmark-outline" />
            </div>
          </template>
        </VCardItem>
        <VCardText>
          <VCard flat border>
            <div class="px-2 pt-2">
              <div
                class="d-flex align-center justify-center bg-grey-darken-4 rounded"
                :style="{ height: $vuetify.display.mdAndUp ? '440px' : '250px' }"
              >
                <div class="text-center pa-4">
                  <VIcon icon="mdi-play-circle-outline" size="64" color="grey-lighten-1" />
                  <div class="text-grey-lighten-1 mt-2 text-body-2">
                    Video player coming soon
                  </div>
                </div>
              </div>
            </div>
            <VCardText>
              <h5 class="text-h5 mb-4">
                About this course
              </h5>
              <p class="text-body-1">
                {{ course.about }}
              </p>
              <VDivider class="my-6" />

              <h5 class="text-h5 mb-4">
                By the numbers
              </h5>
              <div class="d-flex gap-x-12 gap-y-5 flex-wrap">
                <div>
                  <VList class="card-list text-medium-emphasis">
                    <VListItem>
                      <template #prepend>
                        <VIcon icon="mdi-check" size="20" />
                      </template>
                      <VListItemTitle>Skill Level: {{ course.skillLevel }}</VListItemTitle>
                    </VListItem>
                    <VListItem>
                      <template #prepend>
                        <VIcon icon="mdi-account-group" size="20" />
                      </template>
                      <VListItemTitle>Students: {{ course.totalStudents }}</VListItemTitle>
                    </VListItem>
                    <VListItem>
                      <template #prepend>
                        <VIcon icon="mdi-web" size="20" />
                      </template>
                      <VListItemTitle>Languages: {{ course.language }}</VListItemTitle>
                    </VListItem>
                    <VListItem>
                      <template #prepend>
                        <VIcon icon="mdi-subtitles" size="20" />
                      </template>
                      <VListItemTitle>Captions: {{ course.isCaptions ? 'Yes' : 'No' }}</VListItemTitle>
                    </VListItem>
                  </VList>
                </div>
                <div>
                  <VList class="card-list text-medium-emphasis">
                    <VListItem>
                      <template #prepend>
                        <VIcon icon="mdi-play-box-multiple" size="20" />
                      </template>
                      <VListItemTitle>Lectures: {{ course.totalLectures }}</VListItemTitle>
                    </VListItem>
                    <VListItem>
                      <template #prepend>
                        <VIcon icon="mdi-clock-outline" size="20" />
                      </template>
                      <VListItemTitle>Video: {{ course.length }}</VListItemTitle>
                    </VListItem>
                  </VList>
                </div>
              </div>
              <VDivider class="my-6" />

              <h5 class="text-h5 mb-4">
                Description
              </h5>
              <div v-html="course.description" />

              <VDivider class="my-6" />

              <h5 class="text-h5 mb-4">
                Instructor
              </h5>
              <div class="d-flex align-center gap-x-4">
                <VAvatar :image="course.instructorAvatar" size="38" />
                <div>
                  <h6 class="text-h6 mb-1">
                    {{ course.instructor }}
                  </h6>
                  <div class="text-body-2">
                    {{ course.instructorPosition }}
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCardText>
      </VCard>

      <!-- Reviews -->
      <VCard flat border class="mt-6">
        <VCardTitle>Reviews</VCardTitle>
        <VCardText>
          <div class="d-flex align-center ga-4 mb-4">
            <div class="text-center">
              <div class="text-h3 font-weight-bold">{{ avgRating }}</div>
              <VRating
                :model-value="Math.round(course.rating)"
                color="warning"
                density="compact"
                size="small"
                readonly
              />
              <div class="text-caption text-medium-emphasis mt-1">
                {{ course.ratingCount }} review{{ course.ratingCount !== 1 ? 's' : '' }}
              </div>
            </div>
          </div>

          <div v-if="isEnrolled" class="mb-4 pa-4 bg-light-info rounded">
            <div class="font-weight-medium mb-2">
              {{ userReview ? 'Your Review' : 'Write a Review' }}
            </div>
            <VRating
              v-model="reviewForm.rating"
              color="warning"
              density="compact"
              size="28"
              class="mb-2"
            />
            <VTextarea
              v-model="reviewForm.review"
              variant="outlined"
              density="default"
              placeholder="Share your thoughts about this course..."
              rows="3"
              hide-details
            />
            <div class="d-flex ga-2 mt-2">
              <VBtn
                color="primary"
                size="small"
                variant="elevated"
                :loading="reviewForm.processing"
                @click="submitReview"
              >
                {{ userReview ? 'Update Review' : 'Submit Review' }}
              </VBtn>
              <VBtn
                v-if="userReview"
                color="error"
                size="small"
                variant="tonal"
                :loading="deletingReview"
                @click="deleteReview"
              >
                Remove
              </VBtn>
            </div>
          </div>

          <div v-if="!course.reviews || course.reviews.length === 0" class="text-center pa-4 text-medium-emphasis text-body-2">
            No reviews yet. Be the first to review!
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol cols="12" md="4">
      <div class="course-content">
        <VCard flat border class="mb-6">
          <VCardText>
            <div class="text-h5 font-weight-bold mb-1">
              {{ course.price > 0 ? `Rp ${formatPrice(course.price)}` : 'Free' }}
            </div>
          </VCardText>
          <VDivider />
          <VCardText>
              <VBtn
                v-if="isEnrolled"
                color="success"
                variant="elevated"
                block
                :href="route('student.enrollments.show', enrollmentId ?? course.id)"
              >
                <VIcon start icon="mdi-play" />
                Continue Learning
              </VBtn>
            <VBtn
              v-else
              color="primary"
              variant="elevated"
              block
              :loading="enrolling"
              @click="enroll(course.id)"
            >
              <VIcon start icon="mdi-book-plus" />
              Enroll Now
            </VBtn>
          </VCardText>
        </VCard>

        <VExpansionPanels
          v-model="panelStatus"
          variant="accordion"
          class="expansion-panels-width-border mb-6"
        >
          <template
            v-for="(section, index) in course.content"
            :key="index"
          >
            <VExpansionPanel
              elevation="0"
              :value="index"
              expand-icon="mdi-chevron-right"
              collapse-icon="mdi-chevron-down"
            >
              <template #title>
                <div>
                  <h5 class="text-h5 mb-1">
                    {{ section.title }}
                  </h5>
                  <div class="text-medium-emphasis font-weight-normal">
                    {{ section.status }} | {{ section.time }}
                  </div>
                </div>
              </template>
              <template #text>
                <VList class="card-list">
                  <VListItem
                    v-for="(topic, id) in section.topics"
                    :key="id"
                    class="py-4"
                  >
                    <template #prepend>
                      <VCheckbox
                        :model-value="topic.isCompleted"
                        class="me-1"
                        readonly
                      />
                    </template>
                    <VListItemTitle class="text-high-emphasis font-weight-medium">
                      {{ id + 1 }} . {{ topic.title }}
                    </VListItemTitle>
                    <VListItemSubtitle>
                      <div class="text-body-2">
                        {{ topic.time }}
                      </div>
                    </VListItemSubtitle>
                  </VListItem>
                </VList>
              </template>
            </VExpansionPanel>
          </template>
        </VExpansionPanels>

        <VCard flat border>
          <VCardText>
            <div class="d-flex align-center gap-x-4">
              <VAvatar :image="course.instructorAvatar" size="38" />
              <div>
                <h6 class="text-h6 mb-1">
                  {{ course.instructor }}
                </h6>
                <div class="text-body-2">
                  {{ course.instructorPosition }}
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>
      </div>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
.course-content {
  position: sticky;
  inset-block: 4rem 0;
}

.card-list {
  --v-card-list-gap: 16px;
}
</style>

<style lang="scss">
@use "@layouts/styles/mixins" as layoutsMixins;

body .v-layout .v-application__wrap {
  .course-content {
    .v-expansion-panels {
      border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
      border-radius: 6px;

      .v-expansion-panel {
        &--active {
          .v-expansion-panel-title--active {
            border-block-end: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));

            .v-expansion-panel-title__overlay {
              opacity: var(--v-hover-opacity) !important;
            }
          }
        }

        .v-expansion-panel-title {
          .v-expansion-panel-title__overlay {
            background-color: rgba(var(--v-theme-on-surface));
            opacity: var(--v-hover-opacity) !important;
          }

          &:hover {
            .v-expansion-panel-title__overlay {
              opacity: var(--v-hover-opacity) !important;
            }
          }

          &__icon {
            .v-icon {
              block-size: 1.5rem !important;
              color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
              font-size: 1.5rem !important;
              inline-size: 1.5rem !important;

              @include layoutsMixins.rtl {
                transform: scaleX(-1);
              }
            }
          }
        }

        .v-expansion-panel-text {
          &__wrapper {
            padding-block: 1rem;
            padding-inline: 0.75rem;
          }
        }
      }
    }
  }

  .card-list {
    .v-list-item__prepend {
      .v-list-item__spacer {
        inline-size: 8px !important;
      }
    }
  }
}
</style>
