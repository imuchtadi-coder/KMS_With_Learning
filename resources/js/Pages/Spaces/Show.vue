<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  space: { type: Object, required: true },
  articleTree: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({ total: 0, published: 0, needs_review: 0 }) },
  canManage: { type: Boolean, default: false },
})

const expandedNodes = ref(props.articleTree.map(a => a.id))

const toggleExpand = (id) => {
  if (expandedNodes.value.includes(id)) {
    expandedNodes.value = expandedNodes.value.filter(item => item !== id)
  } else {
    expandedNodes.value.push(id)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
</script>

<template>
  <div class="space-show-page pb-8">
    <!-- Breadcrumbs -->
    <VBreadcrumbs
      :items="[
        { title: 'Knowledge Base', href: route('articles.index') },
        { title: 'Knowledge Spaces', href: route('spaces.index') },
        { title: space.name, disabled: true },
      ]"
      density="compact"
      class="pa-0 mb-3 text-caption"
    >
      <template #item="{ item }">
        <Link
          v-if="item.href && !item.disabled"
          :href="item.href"
          class="text-decoration-none text-medium-emphasis hover-primary"
        >
          {{ item.title }}
        </Link>
        <span
          v-else
          :class="item.disabled ? 'text-high-emphasis font-weight-medium' : 'text-medium-emphasis'"
        >
          {{ item.title }}
        </span>
      </template>
    </VBreadcrumbs>

    <!-- Space Banner Header -->
    <VCard
      rounded="xl"
      elevation="0"
      border
      class="pa-6 mb-5 space-header-card position-relative overflow-hidden"
    >
      <div class="space-accent-glow" :style="{ backgroundColor: (space.color || '#4F46E5') + '15' }" />
      
      <div class="d-flex align-start justify-space-between flex-wrap ga-4 position-relative">
        <div class="d-flex align-start ga-4">
          <div
            class="space-header-icon-box rounded-xl d-flex align-center justify-center flex-shrink-0"
            :style="{ backgroundColor: (space.color || '#4F46E5') + '20', color: space.color || '#4F46E5' }"
          >
            <VIcon :icon="space.icon || 'mdi-folder-outline'" size="36" />
          </div>

          <div>
            <div class="d-flex align-center ga-2 mb-1 flex-wrap">
              <h4 class="text-h5 font-weight-bold mb-0 text-high-emphasis">
                {{ space.name }}
              </h4>
              <VChip
                size="x-small"
                :color="space.visibility === 'public' ? 'success' : space.visibility === 'internal' ? 'primary' : 'warning'"
                variant="tonal"
                class="font-weight-medium text-capitalize"
              >
                {{ space.visibility }}
              </VChip>
            </div>

            <p class="text-body-2 text-medium-emphasis mb-3" style="max-width: 720px;">
              {{ space.description || 'Tidak ada deskripsi detail untuk ruang kerja ini.' }}
            </p>

            <div class="d-flex align-center flex-wrap ga-3 text-caption text-medium-emphasis">
              <div v-if="space.manager" class="d-flex align-center ga-1">
                <VIcon icon="mdi-account-tie" size="16" color="primary" />
                <span>Penanggung Jawab: <strong>{{ space.manager.name }}</strong></span>
              </div>
              <div class="d-flex align-center ga-1">
                <VIcon icon="mdi-clock-outline" size="16" />
                <span>Dibuat: {{ formatDate(space.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex align-center ga-2">
          <VBtn
            :href="route('articles.create', { space_id: space.id })"
            color="primary"
            variant="elevated"
            size="small"
            rounded="lg"
            prepend-icon="mdi-plus"
            class="font-weight-medium px-4"
          >
            Tulis Dokumen di Space Ini
          </VBtn>
        </div>
      </div>
    </VCard>

    <!-- Space Stats Strip -->
    <VRow class="mb-4">
      <VCol cols="12" sm="4">
        <VCard rounded="xl" elevation="0" border class="pa-4 text-center">
          <div class="text-caption text-medium-emphasis mb-1">Total Dokumen / SOP</div>
          <div class="text-h5 font-weight-bold text-high-emphasis">{{ stats.total }}</div>
        </VCard>
      </VCol>
      <VCol cols="12" sm="4">
        <VCard rounded="xl" elevation="0" border class="pa-4 text-center">
          <div class="text-caption text-medium-emphasis mb-1">Terpublikasi Resmi</div>
          <div class="text-h5 font-weight-bold text-success">{{ stats.published }}</div>
        </VCard>
      </VCol>
      <VCol cols="12" sm="4">
        <VCard rounded="xl" elevation="0" border class="pa-4 text-center">
          <div class="text-caption text-medium-emphasis mb-1">Perlu Ditinjau / Review Cycle</div>
          <div class="text-h5 font-weight-bold text-warning">{{ stats.needs_review }}</div>
        </VCard>
      </VCol>
    </VRow>

    <!-- Tree Hierarchy View (Notion / Confluence Tree) -->
    <VCard rounded="xl" elevation="0" border class="pa-5">
      <div class="d-flex align-center justify-space-between mb-4 pb-3 border-b flex-wrap ga-2">
        <div class="d-flex align-center ga-2">
          <VIcon icon="mdi-file-tree-outline" size="20" color="primary" />
          <h6 class="text-subtitle-1 font-weight-bold mb-0">
            Hierarki Pohon Dokumen & Prosedur
          </h6>
        </div>
        <div class="text-caption text-medium-emphasis">
          Struktur bertingkat (SOP Induk & Sub-Prosedur)
        </div>
      </div>

      <div v-if="articleTree.length > 0" class="article-tree-container">
        <div v-for="rootDoc in articleTree" :key="rootDoc.id" class="tree-root-item mb-3">
          <!-- Root Level Document Row -->
          <div class="tree-node-row pa-3 rounded-lg d-flex align-center justify-space-between flex-wrap ga-2 border">
            <div class="d-flex align-center ga-2 flex-grow-1">
              <VBtn
                v-if="rootDoc.children && rootDoc.children.length > 0"
                :icon="expandedNodes.includes(rootDoc.id) ? 'mdi-chevron-down' : 'mdi-chevron-right'"
                variant="text"
                density="compact"
                size="small"
                @click="toggleExpand(rootDoc.id)"
              />
              <VIcon v-else icon="mdi-file-document-outline" size="18" color="primary" class="me-1 ms-1" />

              <div>
                <Link
                  :href="route('articles.show', rootDoc.id)"
                  class="font-weight-bold text-decoration-none text-high-emphasis hover-primary"
                >
                  {{ rootDoc.title }}
                </Link>
                <div class="d-flex align-center ga-2 mt-1">
                  <VChip size="x-small" variant="tonal" color="secondary">
                    v{{ rootDoc.version || '1.0' }}
                  </VChip>
                  <VChip
                    size="x-small"
                    :color="rootDoc.status === 'published' ? 'success' : 'warning'"
                    variant="tonal"
                  >
                    {{ rootDoc.status }}
                  </VChip>
                  <span v-if="rootDoc.author" class="text-caption text-medium-emphasis">
                    Oleh {{ rootDoc.author.name }}
                  </span>
                </div>
              </div>
            </div>

            <div class="d-flex align-center ga-2">
              <VBtn
                :href="route('articles.create', { space_id: space.id, parent_id: rootDoc.id })"
                variant="tonal"
                size="x-small"
                rounded="lg"
                prepend-icon="mdi-plus"
                title="Tambah Sub-Dokumen"
              >
                Sub-Dokumen
              </VBtn>
              <VBtn
                :href="route('articles.show', rootDoc.id)"
                variant="text"
                size="small"
                icon="mdi-arrow-right"
              />
            </div>
          </div>

          <!-- Nested Children Documents -->
          <div
            v-if="expandedNodes.includes(rootDoc.id) && rootDoc.children && rootDoc.children.length > 0"
            class="nested-tree-branch ms-6 mt-2 ps-3 border-s"
          >
            <div
              v-for="subDoc in rootDoc.children"
              :key="subDoc.id"
              class="tree-node-row pa-2 px-3 rounded-lg d-flex align-center justify-space-between mb-2 border"
            >
              <div class="d-flex align-center ga-2">
                <VIcon icon="mdi-subdirectory-arrow-right" size="18" color="secondary" />
                <div>
                  <Link
                    :href="route('articles.show', subDoc.id)"
                    class="font-weight-medium text-decoration-none text-high-emphasis hover-primary"
                  >
                    {{ subDoc.title }}
                  </Link>
                  <div class="d-flex align-center ga-2 mt-1">
                    <VChip size="x-small" variant="tonal" color="secondary">
                      v{{ subDoc.version || '1.0' }}
                    </VChip>
                    <VChip
                      size="x-small"
                      :color="subDoc.status === 'published' ? 'success' : 'warning'"
                      variant="tonal"
                    >
                      {{ subDoc.status }}
                    </VChip>
                  </div>
                </div>
              </div>

              <VBtn
                :href="route('articles.show', subDoc.id)"
                variant="text"
                size="small"
                icon="mdi-arrow-right"
              />
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center pa-8 text-medium-emphasis">
        <VIcon icon="mdi-file-document-plus-outline" size="48" class="mb-2 opacity-50" />
        <p class="text-body-2 mb-3">Belum ada dokumen yang dibuat di dalam Knowledge Space ini.</p>
        <VBtn
          :href="route('articles.create', { space_id: space.id })"
          color="primary"
          variant="tonal"
          size="small"
          rounded="lg"
          prepend-icon="mdi-plus"
        >
          Tulis Dokumen Pertama
        </VBtn>
      </div>
    </VCard>
  </div>
</template>

<style scoped>
.space-header-card {
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.98) 0%, rgba(var(--v-theme-surface), 0.95) 100%);
}

.space-accent-glow {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 250px;
  height: 250px;
  border-radius: 50%;
  filter: blur(40px);
  pointer-events: none;
}

.space-header-icon-box {
  width: 64px;
  height: 64px;
}

.tree-node-row {
  background-color: rgba(var(--v-theme-surface), 1);
  transition: all 0.2s ease;
}

.tree-node-row:hover {
  background-color: rgba(var(--v-theme-primary), 0.03);
  border-color: rgba(var(--v-theme-primary), 0.3) !important;
}

.hover-primary:hover {
  color: rgb(var(--v-theme-primary)) !important;
}

.nested-tree-branch {
  border-color: rgba(var(--v-theme-primary), 0.2) !important;
}
</style>
