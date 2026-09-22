<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  discussions: { type: Object, required: true },
  course: { type: Object, default: null },
})

const page = usePage()
const search = ref('')

const filtered = computed(() => {
  if (!search.value) return props.discussions.data
  const q = search.value.toLowerCase()
  return props.discussions.data.filter(d =>
    d.title.toLowerCase().includes(q) || d.content?.toLowerCase().includes(q)
  )
})

const canPinDiscussion = computed(() => {
  console.log('AUTH USER:', page.props.auth?.user)
  console.log('ROLES:', page.props.auth?.user?.roles)
  const roles = page.props.auth?.user?.roles ?? []

  return roles.includes('admin') || roles.includes('instructor')
})

const isInstructor = computed(() => {
  const roles = page.props.auth?.user?.roles ?? []

  return roles.includes('instructor')
})

const togglePin = (discussion) => { 
  router.post( route('discussions.pin', discussion.id), {}, { preserveScroll: true, } ) 
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-4">
      <div>
        <h4 class="text-h4 font-weight-bold">Discussions</h4>
        <p v-if="course" class="text-body-1 text-medium-emphasis">{{ course.title }}</p>
      </div>
      <div class="d-flex align-center ga-3">
        <VTextField
          v-model="search"
          placeholder="Search discussions..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="default"
          hide-details
          style="max-width: 280px;"
        />
        <VBtn
          v-if="isInstructor && course"
          variant="tonal"
          prepend-icon="mdi-arrow-left"
          @click="router.visit(`/instructor/courses/${course.id}`)"
        >
          Back
        </VBtn>

        <VBtn
          color="primary"
          variant="elevated"
          prepend-icon="mdi-plus"
          :href="route('discussions.create', { course_id: course?.id })"
        >
          New Discussion
        </VBtn>
      </div>
    </div>

    <div v-if="filtered.length" class="d-flex flex-column ga-3">
      <VCard
        v-for="disc in filtered"
        :key="disc.id"
        flat
        border
        :class="{ 'border-primary': disc.is_pinned }"
        :href="route('discussions.show', disc.id)"
        class="text-decoration-none"
      >
        <VCardText class="d-flex align-center ga-4 py-3">
          <VAvatar size="40" :color="disc.creator?.avatar_url ? undefined : 'primary'" variant="tonal">
            <VImg v-if="disc.creator?.avatar_url" :src="disc.creator.avatar_url" />
            <span v-else class="text-uppercase font-weight-bold">{{ disc.creator?.name?.[0] }}</span>
          </VAvatar>
          <div class="flex-grow-1" style="min-width: 0;">
            <div class="d-flex align-center ga-2">
              <span v-if="disc.is_pinned" class="v-chip v-chip--size-x-small v-chip--variant-tonal bg-primary text-primary">
                <VIcon start icon="mdi-pin" size="14" />
                Pinned
              </span>
              <span class="font-weight-semibold text-truncate">{{ disc.title }}</span>
            </div>
            <div class="text-body-2 text-medium-emphasis mt-1 d-flex ga-3">
              <span>{{ disc.creator?.name }}</span>
              <span>&bull;</span>
              <span>{{ disc.course?.title }}</span>
              <span v-if="disc.lesson">&bull; {{ disc.lesson?.title }}</span>
            </div>
          </div>
          <!-- <div class="text-right text-body-2 text-medium-emphasis flex-shrink-0" style="min-width: 100px;">
            <div>{{ disc.replies_count ?? 0 }} replies</div>
            <div class="text-caption">{{ new Date(disc.created_at).toLocaleDateString() }}</div>
          </div> -->
        <div class="d-flex align-center ga-3 flex-shrink-0"> 
          <div class="text-right text-body-2 text-medium-emphasis" style="min-width: 100px;"> 
            <div>{{ disc.replies_count ?? 0 }} replies</div> 
              <div class="text-caption"> 
                {{ new Date(disc.created_at).toLocaleDateString() }} 
            </div> 
          </div>

          <VBtn 
            v-if="canPinDiscussion"
            :color="disc.is_pinned ? 'primary' : 'default'" 
            variant="tonal" size="small" :prepend-icon="disc.is_pinned ? 'mdi-pin' : 'mdi-pin-outline'" @click.prevent.stop="togglePin(disc)" > 
            {{ disc.is_pinned ? 'Unpin' : 'Pin' }} 
          </VBtn> 
        </div>
        </VCardText>
      </VCard>
    </div>

    <VCard v-else flat border class="pa-16 text-center">
      <VIcon icon="mdi-forum-outline" size="48" color="disabled" class="mb-4" />
      <p class="text-h6 text-medium-emphasis">No discussions yet.</p>
      <p class="text-body-2 text-disabled mb-4">Start a new discussion to engage with your peers.</p>
      <VBtn
        color="primary"
        variant="elevated"
        prepend-icon="mdi-plus"
        :href="route('discussions.create', { course_id: course?.id })"
      >
        Start Discussion
      </VBtn>
    </VCard>

    <div v-if="discussions.last_page > 1" class="d-flex align-center justify-center pa-4">
      <VPagination
        :model-value="discussions.current_page"
        :length="discussions.last_page"
        :total-visible="5"
        @update:model-value="(p) => router.get(route('discussions.index', { course_id: course?.id, page: p }), {}, { preserveState: true })"
      />
    </div>
  </div>
</template>
