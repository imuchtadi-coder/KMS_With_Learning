<script setup>
import AcademyCourseTable from '@/views/apps/academy/AcademyCourseTable.vue'

defineProps({
  recentEnrollments: {
    type: Array,
    default: () => [],
  },
  totalEnrolled: {
    type: Number,
    default: 0,
  },
  inProgress: {
    type: Number,
    default: 0,
  },
  completed: {
    type: Number,
    default: 0,
  },
  certificates: {
    type: Array,
    default: () => [],
  },
  tenantName: {
    type: String,
    default: 'Platform',
  },
})

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div>
    <VRow class="py-6">
      <VCol cols="12" md="8" :class="$vuetify.display.mdAndUp ? 'border-e' : 'border-b'">
        <div class="pe-3">
          <h5 class="text-h5 mb-2">
            Welcome back, <span class="text-h4">{{ $page.props.auth?.user?.name ?? 'Student' }} 👋🏻</span>
          </h5>
          <div class="text-wrap text-body-1" style="max-inline-size: 360px;">
            Continue learning in <strong>{{ tenantName }}</strong>. Your progress this week is awesome. Let's keep it up!
          </div>

          <div class="d-flex justify-space-between flex-wrap gap-4 flex-column flex-md-row mt-4">
            <div class="d-flex align-center">
              <VAvatar variant="tonal" color="primary" rounded size="54" class="text-primary me-4">
                <VIcon icon="mdi-school" size="38" />
              </VAvatar>
              <div>
                <h6 class="text-h6 text-medium-emphasis">
                  Enrolled Courses
                </h6>
                <h4 class="text-h4 text-primary">
                  {{ totalEnrolled }}
                </h4>
              </div>
            </div>
            <div class="d-flex align-center">
              <VAvatar variant="tonal" color="warning" rounded size="54" class="me-4">
                <VIcon icon="mdi-progress-wrench" size="38" />
              </VAvatar>
              <div>
                <h6 class="text-h6 text-medium-emphasis">
                  In Progress
                </h6>
                <h4 class="text-h4 text-warning">
                  {{ inProgress }}
                </h4>
              </div>
            </div>
            <div class="d-flex align-center">
              <VAvatar variant="tonal" color="success" rounded size="54" class="me-4">
                <VIcon icon="mdi-check-circle" size="38" />
              </VAvatar>
              <div>
                <h6 class="text-h6 text-medium-emphasis">
                  Completed
                </h6>
                <h4 class="text-h4 text-success">
                  {{ completed }}
                </h4>
              </div>
            </div>
          </div>
        </div>
      </VCol>

      <VCol cols="12" md="4">
        <div class="d-flex justify-space-between align-center">
          <div class="d-flex flex-column ps-3">
            <h5 class="text-h5 mb-1 text-no-wrap">
              Certificates
            </h5>
            <div class="text-body-1 mb-7">
              Earned achievements
            </div>
            <h4 class="text-h4 mb-2">
              {{ certificates.length }}
            </h4>
            <div>
              <VChip color="success" label size="small">
                +{{ completed }} Completed
              </VChip>
            </div>
          </div>
          <div class="pa-4">
            <VIcon icon="mdi-certificate" size="80" color="info" />
          </div>
        </div>
      </VCol>
    </VRow>

    <VRow class="match-height">
      <VCol cols="12" md="8">
        <!-- Course Table -->
        <AcademyCourseTable :courses="recentEnrollments" />
      </VCol>

      <VCol cols="12" md="4">
        <!-- Certificates -->
        <VCard v-if="certificates.length > 0">
          <VCardItem title="Your Certificates">
            <template #append>
              <VBtn variant="text" color="primary" size="small" icon>
                <VIcon icon="mdi-dots-vertical" />
              </VBtn>
            </template>
          </VCardItem>
          <VCardText>
            <VList class="card-list">
              <VListItem
                v-for="cert in certificates"
                :key="cert.id"
              >
                <template #prepend>
                  <VAvatar rounded variant="tonal" color="info">
                    <VIcon icon="mdi-certificate" size="24" />
                  </VAvatar>
                </template>
                <VListItemTitle class="font-weight-medium">
                  {{ cert.course?.title ?? 'Certificate' }}
                </VListItemTitle>
                <VListItemSubtitle>
                  Issued {{ formatDate(cert.issued_at) }}
                </VListItemSubtitle>
                <template #append>
                  <div class="d-flex ga-1">
                    <VBtn
                      icon
                      variant="tonal"
                      color="secondary"
                      rounded
                      size="30"
                      :href="route('student.certificates.view', cert.id)"
                    >
                      <VIcon icon="mdi-eye" size="20" />
                    </VBtn>
                    <VBtn
                      icon
                      variant="tonal"
                      color="secondary"
                      rounded
                      size="30"
                      :href="route('student.certificates.download', cert.id)"
                    >
                      <VIcon icon="mdi-download" size="20" />
                    </VBtn>
                  </div>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>

        <VCard v-else>
          <VCardText class="text-center pa-6">
            <VIcon icon="mdi-certificate-outline" size="48" color="disabled" />
            <p class="text-body-2 text-medium-emphasis mt-2">
              Complete a course to earn your first certificate
            </p>
            <VBtn
              color="primary"
              variant="tonal"
              size="small"
              class="mt-2"
              :href="route('student.courses.list')"
            >
              Explore Courses
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 1rem;
}
</style>
