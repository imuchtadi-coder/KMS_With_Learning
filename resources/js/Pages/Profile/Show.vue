<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  user: { type: Object, required: true },
})

const page = usePage()
const flash = computed(() => page.props.flash || {})
const initials = computed(() => props.user.name.charAt(0).toUpperCase())
const memberSince = computed(() => {
  const d = new Date(props.user.created_at)
  return d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">My Profile</h4>
        <p class="text-body-1 text-medium-emphasis">Manage your account information and preferences</p>
      </div>
      <VBtn color="primary" variant="elevated" prepend-icon="mdi-pencil" :href="route('profile.edit')">Edit Profile</VBtn>
    </div>

    <VAlert v-if="flash.success" type="success" closable class="mb-6">{{ flash.success }}</VAlert>

    <VCard flat border class="mb-6">
      <VCardTitle>Profile Information</VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <div class="d-flex align-center ga-4 mb-4">
              <VAvatar color="primary" size="80" variant="tonal">
                <span class="text-h4 font-weight-bold">{{ initials }}</span>
              </VAvatar>
              <div>
                <h5 class="text-h5 font-weight-bold">{{ user.name }}</h5>
                <p class="text-body-2 text-medium-emphasis">{{ user.email }}</p>
                <div v-if="user.roles?.length" class="d-flex ga-2 mt-2">
                  <VChip v-for="role in user.roles" :key="role.id" color="primary" size="small" variant="flat">{{ role.name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) }}</VChip>
                </div>
              </div>
            </div>
          </VCol>

          <VCol cols="12" md="6">
            <label class="text-caption font-weight-bold text-medium-emphasis">Full Name</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded">{{ user.name }}</p>
          </VCol>
          <VCol cols="12" md="6">
            <label class="text-caption font-weight-bold text-medium-emphasis">Email Address</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded">{{ user.email }}</p>
          </VCol>
          <VCol cols="12" md="6">
            <label class="text-caption font-weight-bold text-medium-emphasis">Phone Number</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded">{{ user.phone || 'Not provided' }}</p>
          </VCol>
          <VCol cols="12" md="6">
            <label class="text-caption font-weight-bold text-medium-emphasis">Date of Birth</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded">{{ user.birthday ? new Date(user.birthday).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'Not provided' }}</p>
          </VCol>
          <VCol cols="12" md="6">
            <label class="text-caption font-weight-bold text-medium-emphasis">Department</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded">{{ user.department || 'Not provided' }}</p>
          </VCol>
          <VCol cols="12" md="6">
            <label class="text-caption font-weight-bold text-medium-emphasis">Tenant</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded">{{ user.tenant?.name || 'No tenant' }}</p>
          </VCol>
          <VCol cols="12">
            <label class="text-caption font-weight-bold text-medium-emphasis">Address</label>
            <p class="text-body-1 bg-surface-light pa-2 rounded min-h-[3rem]">{{ user.address || 'Not provided' }}</p>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <VRow>
      <VCol cols="12" md="4">
        <VCard flat border class="border-l-thick border-primary">
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis font-weight-bold">Course Enrollments</p>
              <p class="text-h3 font-weight-bold mt-2">{{ user.enrollments?.length || 0 }}</p>
            </div>
            <VIcon icon="mdi-book-open-variant" size="36" color="primary" />
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard flat border class="border-l-thick border-success">
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis font-weight-bold">Certificates Earned</p>
              <p class="text-h3 font-weight-bold mt-2">{{ user.certificates?.length || 0 }}</p>
            </div>
            <VIcon icon="mdi-certificate" size="36" color="success" />
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard flat border class="border-l-thick border-purple">
          <VCardText class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis font-weight-bold">Member Since</p>
              <p class="text-h5 font-weight-bold mt-2">{{ memberSince }}</p>
            </div>
            <VIcon icon="mdi-calendar" size="36" color="purple" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.border-l-thick { border-left-width: 4px !important; }
.bg-surface-light { background-color: rgb(var(--v-theme-surface-light)) !important; }
.min-h-\[3rem\] { min-height: 3rem; }
</style>
