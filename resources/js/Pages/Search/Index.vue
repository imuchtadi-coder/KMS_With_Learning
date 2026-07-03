<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  q: { type: String, default: '' },
  results: { type: Array, default: () => [] },
  type: { type: String, default: 'all' },
})

const searchQuery = computed({
  get: () => props.q,
  set: (val) => { },
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
  if (t === 'user') return 'purple'
  return 'secondary'
}
</script>

<template>
  <div>
    <nav class="text-caption text-medium-emphasis mb-4">
      <a :href="route('dashboard')" class="text-primary">Dashboard</a>
      <span class="mx-1">/</span>
      <span class="font-weight-bold">Search</span>
    </nav>

    <h4 class="text-h4 font-weight-bold mb-6">Search</h4>

    <VCard flat border class="mb-6 pa-4">
      <form @submit="search" class="d-flex flex-column ga-3">
        <VTextField name="q" :model-value="q" label="Search courses, lessons, users..." variant="outlined" density="default" hide-details="auto">
          <template #append-inner>
            <VBtn type="submit" icon variant="text" color="primary"><VIcon icon="mdi-magnify" /></VBtn>
          </template>
        </VTextField>
        <div class="d-flex ga-3">
          <VRadioGroup :model-value="type" name="type" inline hide-details density="default" @update:model-value="setType">
            <VRadio label="All" value="all" />
            <VRadio label="Courses" value="courses" />
            <VRadio label="Lessons" value="lessons" />
            <VRadio label="Users" value="users" />
          </VRadioGroup>
        </div>
      </form>
    </VCard>

    <template v-if="q && q.trim().length >= 2">
      <p class="text-body-2 text-medium-emphasis mb-4">
        Found <strong>{{ results.length }}</strong> result{{ results.length !== 1 ? 's' : '' }} for "{{ q }}"
      </p>

      <div class="d-flex flex-column ga-3">
        <VCard v-for="(result, i) in results" :key="i" flat border hover :href="result.url" class="text-decoration-none">
          <VCardText>
            <div class="d-flex align-start ga-3">
              <VChip :color="typeBadgeColor(result.type)" size="small" variant="flat" class="flex-shrink-0 mt-1">
                {{ result.type === 'course' ? 'Course' : result.type === 'lesson' ? 'Lesson' : 'User' }}
              </VChip>
              <div class="flex-1 min-w-0">
                <h6 class="text-h6 font-weight-bold text-primary">{{ result.item?.title || result.item?.name || result.item?.id }}</h6>
                <p v-if="result.type === 'course' && result.item?.description" class="text-body-2 text-medium-emphasis mt-1 line-clamp-2">{{ result.item.description }}</p>
                <p v-if="result.type === 'user'" class="text-body-2 text-medium-emphasis mt-1">{{ result.item?.email }}</p>
                <p v-if="result.type === 'lesson' && result.item?.section?.course" class="text-body-2 text-medium-emphasis mt-1">
                  {{ result.item.section.course.title }} › {{ result.item.section.title }}
                </p>
              </div>
            </div>
          </VCardText>
        </VCard>

        <VCard v-if="!results.length" flat border class="pa-12 text-center">
          <p class="text-h6 text-medium-emphasis">No results found</p>
          <p class="text-body-2 text-medium-emphasis mt-2">Try different keywords or broaden your search.</p>
        </VCard>
      </div>
    </template>

    <div v-else-if="q && q.trim().length < 2" class="text-center py-8 text-medium-emphasis">
      Please enter at least 2 characters to search.
    </div>

    <div v-else class="text-center py-12 text-medium-emphasis">
      <VIcon icon="mdi-magnify" size="64" color="disabled" />
      <p class="text-h6 mt-4">Search courses, lessons, and users</p>
    </div>
  </div>
</template>

<style scoped>
.min-w-0 { min-width: 0; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
