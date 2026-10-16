<script setup>
import { ref, computed } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  articles: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  tags: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  canCreate: { type: Boolean, default: false },
  isSme: { type: Boolean, default: false },
  pendingReviewCount: { type: Number, default: 0 },
})

const page = usePage()
const search = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category_id ? Number(props.filters.category_id) : null)
const selectedTag = ref(props.filters.tag_id ? Number(props.filters.tag_id) : null)
const selectedStatus = ref(props.filters.status || '')

const applyFilters = () => {
  router.get(route('articles.index'), {
    search: search.value || undefined,
    category_id: selectedCategory.value || undefined,
    tag_id: selectedTag.value || undefined,
    status: selectedStatus.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  search.value = ''
  selectedCategory.value = null
  selectedTag.value = null
  selectedStatus.value = ''
  applyFilters()
}

const resetSearch = () => {
  search.value = ''
  applyFilters()
}

const filterByCategory = (catId) => {
  selectedCategory.value = selectedCategory.value === catId ? null : catId
  applyFilters()
}

const filterByTag = (tagId) => {
  selectedTag.value = selectedTag.value === tagId ? null : tagId
  applyFilters()
}

const filterByStatus = (status) => {
  selectedStatus.value = selectedStatus.value === status ? '' : status
  applyFilters()
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const userRoles = computed(() => page.props.auth?.user?.roles ?? [])
const isStaff = computed(() => {
  return userRoles.value.some(r => ['superadmin', 'tenant_admin', 'instructor'].includes(r))
})
</script>

<template>
  <div class="knowledge-base-page pb-8">
    <!-- Premium Sleek Hero Header Card -->
    <VCard
      rounded="xl"
      elevation="0"
      border
      class="pa-5 mb-4 hero-kb-card position-relative overflow-hidden"
    >
      <div class="hero-glow" />
      <div class="d-flex align-center justify-space-between flex-wrap ga-4 position-relative">
        <div class="d-flex align-center ga-3">
          <div class="header-icon-box rounded-lg d-flex align-center justify-center">
            <VIcon icon="mdi-bookshelf" size="26" color="primary" />
          </div>
          <div>
            <div class="d-flex align-center ga-2 mb-1 flex-wrap">
              <h5 class="text-h6 font-weight-bold mb-0 text-high-emphasis">
                Pusat Pengetahuan & SOP Terstandar
              </h5>
              <VChip size="x-small" color="primary" variant="tonal" class="font-weight-medium">
                Knowledge Base & SOP
              </VChip>
            </div>
            <p class="text-caption text-medium-emphasis mb-0" style="max-width: 680px;">
              Dokumentasi operasional, panduan kerja, dan referensi pengetahuan yang terkelola dengan kontrol versi dan validasi Subject Matter Expert (SME).
            </p>
          </div>
        </div>

        <div class="d-flex align-center ga-2">
          <VBtn
            v-if="canCreate"
            color="primary"
            variant="elevated"
            size="small"
            rounded="lg"
            prepend-icon="mdi-plus"
            :href="route('articles.create')"
            class="font-weight-medium px-4"
          >
            Tulis Dokumen / SOP Baru
          </VBtn>
        </div>
      </div>

      <!-- Integrated Sleek Search Bar -->
      <div class="mt-4 pt-3 border-t d-flex align-center flex-wrap ga-2 position-relative">
        <div class="flex-grow-1" style="min-width: 280px;">
          <VTextField
            v-model="search"
            placeholder="Cari artikel, SOP, petunjuk teknis, atau kata kunci..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            rounded="lg"
            class="search-input"
            clearable
            @keydown.enter="applyFilters"
            @click:clear="resetSearch"
          />
        </div>
        <VBtn
          color="primary"
          variant="tonal"
          size="small"
          rounded="lg"
          class="px-4 font-weight-medium"
          prepend-icon="mdi-magnify"
          @click="applyFilters"
        >
          Cari
        </VBtn>
        <VBtn
          v-if="search"
          variant="text"
          color="secondary"
          size="small"
          rounded="lg"
          @click="resetSearch"
        >
          Reset
        </VBtn>
      </div>
    </VCard>

    <!-- SME Review Queue Alert (Visible for SMEs when there are pending articles) -->
    <VAlert
      v-if="isSme && pendingReviewCount > 0"
      type="warning"
      variant="tonal"
      density="compact"
      rounded="lg"
      class="mb-4 border border-warning"
      prepend-icon="mdi-clipboard-text-clock-outline"
    >
      <div class="d-flex align-center justify-space-between flex-wrap ga-3">
        <div class="text-caption">
          <strong>Perhatian SME:</strong> Terdapat <strong>{{ pendingReviewCount }}</strong> dokumen baru/revisi yang sedang menunggu peninjauan dan persetujuan dari Anda.
        </div>
        <VBtn
          color="warning"
          variant="elevated"
          size="x-small"
          rounded="lg"
          class="font-weight-medium"
          @click="filterByStatus('pending_review')"
        >
          Lihat Antrean Review
        </VBtn>
      </div>
    </VAlert>

    <!-- Category & Governance Quick Filters -->
    <VCard
      rounded="xl"
      elevation="0"
      border
      class="pa-3 px-4 mb-4"
    >
      <div class="d-flex align-center justify-space-between flex-wrap ga-3">
        <!-- Categories -->
        <div class="d-flex align-center flex-wrap ga-1">
          <span class="text-caption font-weight-bold text-medium-emphasis me-1" style="font-size: 0.75rem;">
            Kategori:
          </span>
          <VChip
            :color="selectedCategory === null ? 'primary' : 'default'"
            :variant="selectedCategory === null ? 'flat' : 'tonal'"
            size="small"
            rounded="lg"
            class="cursor-pointer font-weight-medium"
            @click="filterByCategory(null)"
          >
            Semua
          </VChip>
          <VChip
            v-for="cat in categories"
            :key="cat.id"
            :color="selectedCategory === cat.id ? 'primary' : 'default'"
            :variant="selectedCategory === cat.id ? 'flat' : 'tonal'"
            size="small"
            rounded="lg"
            class="cursor-pointer"
            @click="filterByCategory(cat.id)"
          >
            {{ cat.name }} ({{ cat.articles_count || 0 }})
          </VChip>
        </div>

        <!-- Status Filter for Staff & SME -->
        <div v-if="isStaff" class="d-flex align-center ga-2">
          <VSelect
            v-model="selectedStatus"
            :items="[
              { title: 'Semua Status Dokumen', value: '' },
              { title: 'Resmi Dipublikasikan (Live)', value: 'published' },
              { title: `Menunggu Review SME ${pendingReviewCount > 0 ? `(${pendingReviewCount})` : ''}`, value: 'pending_review' },
              { title: 'Perlu Revisi Penulis', value: 'rejected' },
              { title: 'Draft Saya', value: 'draft' },
              { title: 'Diarsipkan', value: 'archived' },
            ]"
            item-title="title"
            item-value="value"
            label="Tata Kelola Status"
            variant="outlined"
            density="compact"
            hide-details
            style="min-width: 200px;"
            @update:model-value="applyFilters"
          />
          <VBtn
            v-if="search || selectedCategory || selectedTag || selectedStatus"
            variant="text"
            color="error"
            size="small"
            density="compact"
            prepend-icon="mdi-filter-off-outline"
            @click="resetFilters"
          >
            Reset
          </VBtn>
        </div>
      </div>

      <!-- Tag Filter row if any tags -->
      <div v-if="tags.length > 0" class="d-flex align-center flex-wrap ga-1 mt-2 pt-2 border-t">
        <span class="text-caption font-weight-bold text-medium-emphasis me-2" style="font-size: 0.72rem;">
          Tag Topik:
        </span>
        <VChip
          v-for="tag in tags"
          :key="tag.id"
          :color="selectedTag === tag.id ? 'primary' : 'default'"
          :variant="selectedTag === tag.id ? 'flat' : 'text'"
          size="x-small"
          rounded="sm"
          class="cursor-pointer"
          @click="filterByTag(tag.id)"
        >
          #{{ tag.name }}
        </VChip>
      </div>
    </VCard>

    <!-- Articles Grid -->
    <div v-if="articles.data && articles.data.length > 0">
      <VRow dense>
        <VCol
          v-for="article in articles.data"
          :key="article.id"
          cols="12"
          md="6"
          lg="4"
        >
          <VCard
            rounded="xl"
            elevation="0"
            border
            :class="{
              'border-warning border-2': article.status === 'pending_review',
              'border-error border-2': article.status === 'rejected',
            }"
            class="h-100 d-flex flex-column article-card transition-all overflow-hidden"
          >
            <div class="pa-4 pb-2">
              <!-- Top Badges: Category & Version -->
              <div class="d-flex align-center justify-space-between mb-2">
                <div class="d-flex align-center ga-1">
                  <VChip
                    v-if="article.category"
                    size="x-small"
                    color="primary"
                    variant="tonal"
                    rounded="sm"
                    class="font-weight-medium"
                  >
                    {{ article.category.name }}
                  </VChip>
                  <VChip size="x-small" variant="tonal" color="secondary" rounded="sm" class="font-weight-bold">
                    v{{ article.version || '1.0' }}
                  </VChip>
                </div>

                <div class="d-flex align-center ga-1">
                  <!-- Governance Status Badges -->
                  <VChip
                    v-if="article.status === 'pending_review'"
                    size="x-small"
                    color="warning"
                    variant="flat"
                    prepend-icon="mdi-clock-time-four-outline"
                    class="font-weight-bold"
                  >
                    Review SME
                  </VChip>
                  <VChip
                    v-else-if="article.status === 'rejected'"
                    size="x-small"
                    color="error"
                    variant="flat"
                    prepend-icon="mdi-alert-circle-outline"
                    class="font-weight-bold"
                  >
                    Perlu Revisi
                  </VChip>
                  <VChip
                    v-else-if="article.status === 'draft'"
                    size="x-small"
                    color="secondary"
                    variant="tonal"
                  >
                    Draft
                  </VChip>
                  <VChip
                    v-else-if="article.status === 'archived'"
                    size="x-small"
                    color="default"
                    variant="tonal"
                  >
                    Arsip
                  </VChip>

                  <!-- Attachments Chip -->
                  <VChip
                    v-if="article.attachments_count > 0"
                    size="x-small"
                    color="info"
                    variant="tonal"
                    prepend-icon="mdi-paperclip"
                  >
                    {{ article.attachments_count }}
                  </VChip>
                </div>
              </div>

              <!-- Title -->
              <h6 class="text-subtitle-1 font-weight-bold px-0 text-wrap line-clamp-2 mb-1">
                <Link
                  :href="route('articles.show', article.id)"
                  class="text-decoration-none text-high-emphasis article-title-link"
                >
                  {{ article.title }}
                </Link>
              </h6>
            </div>

            <div class="px-4 flex-grow-1">
              <p class="text-caption text-medium-emphasis line-clamp-3 mb-2" style="font-size: 0.78rem; line-height: 1.45;">
                {{ article.excerpt || 'Klik untuk membaca selengkapnya mengenai panduan dan dokumentasi resmi ini...' }}
              </p>

              <!-- Tags list -->
              <div v-if="article.tags && article.tags.length > 0" class="d-flex flex-wrap ga-1 mb-2">
                <VChip
                  v-for="tag in article.tags.slice(0, 3)"
                  :key="tag.id"
                  size="x-small"
                  variant="outlined"
                  class="text-disabled"
                >
                  #{{ tag.name }}
                </VChip>
                <span v-if="article.tags.length > 3" class="text-caption text-disabled align-self-center" style="font-size: 0.68rem;">
                  +{{ article.tags.length - 3 }}
                </span>
              </div>
            </div>

            <VDivider />

            <!-- Card Footer: Author & SME Stamp -->
            <div class="pa-3 px-4 d-flex align-center justify-space-between text-caption text-medium-emphasis">
              <div class="d-flex align-center ga-2">
                <VAvatar size="22" color="primary" variant="tonal">
                  <VImg v-if="article.author?.avatar_url" :src="article.author.avatar_url" />
                  <span v-else class="text-caption font-weight-bold" style="font-size: 0.65rem;">
                    {{ article.author?.name?.[0] || 'A' }}
                  </span>
                </VAvatar>
                <span class="text-truncate font-weight-medium" style="max-width: 110px; font-size: 0.75rem;">
                  {{ article.author?.name || 'Kontributor' }}
                </span>
              </div>

              <!-- Right metadata: SME Verified -->
              <div class="d-flex align-center ga-2">
                <span v-if="article.reviewer" title="Diverifikasi oleh SME" class="d-flex align-center text-success font-weight-medium" style="font-size: 0.72rem;">
                  <VIcon icon="mdi-shield-check" size="14" class="me-1" />
                  SME Verified
                </span>
                <span class="text-disabled" style="font-size: 0.7rem;">
                  {{ formatDate(article.created_at) }}
                </span>
              </div>
            </div>
          </VCard>
        </VCol>
      </VRow>

      <!-- Pagination -->
      <div v-if="articles.links && articles.links.length > 3" class="d-flex justify-center mt-6">
        <div class="d-flex ga-1 flex-wrap">
          <template v-for="(link, i) in articles.links" :key="i">
            <VBtn
              v-if="link.url"
              :variant="link.active ? 'elevated' : 'outlined'"
              :color="link.active ? 'primary' : 'default'"
              size="small"
              rounded="lg"
              :href="link.url"
              v-html="link.label"
            />
            <VBtn
              v-else
              variant="text"
              disabled
              size="small"
              rounded="lg"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <VCard v-else rounded="xl" elevation="0" border class="pa-8 text-center my-4">
      <VIcon icon="mdi-file-search-outline" size="48" color="medium-emphasis" class="mb-3" />
      <h6 class="text-subtitle-1 font-weight-bold mb-1">Belum Ada Dokumen Ditemukan</h6>
      <p class="text-caption text-medium-emphasis mb-4 mx-auto" style="max-width: 420px;">
        Tidak ada dokumen pengetahuan yang cocok dengan filter atau kata kunci pencarian Anda.
      </p>
      <div class="d-flex justify-center ga-2">
        <VBtn
          v-if="search || selectedCategory || selectedTag || selectedStatus"
          variant="outlined"
          size="small"
          rounded="lg"
          prepend-icon="mdi-refresh"
          @click="resetFilters"
        >
          Reset Filter
        </VBtn>
        <VBtn
          v-if="canCreate"
          color="primary"
          variant="elevated"
          size="small"
          rounded="lg"
          prepend-icon="mdi-plus"
          :href="route('articles.create')"
        >
          Tulis Dokumen Baru
        </VBtn>
      </div>
    </VCard>
  </div>
</template>

<style scoped>
.hero-kb-card {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, rgba(var(--v-theme-surface), 1) 100%);
}

.hero-glow {
  position: absolute;
  top: -40px;
  right: -40px;
  width: 140px;
  height: 140px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(var(--v-theme-primary), 0.12) 0%, transparent 70%);
  pointer-events: none;
}

.header-icon-box {
  width: 44px;
  height: 44px;
  background-color: rgba(var(--v-theme-primary), 0.1);
  border: 1px solid rgba(var(--v-theme-primary), 0.2);
  flex-shrink: 0;
}

.article-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.article-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06) !important;
}

.article-title-link:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
