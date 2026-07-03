<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AcademyMyCourses from '@/views/apps/academy/AcademyMyCourses.vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})

defineProps({
  courses: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div>
    <h4 class="text-h4 font-weight-medium mb-2">
      My Courses
    </h4>
    <p class="text-body-1 text-medium-emphasis mb-6">
      Manage your enrolled courses and track your learning progress
    </p>

    <VAlert v-if="flash.error" type="error" closable class="mb-4">{{ flash.error }}</VAlert>

    <AcademyMyCourses
      :courses="courses"
      search-query=""
    />
  </div>
</template>
