<script setup>
import { ref, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'

const props = defineProps({
  discussions: { type: Object, required: true },
  course: { type: Object, default: null },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      resolved: 0,
      unresolved: 0,
    }),
  },
})

const page = usePage()
const search = ref(props.filters?.search || '')
const selectedType = ref(props.filters?.type || 'all')
const selectedStatus = ref(props.filters?.status || 'all')
const selectedCategory = ref(props.filters?.category_id ? Number(props.filters.category_id) : null)

const applyFilters = () => {
  router.get(route('discussions.index'), {
    course_id: props.course?.id || undefined,
    search: search.value || undefined,
    type: selectedType.value !== 'all' ? selectedType.value : undefined,
    status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    category_id: selectedCategory.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const setCategoryFilter = (catId) => {
  selectedCategory.value = selectedCategory.value === catId ? null : catId
  applyFilters()
}

const userRoles = computed(() => page.props.auth?.user?.roles ?? [])

const canPinDiscussion = computed(() => {
  return userRoles.value.some(r => ['superadmin', 'tenant_admin', 'instructor'].includes(r))
})

const isInstructor = computed(() => userRoles.value.includes('instructor'))

const togglePin = (discussion) => {
  router.post(route('discussions.pin', discussion.id), {}, { preserveScroll: true })
}

// Strip HTML tags for clean card excerpt preview
const stripHtml = (html) => {
  if (!html) return ''
  const tmp = document.createElement('div')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}
</script>

<template>
  <div class="discussions-page">
    <!-- Header Section -->
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div>
        <div class="d-flex align-center ga-2 mb-1">
          <VIcon icon="mdi-forum-outline" color="primary" size="26" />
          <h4 class="text-h4 font-weight-bold mb-0">Forum Diskusi & Q&A</h4>
        </div>
        <p v-if="course" class="text-body-2 text-medium-emphasis mb-0">
          Diskusi Terkait Kursus: <strong>{{ course.title }}</strong>
        </p>
        <p v-else class="text-body-2 text-medium-emphasis mb-0">
          Wadah kolaborasi, tanya-jawab seputar materi, dan penanganan kendala teknis lapangan.
        </p>
      </div>

      <div class="d-flex align-center ga-2 flex-wrap">
        <VBtn
          v-if="isInstructor && course"
          variant="tonal"
          size="small"
          prepend-icon="mdi-arrow-left"
          @click="router.visit(`/instructor/courses/${course.id}`)"
        >
          Kembali ke Kursus
        </VBtn>

        <VBtn
          color="primary"
          variant="elevated"
          size="default"
          prepend-icon="mdi-plus"
          :href="route('discussions.create', { course_id: course?.id })"
        >
          Ajukan Pertanyaan Baru
        </VBtn>
      </div>
    </div>

    <!-- Quick Stats Metric Cards -->
    <VRow class="mb-5">
      <VCol cols="12" sm="4">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Total Diskusi</span>
              <span class="text-h5 font-weight-bold">{{ stats.total || discussions.total || 0 }}</span>
            </div>
            <VAvatar color="primary" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-forum" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Solusi Terverifikasi</span>
              <span class="text-h5 font-weight-bold text-success">{{ stats.resolved || 0 }}</span>
            </div>
            <VAvatar color="success" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-check-decagram-outline" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard flat border class="pa-3 h-100 stat-card">
          <div class="d-flex align-center justify-space-between">
            <div>
              <span class="text-caption font-weight-medium text-medium-emphasis d-block">Menunggu Jawaban</span>
              <span class="text-h5 font-weight-bold text-warning">{{ stats.unresolved || 0 }}</span>
            </div>
            <VAvatar color="warning" variant="tonal" size="36" rounded>
              <VIcon icon="mdi-help-circle-outline" size="20" />
            </VAvatar>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Filter & Search Toolbar Card -->
    <VCard flat border class="pa-4 mb-5">
      <VRow align="center">
        <!-- Search input -->
        <VCol cols="12" md="5">
          <VTextField
            v-model="search"
            placeholder="Cari topik diskusi atau pertanyaan..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            clearable
            hide-details
            @keydown.enter="applyFilters"
            @click:clear="search = ''; applyFilters()"
          />
        </VCol>

        <!-- Scope & Status Select Dropdowns -->
        <VCol cols="12" md="7" class="d-flex align-center justify-md-end flex-wrap ga-2">
          <!-- Scope Filter -->
          <VSelect
            v-model="selectedType"
            :items="[
              { title: 'Semua Diskusi', value: 'all' },
              { title: 'Q&A Umum (Non-Kursus)', value: 'general' },
              { title: 'Terkait Kursus', value: 'course' },
            ]"
            item-title="title"
            item-value="value"
            variant="outlined"
            density="compact"
            prepend-inner-icon="mdi-forum-outline"
            hide-details
            style="min-width: 190px; max-width: 220px;"
            class="flex-grow-1 flex-md-grow-0"
            @update:model-value="applyFilters"
          />

          <!-- Status Filter -->
          <VSelect
            v-model="selectedStatus"
            :items="[
              { title: 'Semua Status', value: 'all' },
              { title: 'Solusi Terverifikasi', value: 'resolved' },
              { title: 'Belum Selesai', value: 'unresolved' },
            ]"
            item-title="title"
            item-value="value"
            variant="outlined"
            density="compact"
            prepend-inner-icon="mdi-check-decagram-outline"
            hide-details
            style="min-width: 190px; max-width: 220px;"
            class="flex-grow-1 flex-md-grow-0"
            @update:model-value="applyFilters"
          />
        </VCol>
      </VRow>

      <!-- Category Filter Chips if available -->
      <div v-if="categories.length > 0" class="d-flex align-center flex-wrap ga-2 mt-3 pt-3 border-t">
        <span class="text-caption font-weight-bold text-medium-emphasis me-1">
          <VIcon icon="mdi-tag-outline" size="14" class="me-1" />
          Kategori:
        </span>
        <VChip
          :color="selectedCategory === null ? 'primary' : 'default'"
          :variant="selectedCategory === null ? 'elevated' : 'outlined'"
          size="x-small"
          class="cursor-pointer"
          @click="setCategoryFilter(null)"
        >
          Semua
        </VChip>
        <VChip
          v-for="cat in categories"
          :key="cat.id"
          :color="selectedCategory === cat.id ? 'primary' : 'default'"
          :variant="selectedCategory === cat.id ? 'elevated' : 'outlined'"
          size="x-small"
          class="cursor-pointer"
          @click="setCategoryFilter(cat.id)"
        >
          {{ cat.name }}
        </VChip>
      </div>
    </VCard>

    <!-- Discussion Cards List -->
    <div v-if="discussions.data && discussions.data.length" class="d-flex flex-column ga-3">
      <VCard
        v-for="disc in discussions.data"
        :key="disc.id"
        flat
        border
        hover
        :class="{
          'pinned-card': disc.is_pinned,
          'resolved-card': disc.is_resolved
        }"
        :href="route('discussions.show', disc.id)"
        class="text-decoration-none discussion-card rounded-lg"
      >
        <VCardText class="d-flex align-start ga-3 py-3 px-4">
          <!-- Author Avatar -->
          <VAvatar
            size="38"
            :color="disc.creator?.avatar_url ? undefined : 'primary'"
            variant="tonal"
            class="mt-1 flex-shrink-0"
          >
            <VImg v-if="disc.creator?.avatar_url" :src="disc.creator.avatar_url" />
            <span v-else class="text-uppercase font-weight-bold text-caption">
              {{ disc.creator?.name?.[0] }}
            </span>
          </VAvatar>

          <!-- Middle Content Column -->
          <div class="flex-grow-1" style="min-width: 0;">
            <!-- Badges Bar -->
            <div class="d-flex align-center ga-2 flex-wrap mb-1">
              <!-- Pin Badge -->
              <VChip
                v-if="disc.is_pinned"
                color="primary"
                size="x-small"
                variant="flat"
                prepend-icon="mdi-pin"
                class="font-weight-semibold"
              >
                Disematkan
              </VChip>

              <!-- Solution Badge -->
              <VChip
                v-if="disc.is_resolved"
                color="success"
                size="x-small"
                variant="elevated"
                prepend-icon="mdi-check-decagram"
                class="font-weight-bold"
              >
                Solusi Terverifikasi
              </VChip>

              <!-- Scope Badge: Q&A Umum vs Course -->
              <VChip
                v-if="!disc.course_id"
                color="teal"
                size="x-small"
                variant="tonal"
                prepend-icon="mdi-earth"
              >
                Q&A Umum
              </VChip>
              <VChip
                v-else
                color="primary"
                size="x-small"
                variant="tonal"
                prepend-icon="mdi-book-open-outline"
                class="text-truncate"
                style="max-width: 200px;"
              >
                {{ disc.course?.title }}
              </VChip>

              <!-- Category Badge -->
              <VChip
                v-if="disc.category"
                size="x-small"
                variant="outlined"
              >
                {{ disc.category.name }}
              </VChip>
            </div>

            <!-- Title -->
            <h6 class="text-subtitle-1 font-weight-bold disc-title mb-1 text-truncate" :title="disc.title">
              {{ disc.title }}
            </h6>

            <!-- Content preview (stripped of HTML tags) -->
            <p class="text-caption text-medium-emphasis line-clamp-2 mb-2">
              {{ stripHtml(disc.content) || 'Tanpa rincian isi.' }}
            </p>

            <!-- Meta info footer -->
            <div class="text-caption text-medium-emphasis d-flex align-center ga-2 flex-wrap">
              <span class="font-weight-medium text-high-emphasis">
                {{ disc.creator?.name }}
              </span>
              <span>&bull;</span>
              <span>
                {{ new Date(disc.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
              </span>
              <span v-if="disc.lesson" class="text-truncate" style="max-width: 250px;">
                &bull; Pelajaran: {{ disc.lesson?.title }}
              </span>
            </div>
          </div>

          <!-- Right Action & Counter -->
          <div class="d-flex flex-column align-end ga-1 flex-shrink-0">
            <div
              class="text-center pa-2 rounded replies-counter-box"
              :class="{
                'bg-success-light text-success': disc.is_resolved,
                'bg-surface-variant text-medium-emphasis': !disc.is_resolved && !disc.replies_count,
                'bg-primary-light text-primary': !disc.is_resolved && disc.replies_count > 0,
              }"
              style="min-width: 68px;"
            >
              <div class="text-subtitle-2 font-weight-bold line-height-1">
                {{ disc.replies_count ?? 0 }}
              </div>
              <div class="text-caption" style="font-size: 0.68rem !important;">
                jawaban
              </div>
            </div>

            <VBtn
              v-if="canPinDiscussion"
              :color="disc.is_pinned ? 'primary' : 'secondary'"
              variant="text"
              size="x-small"
              density="compact"
              :icon="disc.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'"
              :title="disc.is_pinned ? 'Lepas Sematan' : 'Sematkan Diskusi'"
              @click.prevent.stop="togglePin(disc)"
            />
          </div>
        </VCardText>
      </VCard>
    </div>

    <!-- Empty State -->
    <VCard v-else flat border class="pa-12 text-center mt-3">
      <VAvatar size="64" color="primary" variant="tonal" class="mb-3">
        <VIcon icon="mdi-comment-question-outline" size="36" />
      </VAvatar>
      <h6 class="text-subtitle-1 font-weight-bold mb-1">Belum Ada Topik Diskusi Ditemukan</h6>
      <p class="text-caption text-medium-emphasis mb-4 mx-auto" style="max-width: 440px;">
        Jadilah yang pertama mengajukan pertanyaan, berbagi pengalaman lapangan, atau mendiskusikan materi pelajaran.
      </p>
      <VBtn
        color="primary"
        variant="elevated"
        size="small"
        prepend-icon="mdi-plus"
        :href="route('discussions.create', { course_id: course?.id })"
      >
        Ajukan Pertanyaan Baru
      </VBtn>
    </VCard>

    <!-- Pagination -->
    <div v-if="discussions.last_page > 1" class="d-flex align-center justify-center pa-4 mt-4">
      <VPagination
        :model-value="discussions.current_page"
        :length="discussions.last_page"
        :total-visible="5"
        density="compact"
        size="small"
        @update:model-value="(p) => router.get(route('discussions.index', {
          course_id: course?.id,
          page: p,
          type: selectedType !== 'all' ? selectedType : undefined,
          status: selectedStatus !== 'all' ? selectedStatus : undefined,
          category_id: selectedCategory || undefined,
          search: search || undefined,
        }), {}, { preserveState: true })"
      />
    </div>
  </div>
</template>

<style scoped>
.stat-card {
  transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.discussion-card {
  transition: transform 0.18s ease-in-out, box-shadow 0.18s ease-in-out;
}
.discussion-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07) !important;
}

.disc-title {
  transition: color 0.15s ease-in-out;
}
.discussion-card:hover .disc-title {
  color: rgb(var(--v-theme-primary)) !important;
}

.pinned-card {
  border-color: rgba(var(--v-theme-primary), 0.4) !important;
}

.resolved-card {
  border-inline-start: 4px solid #10b981 !important;
}

.replies-counter-box {
  transition: all 0.15s ease-in-out;
}

.bg-success-light {
  background-color: rgba(16, 185, 129, 0.12);
}

.bg-primary-light {
  background-color: rgba(var(--v-theme-primary), 0.12);
}

.cursor-pointer {
  cursor: pointer;
}

.border-t {
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
