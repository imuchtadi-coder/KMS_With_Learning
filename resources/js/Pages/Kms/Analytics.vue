<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  docMetrics: {
    type: Object,
    default: () => ({
      total: 0,
      published: 0,
      pending: 0,
      draft: 0,
      healthy: 0,
      expiring_soon: 0,
      expired: 0,
    }),
  },
  qaMetrics: {
    type: Object,
    default: () => ({
      total: 0,
      resolved: 0,
      unresolved: 0,
      resolution_rate: 0,
    }),
  },
  spaceBreakdown: {
    type: Array,
    default: () => [],
  },
  topAuthors: {
    type: Array,
    default: () => [],
  },
  topSkills: {
    type: Array,
    default: () => [],
  },
  contentGaps: {
    type: Array,
    default: () => [],
  },
  urgentReviewArticles: {
    type: Array,
    default: () => [],
  },
})

// Calculate percentage for freshness
const healthPercentage = computed(() => {
  if (!props.docMetrics.published) return 0
  return Math.round((props.docMetrics.healthy / props.docMetrics.published) * 100)
})

const getDaysUntilReview = (dateStr) => {
  if (!dateStr) return null
  const reviewDate = new Date(dateStr)
  const today = new Date()
  const diffTime = reviewDate - today
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}
</script>

<template>
  <div class="kms-analytics-container pb-8">
    <!-- Top Header Banner -->
    <VCard elevation="0" class="mb-6 border rounded-lg overflow-hidden">
      <div class="px-6 py-6 d-flex flex-wrap align-center justify-space-between gap-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.08) 0%, rgba(14, 165, 233, 0.05) 100%);">
        <div>
          <div class="d-flex align-center gap-2 mb-1">
            <VIcon icon="mdi-chart-timeline-variant-shimmer" color="primary" size="28" />
            <h1 class="text-h5 font-weight-bold mb-0">
              KMS Executive Analytics & Governance
            </h1>
          </div>
          <p class="text-body-2 text-medium-emphasis mb-0">
            Monitoring kesehatan repositori SOP, kepatuhan siklus review dokumen, pemecahan masalah (Q&A), dan distribusi keahlian pakar.
          </p>
        </div>

        <div class="d-flex align-center gap-2">
          <VBtn
            variant="outlined"
            color="primary"
            prepend-icon="mdi-folder-multiple-outline"
            :to="'/spaces'"
          >
            Spaces
          </VBtn>
          <VBtn
            color="primary"
            prepend-icon="mdi-file-document-plus-outline"
            :to="'/articles/create'"
          >
            Tulis SOP Baru
          </VBtn>
        </div>
      </div>
    </VCard>

    <!-- KPI Summary Row -->
    <VRow class="mb-6">
      <!-- Total SOP -->
      <VCol cols="12" sm="6" md="3">
        <VCard elevation="0" class="border rounded-lg h-100">
          <VCardText class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption font-weight-medium text-uppercase text-medium-emphasis">
                Total SOP & Dokumen
              </span>
              <VAvatar color="primary" variant="tonal" size="36">
                <VIcon icon="mdi-file-document-multiple-outline" size="20" />
              </VAvatar>
            </div>
            <div class="text-h4 font-weight-bold mb-1">
              {{ docMetrics.total }}
            </div>
            <div class="d-flex align-center gap-1 text-caption text-medium-emphasis">
              <span class="text-success font-weight-medium">{{ docMetrics.published }} Terbit</span>
              <span>•</span>
              <span class="text-warning font-weight-medium">{{ docMetrics.pending }} Review</span>
              <span>•</span>
              <span>{{ docMetrics.draft }} Draf</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Freshness / Health Rate -->
      <VCol cols="12" sm="6" md="3">
        <VCard elevation="0" class="border rounded-lg h-100">
          <VCardText class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption font-weight-medium text-uppercase text-medium-emphasis">
                Kesehatan Dokumen
              </span>
              <VAvatar color="success" variant="tonal" size="36">
                <VIcon icon="mdi-shield-check-outline" size="20" />
              </VAvatar>
            </div>
            <div class="text-h4 font-weight-bold mb-1 text-success">
              {{ healthPercentage }}%
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ docMetrics.healthy }} dari {{ docMetrics.published }} SOP dalam masa berlaku aktif
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Review Warning & Expired -->
      <VCol cols="12" sm="6" md="3">
        <VCard elevation="0" class="border rounded-lg h-100">
          <VCardText class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption font-weight-medium text-uppercase text-medium-emphasis">
                Perlu Review / Kadaluarsa
              </span>
              <VAvatar :color="docMetrics.expired > 0 ? 'error' : 'warning'" variant="tonal" size="36">
                <VIcon :icon="docMetrics.expired > 0 ? 'mdi-alert-circle-outline' : 'mdi-clock-alert-outline'" size="20" />
              </VAvatar>
            </div>
            <div class="text-h4 font-weight-bold mb-1" :class="docMetrics.expired > 0 ? 'text-error' : 'text-warning'">
              {{ docMetrics.expired + docMetrics.expiring_soon }}
            </div>
            <div class="d-flex align-center gap-1 text-caption">
              <span class="text-error font-weight-medium">{{ docMetrics.expired }} Kadaluarsa</span>
              <span>•</span>
              <span class="text-warning font-weight-medium">{{ docMetrics.expiring_soon }} &lt; 14 Hari</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Q&A Resolution Rate -->
      <VCol cols="12" sm="6" md="3">
        <VCard elevation="0" class="border rounded-lg h-100">
          <VCardText class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption font-weight-medium text-uppercase text-medium-emphasis">
                Q&A Resolution Rate
              </span>
              <VAvatar color="info" variant="tonal" size="36">
                <VIcon icon="mdi-check-decagram-outline" size="20" />
              </VAvatar>
            </div>
            <div class="text-h4 font-weight-bold mb-1 text-info">
              {{ qaMetrics.resolution_rate }}%
            </div>
            <div class="text-caption text-medium-emphasis">
              {{ qaMetrics.resolved }} terjawab dari {{ qaMetrics.total }} kendala operasional
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Main Analytics Content -->
    <VRow>
      <!-- Left Column: Space Distribution & Compliance Urgency -->
      <VCol cols="12" lg="7">
        <!-- Distribution by Knowledge Space -->
        <VCard elevation="0" class="border rounded-lg mb-6">
          <VCardItem class="px-5 pt-5 pb-3">
            <div class="d-flex align-center justify-space-between">
              <div>
                <VCardTitle class="text-subtitle-1 font-weight-bold">
                  Distribusi SOP per Knowledge Space
                </VCardTitle>
                <VCardSubtitle class="text-caption text-medium-emphasis">
                  Sebaran dokumen operasional berdasarkan divisi dan domain kerja
                </VCardSubtitle>
              </div>
              <VBtn size="small" variant="text" color="primary" :to="'/spaces'">
                Kelola Spaces
              </VBtn>
            </div>
          </VCardItem>
          <VDivider />
          <VCardText class="pa-5">
            <div v-if="spaceBreakdown.length === 0" class="text-center py-6 text-medium-emphasis">
              Belum ada data knowledge spaces terdaftar.
            </div>
            <div v-else class="d-flex flex-column gap-4">
              <div
                v-for="space in spaceBreakdown"
                :key="space.id"
                class="space-stat-row"
              >
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="d-flex align-center gap-2">
                    <VAvatar size="24" :color="space.color || '#4F46E5'" class="text-white" variant="flat">
                      <VIcon :icon="space.icon || 'mdi-folder-outline'" size="14" />
                    </VAvatar>
                    <Link :href="`/spaces/${space.id}`" class="text-body-2 font-weight-medium text-high-emphasis text-decoration-none">
                      {{ space.name }}
                    </Link>
                  </div>
                  <span class="text-caption font-weight-bold">
                    {{ space.total_articles }} SOP ({{ docMetrics.total > 0 ? Math.round((space.total_articles / docMetrics.total) * 100) : 0 }}%)
                  </span>
                </div>
                <VProgressLinear
                  :model-value="docMetrics.total > 0 ? (space.total_articles / docMetrics.total) * 100 : 0"
                  :color="space.color || '#4F46E5'"
                  height="8"
                  rounded
                />
              </div>
            </div>
          </VCardText>
        </VCard>

        <!-- Urgent Review Queue (Document Governance) -->
        <VCard elevation="0" class="border rounded-lg">
          <VCardItem class="px-5 pt-5 pb-3">
            <div class="d-flex align-center justify-space-between">
              <div>
                <VCardTitle class="text-subtitle-1 font-weight-bold d-flex align-center gap-2">
                  <VIcon icon="mdi-alert-octagon-outline" color="error" size="20" />
                  Antrean Tinjauan SOP & Kepatuhan
                </VCardTitle>
                <VCardSubtitle class="text-caption text-medium-emphasis">
                  Dokumen yang telah melewati jadwal review atau mendekati masa kadaluarsa (ISO / Kepatuhan)
                </VCardSubtitle>
              </div>
              <VBtn
                size="small"
                variant="tonal"
                color="error"
                :to="'/articles?needs_review=1'"
              >
                Lihat Semua Antrean
              </VBtn>
            </div>
          </VCardItem>
          <VDivider />
          <VCardText class="pa-0">
            <div v-if="urgentReviewArticles.length === 0" class="text-center py-8 text-medium-emphasis">
              <VIcon icon="mdi-check-circle-outline" color="success" size="36" class="mb-2" />
              <div class="font-weight-medium text-body-2">Semua SOP dalam kondisi patuh & up-to-date!</div>
              <div class="text-caption">Tidak ada dokumen yang kadaluarsa dalam 14 hari ke depan.</div>
            </div>
            <VList v-else lines="two" class="py-0">
              <VListItem
                v-for="item in urgentReviewArticles"
                :key="item.id"
                class="px-5 py-3 border-b"
              >
                <template #prepend>
                  <VAvatar
                    :color="getDaysUntilReview(item.next_review_at) < 0 ? 'error' : 'warning'"
                    variant="tonal"
                    size="36"
                    class="mr-3"
                  >
                    <VIcon
                      :icon="getDaysUntilReview(item.next_review_at) < 0 ? 'mdi-alert' : 'mdi-clock-alert-outline'"
                      size="20"
                    />
                  </VAvatar>
                </template>

                <VListItemTitle class="font-weight-medium mb-1">
                  <Link :href="`/articles/${item.id}`" class="text-high-emphasis text-decoration-none">
                    {{ item.title }}
                  </Link>
                  <VChip size="x-small" variant="tonal" class="ml-2 font-mono">
                    v{{ item.version || '1.0' }}
                  </VChip>
                </VListItemTitle>

                <VListItemSubtitle class="text-caption d-flex align-center gap-2">
                  <span v-if="item.space" class="text-primary font-weight-medium">
                    📁 {{ item.space.name }}
                  </span>
                  <span>•</span>
                  <span>Penulis: {{ item.author?.name || 'SME' }}</span>
                </VListItemSubtitle>

                <template #append>
                  <div class="d-flex flex-column align-end gap-1">
                    <VChip
                      size="x-small"
                      :color="getDaysUntilReview(item.next_review_at) < 0 ? 'error' : 'warning'"
                      variant="flat"
                      class="font-weight-bold"
                    >
                      <template v-if="getDaysUntilReview(item.next_review_at) < 0">
                        Lewat {{ Math.abs(getDaysUntilReview(item.next_review_at)) }} hari
                      </template>
                      <template v-else>
                        Sisa {{ getDaysUntilReview(item.next_review_at) }} hari
                      </template>
                    </VChip>
                    <VBtn
                      size="x-small"
                      variant="text"
                      color="primary"
                      prepend-icon="mdi-pencil-outline"
                      :to="`/articles/${item.id}/edit`"
                    >
                      Revisi & Review
                    </VBtn>
                  </div>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Right Column: Content Gaps & SME Directory Leaderboard -->
      <VCol cols="12" lg="5">
        <!-- Content Gaps (High activity unresolved Q&A) -->
        <VCard elevation="0" class="border rounded-lg mb-6">
          <VCardItem class="px-5 pt-5 pb-3">
            <div class="d-flex align-center justify-space-between">
              <div>
                <VCardTitle class="text-subtitle-1 font-weight-bold d-flex align-center gap-2">
                  <VIcon icon="mdi-lightbulb-alert-outline" color="warning" size="20" />
                  Content Gaps (Peluang SOP Baru)
                </VCardTitle>
                <VCardSubtitle class="text-caption text-medium-emphasis">
                  Pertanyaan kendala yang sering ditanyakan tapi belum memiliki SOP resmi
                </VCardSubtitle>
              </div>
            </div>
          </VCardItem>
          <VDivider />
          <VCardText class="pa-0">
            <div v-if="contentGaps.length === 0" class="text-center py-6 text-medium-emphasis">
              Belum ada celah pengetahuan signifikan yang terdeteksi di Problem Solving Hub.
            </div>
            <VList v-else lines="two" class="py-0">
              <VListItem
                v-for="gap in contentGaps"
                :key="gap.id"
                class="px-5 py-3 border-b"
              >
                <VListItemTitle class="font-weight-medium mb-1 text-wrap">
                  <Link :href="`/discussions/${gap.id}`" class="text-high-emphasis text-decoration-none">
                    {{ gap.title }}
                  </Link>
                </VListItemTitle>
                <VListItemSubtitle class="text-caption d-flex align-center gap-2">
                  <VChip size="x-small" color="secondary" variant="tonal">
                    {{ gap.replies_count }} Diskusi Tanggapan
                  </VChip>
                  <span class="text-warning font-weight-medium">• Belum Terpecahkan</span>
                </VListItemSubtitle>
                <template #append>
                  <VBtn
                    size="small"
                    variant="tonal"
                    color="primary"
                    prepend-icon="mdi-file-plus-outline"
                    :to="`/articles/create?source_discussion_id=${gap.id}&title=${encodeURIComponent('SOP: ' + gap.title)}`"
                  >
                    Bikin SOP
                  </VBtn>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>

        <!-- Top SME Contributors Leaderboard -->
        <VCard elevation="0" class="border rounded-lg mb-6">
          <VCardItem class="px-5 pt-5 pb-3">
            <div class="d-flex align-center justify-space-between">
              <div>
                <VCardTitle class="text-subtitle-1 font-weight-bold d-flex align-center gap-2">
                  <VIcon icon="mdi-trophy-outline" color="amber-darken-2" size="20" />
                  Top Kontributor Pengetahuan (SME)
                </VCardTitle>
                <VCardSubtitle class="text-caption text-medium-emphasis">
                  Karyawan teraktif dalam mendokumentasikan SOP & solusi teknis
                </VCardSubtitle>
              </div>
              <VBtn size="small" variant="text" color="primary" :to="'/experts'">
                Direktori Pakar
              </VBtn>
            </div>
          </VCardItem>
          <VDivider />
          <VCardText class="pa-0">
            <div v-if="topAuthors.length === 0" class="text-center py-6 text-medium-emphasis">
              Belum ada data kontributor SOP.
            </div>
            <VList v-else lines="two" class="py-0">
              <VListItem
                v-for="(author, idx) in topAuthors"
                :key="author.id"
                class="px-5 py-3 border-b"
              >
                <template #prepend>
                  <div class="d-flex align-center mr-3">
                    <span class="font-weight-bold text-caption text-medium-emphasis mr-2" style="width: 16px;">
                      #{{ idx + 1 }}
                    </span>
                    <VAvatar size="36" color="primary" variant="tonal">
                      <VImg v-if="author.avatar" :src="author.avatar" />
                      <span v-else class="text-caption font-weight-bold">
                        {{ author.name ? author.name.charAt(0).toUpperCase() : 'U' }}
                      </span>
                    </VAvatar>
                  </div>
                </template>

                <VListItemTitle class="font-weight-bold text-body-2">
                  {{ author.name }}
                </VListItemTitle>
                <VListItemSubtitle class="text-caption">
                  {{ author.department || 'Operasional & Teknis' }}
                </VListItemSubtitle>

                <template #append>
                  <VChip size="small" color="primary" variant="tonal" class="font-weight-bold">
                    {{ author.published_articles_count }} SOP
                  </VChip>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>

        <!-- Top Expertise & Skills in Demand -->
        <VCard elevation="0" class="border rounded-lg">
          <VCardItem class="px-5 pt-5 pb-3">
            <VCardTitle class="text-subtitle-1 font-weight-bold d-flex align-center gap-2">
              <VIcon icon="mdi-tag-heart-outline" color="purple" size="20" />
              Taksonomi Keahlian Terpopuler
            </VCardTitle>
            <VCardSubtitle class="text-caption text-medium-emphasis">
              Keahlian teknis dengan pakar terbanyak di organisasi
            </VCardSubtitle>
          </VCardItem>
          <VDivider />
          <VCardText class="pa-5">
            <div v-if="topSkills.length === 0" class="text-center py-4 text-medium-emphasis">
              Belum ada keahlian terdaftar.
            </div>
            <div v-else class="d-flex flex-wrap gap-2">
              <VChip
                v-for="skill in topSkills"
                :key="skill.id"
                size="default"
                variant="outlined"
                color="primary"
                class="font-weight-medium"
              >
                <VIcon icon="mdi-star" size="14" class="mr-1 text-warning" />
                {{ skill.name }}
                <VChip size="x-small" color="primary" variant="tonal" class="ml-2">
                  {{ skill.user_skills_count }} Pakar
                </VChip>
              </VChip>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.kms-analytics-container {
  max-width: 1400px;
  margin: 0 auto;
}

.space-stat-row {
  transition: all 0.2s ease;
}

.space-stat-row:hover {
  transform: translateX(4px);
}
</style>
