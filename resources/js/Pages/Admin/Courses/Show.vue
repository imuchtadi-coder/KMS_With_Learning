<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
  course: { type: Object, required: true },
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">{{ course.title }}</h4>
        <p class="text-body-2 text-medium-emphasis">
          by {{ course.instructor?.name || 'N/A' }}
          <VChip
            :color="course.published ? 'success' : 'warning'"
            size="x-small"
            variant="tonal"
            label
            class="ml-2"
          >
            {{ course.published ? 'Published' : 'Draft' }}
          </VChip>
        </p>
      </div>
      <div class="d-flex ga-2">
        <VBtn
          v-if="!course.published"
          color="success"
          variant="tonal"
          @click="router.post(route('admin.courses.publish', course.id))"
        >
          <VIcon start icon="mdi-check" /> Publish
        </VBtn>
        <VBtn
          v-else
          color="warning"
          variant="tonal"
          @click="router.post(route('admin.courses.unpublish', course.id))"
        >
          <VIcon start icon="mdi-close" /> Unpublish
        </VBtn>
        <VBtn variant="outlined" :href="route('admin.courses.index')">Back</VBtn>
      </div>
    </div>

    <VCard flat border class="mb-4">
      <VCardText>
        <p class="text-body-1 mb-4">{{ course.description }}</p>

        <VRow>
          <VCol cols="6" md="3">
            <div class="text-caption text-medium-emphasis">Skill Level</div>
            <div class="font-weight-medium">{{ course.skill_level || 'N/A' }}</div>
          </VCol>
          <VCol cols="6" md="3">
            <div class="text-caption text-medium-emphasis">Duration</div>
            <div class="font-weight-medium">{{ course.duration || 'N/A' }}</div>
          </VCol>
          <VCol cols="6" md="3">
            <div class="text-caption text-medium-emphasis">Language</div>
            <div class="font-weight-medium">{{ course.language || 'N/A' }}</div>
          </VCol>
          <VCol cols="6" md="3">
            <div class="text-caption text-medium-emphasis">Price</div>
            <div class="font-weight-medium">{{ course.price ? 'Rp ' + Number(course.price).toLocaleString() : 'Free' }}</div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VCard flat border :title="'Sections (' + (course.sections?.length || 0) + ')'">
      <VCardText>
        <VExpansionPanels variant="accordion">
          <VExpansionPanel
            v-for="section in course.sections"
            :key="section.id"
          >
            <VExpansionPanelTitle class="font-weight-medium">
              {{ section.title }}
            </VExpansionPanelTitle>
            <VExpansionPanelText>
              <VList density="compact">
                <VListItem
                  v-for="lesson in section.lessons"
                  :key="lesson.id"
                  :title="lesson.title"
                  :subtitle="lesson.videos?.length + ' video(s)'"
                />
                <VListItem v-if="!section.lessons?.length" disabled title="No lessons" />
              </VList>
            </VExpansionPanelText>
          </VExpansionPanel>
        </VExpansionPanels>
      </VCardText>
    </VCard>
  </div>
</template>
