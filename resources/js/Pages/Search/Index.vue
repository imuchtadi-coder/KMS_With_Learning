<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  q: { type: String, default: '' },
  results: { type: Array, default: () => [] },
  type: { type: String, default: 'all' },
})

function search(e) {
  e.preventDefault()
  const form = e.target
  router.get(route('search.index'), { q: form.q.value, type: form.type.value }, { preserveState: true })
}

function setType(t) {
  router.get(route('search.index'), { q: props.q, type: t }, { preserveState: true })
}

function typeBadgeColor(t) {
  if (t === 'course') return 'primary'
  if (t === 'lesson') return 'success'
  if (t === 'article') return 'info'
  if (t === 'user') return 'purple'
  return 'secondary'
}

function typeBadgeLabel(t) {
  if (t === 'course') return 'Course'
  if (t === 'lesson') return 'Lesson'
  if (t === 'article') return 'Artikel / SOP'
  if (t === 'user') return 'User'
  return t
}
</script>

<template>
  <div class="search-page">
    <nav class="text-caption text-medium-emphasis mb-4">
      <a :href="route('dashboard')" class="text-primary text-decoration-none">Dashboard</a>
      <span class="mx-1">/</span>
      <span class="font-weight-bold">Pencarian Terpadu</span>
    </nav>

    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-3">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Pencarian Terpadu</h4>
        <p class="text-body-2 text-medium-emphasis mb-0">Cari artikel pengetahuan, SOP, modul kursus, materi pelajaran, dan pengguna.</p>
      </div>
    </div>

    <VCard flat border class="mb-6 pa-5">
      <form @submit="search" class="d-flex flex-column ga-4">
        <VTextField
          name="q"
          :model-value="q"
          label="Cari artikel pengetahuan, SOP, kursus, materi, topik..."
          placeholder="Ketik kata kunci, nama penyakit, langkah kerja, atau istilah teknis..."
          variant="outlined"
          density="comfortable"
          hide-details="auto"
          prepend-inner-icon="mdi-magnify"
        >
          <template #append-inner>
            <VBtn type="submit" variant="elevated" color="primary" class="font-weight-bold">
              Cari
            </VBtn>
          </template>
        </VTextField>

        <div class="d-flex align-center flex-wrap ga-3">
          <span class="text-caption font-weight-bold text-medium-emphasis">Filter Tipe:</span>
          <VRadioGroup
            :model-value="type"
            name="type"
            inline
            hide-details
            density="compact"
            @update:model-value="setType"
          >
            <VRadio label="Semua" value="all" />
            <VRadio label="Artikel / SOP" value="articles" />
            <VRadio label="Kursus" value="courses" />
            <VRadio label="Pelajaran" value="lessons" />
            <VRadio label="Pengguna" value="users" />
          </VRadioGroup>
        </div>
      </form>
    </VCard>

    <template v-if="q && q.trim().length >= 2">
      <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-2">
        <p class="text-body-2 text-medium-emphasis mb-0">
          Ditemukan <strong>{{ results.length }}</strong> hasil untuk kata kunci "<strong>{{ q }}</strong>"
        </p>
      </div>

      <div class="d-flex flex-column ga-3">
        <VCard
          v-for="(result, i) in results"
          :key="i"
          flat
          border
          hover
          :href="result.url"
          class="text-decoration-none result-card transition-all"
        >
          <VCardText class="pa-5">
            <div class="d-flex align-start ga-4">
              <VChip
                :color="typeBadgeColor(result.type)"
                size="small"
                variant="tonal"
                class="flex-shrink-0 mt-1 font-weight-bold"
              >
                {{ typeBadgeLabel(result.type) }}
              </VChip>

              <div class="flex-1 min-w-0">
                <div class="d-flex align-center ga-2 flex-wrap mb-1">
                  <h6 class="text-h6 font-weight-bold text-primary mb-0 hover-underline">
                    {{ result.item?.title || result.item?.name || result.item?.id }}
                  </h6>
                  <VChip
                    v-if="result.type === 'article' && result.item?.category"
                    size="x-small"
                    variant="outlined"
                    color="primary"
                  >
                    {{ result.item.category.name }}
                  </VChip>
                </div>

                <!-- Article snippet with highlighted matched words -->
                <div
                  v-if="result.type === 'article' && result.snippet"
                  class="text-body-2 text-medium-emphasis mt-2 search-snippet"
                  v-html="result.snippet"
                />

                <p
                  v-else-if="result.type === 'article' && result.item?.excerpt"
                  class="text-body-2 text-medium-emphasis mt-2 line-clamp-2"
                >
                  {{ result.item.excerpt }}
                </p>

                <p
                  v-if="result.type === 'course' && result.item?.description"
                  class="text-body-2 text-medium-emphasis mt-2 line-clamp-2"
                >
                  {{ result.item.description }}
                </p>

                <p
                  v-if="result.type === 'user'"
                  class="text-body-2 text-medium-emphasis mt-2"
                >
                  {{ result.item?.email }}
                </p>

                <p
                  v-if="result.type === 'lesson' && result.item?.section?.course"
                  class="text-body-2 text-medium-emphasis mt-2"
                >
                  {{ result.item.section.course.title }} › {{ result.item.section.title }}
                </p>

                <!-- Tags if article -->
                <div
                  v-if="result.type === 'article' && result.item?.tags && result.item.tags.length > 0"
                  class="d-flex align-center ga-1 mt-3 flex-wrap"
                >
                  <VChip
                    v-for="t in result.item.tags"
                    :key="t.id"
                    size="x-small"
                    variant="text"
                    class="text-disabled px-1"
                  >
                    #{{ t.name }}
                  </VChip>
                </div>
              </div>
            </div>
          </VCardText>
        </VCard>

        <!-- No Results -->
        <VCard v-if="!results.length" flat border class="pa-12 text-center my-4">
          <VIcon icon="tabler-file-unknown" size="56" color="medium-emphasis" class="mb-3" />
          <h5 class="text-h6 font-weight-bold mb-1">Tidak Ada Hasil Ditemukan</h5>
          <p class="text-body-2 text-medium-emphasis mb-0 mx-auto" style="max-width: 450px;">
            Tidak ditemukan artikel, kursus, atau pelajaran yang cocok dengan "{{ q }}". Coba gunakan sinonim, istilah umum, atau pilih filter "Semua".
          </p>
        </VCard>
      </div>
    </template>

    <div v-else-if="q && q.trim().length < 2" class="text-center py-8 text-medium-emphasis">
      Masukkan minimal 2 karakter untuk memulai pencarian.
    </div>

    <div v-else class="text-center py-12 text-medium-emphasis">
      <VIcon icon="tabler-search" size="64" color="disabled" class="mb-3" />
      <h5 class="text-h6 font-weight-bold mb-1">Cari Informasi Apapun di Organisasi Anda</h5>
      <p class="text-body-2 text-medium-emphasis">Pencarian cerdas mencakup isi dokumen, SOP, modul kursus, dan topik teknis.</p>
    </div>
  </div>
</template>

<style scoped>
.min-w-0 {
  min-width: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.result-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.result-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07) !important;
}

.hover-underline:hover {
  text-decoration: underline;
}

/* Snippet highlight styling from ts_headline */
:deep(mark),
:deep(.search-highlight) {
  background-color: #fef08a !important;
  color: #854d0e !important;
  font-weight: 700 !important;
  padding: 1px 4px;
  border-radius: 3px;
}
</style>
