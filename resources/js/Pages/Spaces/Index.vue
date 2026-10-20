<script setup>
import { ref } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  spaces: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
  managers: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters.search || '')
const isCreateDialogOpen = ref(false)

const form = useForm({
  name: '',
  description: '',
  icon: 'mdi-folder-outline',
  color: '#4F46E5',
  visibility: 'internal',
  manager_id: null,
})

const availableIcons = [
  'mdi-folder-outline',
  'mdi-file-document-multiple-outline',
  'mdi-server-network',
  'mdi-account-group-outline',
  'mdi-shield-check-outline',
  'mdi-cogs',
  'mdi-chart-bar',
  'mdi-scale-balance',
  'mdi-leaf',
  'mdi-laptop',
]

const availableColors = [
  '#4F46E5', // Indigo
  '#059669', // Emerald
  '#2563EB', // Blue
  '#7C3AED', // Purple
  '#EA580C', // Orange
  '#DC2626', // Red
  '#0891B2', // Cyan
  '#4B5563', // Slate
]

const applySearch = () => {
  router.get(route('spaces.index'), {
    search: search.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const submitCreateSpace = () => {
  form.post(route('spaces.store'), {
    onSuccess: () => {
      isCreateDialogOpen.value = false
      form.reset()
    },
  })
}
</script>

<template>
  <div class="spaces-page pb-8">
    <!-- Hero Header -->
    <VCard
      rounded="xl"
      elevation="0"
      border
      class="pa-5 mb-5 hero-spaces-card position-relative overflow-hidden"
    >
      <div class="d-flex align-center justify-space-between flex-wrap ga-4 position-relative">
        <div class="d-flex align-center ga-3">
          <div class="header-icon-box rounded-lg d-flex align-center justify-center">
            <VIcon icon="mdi-folder-multiple-outline" size="28" color="primary" />
          </div>
          <div>
            <div class="d-flex align-center ga-2 mb-1 flex-wrap">
              <h5 class="text-h6 font-weight-bold mb-0 text-high-emphasis">
                Knowledge Spaces Enterprise
              </h5>
              <VChip size="x-small" color="primary" variant="tonal" class="font-weight-medium">
                Confluence-Style Repositories
              </VChip>
            </div>
            <p class="text-caption text-medium-emphasis mb-0" style="max-width: 680px;">
              Ruang kerja dokumentasi terisolasi per divisi, proyek, atau domain operasional. Kelola SOP, arsitektur teknis, dan kebijakan tim dengan kontrol akses dan hak kurasi terpusat.
            </p>
          </div>
        </div>

        <div class="d-flex align-center ga-2">
          <VBtn
            v-if="canManage"
            color="primary"
            variant="elevated"
            size="small"
            rounded="lg"
            prepend-icon="mdi-plus"
            class="font-weight-medium px-4"
            @click="isCreateDialogOpen = true"
          >
            Buat Space Baru
          </VBtn>
        </div>
      </div>

      <!-- Quick Search Bar -->
      <div class="mt-4 pt-3 border-t d-flex align-center flex-wrap ga-2">
        <div class="flex-grow-1" style="min-width: 260px;">
          <VTextField
            v-model="search"
            placeholder="Cari ruang kerja/space berdasarkan nama atau deskripsi..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            rounded="lg"
            clearable
            @keydown.enter="applySearch"
            @click:clear="applySearch"
          />
        </div>
        <VBtn
          color="primary"
          variant="tonal"
          size="small"
          rounded="lg"
          class="px-4 font-weight-medium"
          @click="applySearch"
        >
          Cari Space
        </VBtn>
      </div>
    </VCard>

    <!-- Spaces Grid -->
    <div v-if="spaces.length > 0" class="spaces-grid">
      <VCard
        v-for="space in spaces"
        :key="space.id"
        rounded="xl"
        elevation="0"
        border
        class="space-card d-flex flex-column transition-swing"
      >
        <div class="space-card-top-bar" :style="{ backgroundColor: space.color || '#4F46E5' }" />
        
        <VCardItem class="pa-5 flex-grow-1">
          <div class="d-flex align-start justify-space-between ga-2 mb-3">
            <div
              class="space-icon-wrapper rounded-lg d-flex align-center justify-center"
              :style="{ backgroundColor: (space.color || '#4F46E5') + '15', color: space.color || '#4F46E5' }"
            >
              <VIcon :icon="space.icon || 'mdi-folder-outline'" size="28" />
            </div>
            <VChip
              size="x-small"
              :color="space.visibility === 'public' ? 'success' : space.visibility === 'internal' ? 'primary' : 'warning'"
              variant="tonal"
              class="font-weight-medium text-capitalize"
            >
              {{ space.visibility }}
            </VChip>
          </div>

          <h6 class="text-subtitle-1 font-weight-bold mb-2 line-clamp-1">
            <Link :href="route('spaces.show', space.id)" class="text-high-emphasis text-decoration-none space-title-link">
              {{ space.name }}
            </Link>
          </h6>

          <p class="text-caption text-medium-emphasis mb-4 line-clamp-2" style="min-height: 38px;">
            {{ space.description || 'Tidak ada deskripsi ruang kerja.' }}
          </p>

          <div class="space-meta-info pt-3 border-t d-flex align-center justify-space-between text-caption text-medium-emphasis">
            <Link :href="route('spaces.show', space.id)" class="d-flex align-center ga-1 text-decoration-none text-medium-emphasis space-count-link" title="Klik untuk melihat dokumen di Space ini">
              <VIcon icon="mdi-file-document-outline" size="16" color="primary" />
              <span><strong class="text-high-emphasis">{{ space.published_articles_count || 0 }}</strong> SOP / Dokumen</span>
            </Link>
            <div v-if="space.manager" class="d-flex align-center ga-1" :title="'Lead: ' + space.manager.name">
              <VIcon icon="mdi-account-tie" size="16" />
              <span class="text-truncate" style="max-width: 110px;">{{ space.manager.name }}</span>
            </div>
          </div>
        </VCardItem>

        <VCardActions class="pa-4 pt-0 d-flex ga-2">
          <VBtn
            :href="route('spaces.show', space.id)"
            color="primary"
            variant="tonal"
            size="small"
            rounded="lg"
            block
            append-icon="mdi-arrow-right"
            class="font-weight-medium"
          >
            Buka Knowledge Space
          </VBtn>
        </VCardActions>
      </VCard>
    </div>

    <!-- Empty State -->
    <VCard
      v-else
      rounded="xl"
      elevation="0"
      border
      class="pa-10 text-center"
    >
      <VIcon icon="mdi-folder-alert-outline" size="56" color="secondary" class="mb-3 opacity-60" />
      <h6 class="text-h6 font-weight-bold mb-1">Tidak ada Knowledge Space ditemukan</h6>
      <p class="text-caption text-medium-emphasis mb-4">
        Belum ada ruang kerja dokumentasi yang dibuat atau cocok dengan kata kunci pencarian Anda.
      </p>
      <VBtn
        v-if="canManage"
        color="primary"
        variant="elevated"
        size="small"
        rounded="lg"
        prepend-icon="mdi-plus"
        @click="isCreateDialogOpen = true"
      >
        Buat Space Pertama
      </VBtn>
    </VCard>

    <!-- Dialog Create Space -->
    <VDialog v-model="isCreateDialogOpen" max-width="560px" persistent>
      <VCard rounded="xl" class="pa-2">
        <VCardTitle class="pa-4 d-flex align-center justify-space-between">
          <span class="text-h6 font-weight-bold">Buat Knowledge Space Baru</span>
          <VBtn icon="mdi-close" variant="text" density="compact" @click="isCreateDialogOpen = false" />
        </VCardTitle>

        <VCardText class="pa-4 pt-0">
          <form @submit.prevent="submitCreateSpace">
            <VTextField
              v-model="form.name"
              label="Nama Knowledge Space *"
              placeholder="Misal: Operasional & SOP Lapangan"
              variant="outlined"
              density="compact"
              rounded="lg"
              class="mb-3"
              :error-messages="form.errors.name"
              required
            />

            <VTextarea
              v-model="form.description"
              label="Deskripsi Ruang Kerja"
              placeholder="Jelaskan jenis dokumentasi dan cakupan kerja dalam space ini..."
              variant="outlined"
              density="compact"
              rounded="lg"
              rows="3"
              class="mb-3"
              :error-messages="form.errors.description"
            />

            <!-- Icon & Color Picker -->
            <div class="mb-3">
              <label class="text-caption font-weight-bold mb-1 d-block">Pilih Ikon Space:</label>
              <div class="d-flex flex-wrap ga-2 mb-3">
                <VBtn
                  v-for="iconName in availableIcons"
                  :key="iconName"
                  :icon="iconName"
                  size="small"
                  :variant="form.icon === iconName ? 'elevated' : 'tonal'"
                  :color="form.icon === iconName ? 'primary' : 'default'"
                  rounded="lg"
                  @click="form.icon = iconName"
                />
              </div>

              <label class="text-caption font-weight-bold mb-1 d-block">Pilih Warna Aksen:</label>
              <div class="d-flex flex-wrap ga-2">
                <div
                  v-for="colorHex in availableColors"
                  :key="colorHex"
                  class="color-picker-dot cursor-pointer rounded-circle"
                  :style="{
                    backgroundColor: colorHex,
                    width: '28px',
                    height: '28px',
                    border: form.color === colorHex ? '3px solid #111827' : '1px solid rgba(0,0,0,0.1)'
                  }"
                  @click="form.color = colorHex"
                />
              </div>
            </div>

            <VSelect
              v-model="form.visibility"
              :items="[
                { title: 'Internal Company (Semua Pegawai)', value: 'internal' },
                { title: 'Public (Dapat Dibaca Publik)', value: 'public' },
                { title: 'Restricted (Khusus Anggota Divisi)', value: 'restricted' },
              ]"
              label="Tingkat Akses (Visibility) *"
              variant="outlined"
              density="compact"
              rounded="lg"
              class="mb-3"
            />

            <VSelect
              v-if="managers.length > 0"
              v-model="form.manager_id"
              :items="managers"
              item-title="name"
              item-value="id"
              label="Penanggung Jawab / Lead SME"
              placeholder="Pilih penanggung jawab konten..."
              variant="outlined"
              density="compact"
              rounded="lg"
              clearable
              class="mb-3"
            />
          </form>
        </VCardText>

        <VCardActions class="pa-4 pt-0 d-flex justify-end ga-2">
          <VBtn variant="text" rounded="lg" @click="isCreateDialogOpen = false">
            Batal
          </VBtn>
          <VBtn
            color="primary"
            variant="elevated"
            rounded="lg"
            :loading="form.processing"
            @click="submitCreateSpace"
          >
            Simpan & Buat Space
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.hero-spaces-card {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.05) 0%, rgba(var(--v-theme-surface), 0.98) 100%);
}

.header-icon-box {
  width: 48px;
  height: 48px;
  background-color: rgba(var(--v-theme-primary), 0.12);
}

.spaces-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.space-card {
  overflow: hidden;
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.space-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.space-card-top-bar {
  height: 5px;
  width: 100%;
}

.space-icon-wrapper {
  width: 48px;
  height: 48px;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.space-title-link:hover,
.space-count-link:hover {
  color: rgb(var(--v-theme-primary)) !important;
  text-decoration: underline !important;
}
</style>
