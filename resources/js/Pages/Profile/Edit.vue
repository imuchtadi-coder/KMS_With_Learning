<script setup>
import { usePage } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  user: { type: Object, required: true },
})

const page = usePage()
const errors = computed(() => page.props.errors || {})

const profileForm = useForm({
  name: props.user?.name,
  email: props.user?.email,
  phone: props.user?.phone || '',
  birthday: props.user?.birthday || '',
  department: props.user?.department || '',
  address: props.user?.address || '',
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold mb-1">Edit Profile</h4>
        <p class="text-body-1 text-medium-emphasis">Update your account information</p>
      </div>
      <VBtn variant="outlined" prepend-icon="mdi-arrow-left" :href="route('profile.show')">Back to Profile</VBtn>
    </div>

    <VAlert v-if="Object.keys(errors).length" type="error" closable class="mb-6">
      <template #title>There were some errors:</template>
      <ul class="mt-1">
        <li v-for="(msg, key) in errors" :key="key">{{ msg }}</li>
      </ul>
    </VAlert>

    <VCard flat border class="mb-6">
      <VCardTitle>Profile Information</VCardTitle>
      <VCardSubtitle>Update your personal information and contact details.</VCardSubtitle>
      <VCardText>
        <VForm @submit.prevent="profileForm.put(route('profile.update'))" class="d-flex flex-column ga-4">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField v-model="profileForm.name" label="Full Name *" variant="outlined" density="default" :rules="[v => !!v || 'Name is required']" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="profileForm.email" label="Email Address *" variant="outlined" density="default" type="email" :rules="[v => !!v || 'Email is required']" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="profileForm.phone" label="Phone Number" variant="outlined" density="default" placeholder="e.g., +62 812-3456-7890" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="profileForm.birthday" label="Date of Birth" variant="outlined" density="default" type="date" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="profileForm.department" label="Department" variant="outlined" density="default" placeholder="e.g., IT Department, HR, Marketing" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField :model-value="user.tenant?.name || 'No tenant'" label="Tenant" variant="outlined" density="default" readonly disabled hide-details="auto" />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="profileForm.address" label="Address" variant="outlined" density="default" rows="3" placeholder="Enter your full address" hide-details="auto" />
            </VCol>
          </VRow>
          <div class="d-flex justify-end pt-2">
            <VBtn type="submit" color="primary" variant="elevated" :loading="profileForm.processing" prepend-icon="mdi-check">Update Profile</VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>

    <VCard flat border>
      <VCardTitle>Change Password</VCardTitle>
      <VCardSubtitle>Update your password to keep your account secure.</VCardSubtitle>
      <VCardText>
        <VForm @submit.prevent="passwordForm.put(route('profile.update-password'))" class="d-flex flex-column ga-4">
          <VRow>
            <VCol cols="12" md="6">
              <VTextField v-model="passwordForm.current_password" label="Current Password *" variant="outlined" density="default" type="password" :rules="[v => !!v || 'Current password is required']" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="passwordForm.password" label="New Password *" variant="outlined" density="default" type="password" :rules="[v => !!v || 'New password is required']" hide-details="auto" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="passwordForm.password_confirmation" label="Confirm New Password *" variant="outlined" density="default" type="password" :rules="[v => !!v || 'Please confirm your password']" hide-details="auto" />
            </VCol>
          </VRow>
          <div class="d-flex justify-end pt-2">
            <VBtn type="submit" color="success" variant="elevated" :loading="passwordForm.processing" prepend-icon="mdi-lock-reset">Update Password</VBtn>
          </div>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>
