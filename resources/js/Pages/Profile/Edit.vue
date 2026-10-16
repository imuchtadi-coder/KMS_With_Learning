<script setup>
import { ref, computed } from 'vue'
import { usePage, useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  user: { type: Object, required: true },
})

const page = usePage()
const errors = computed(() => page.props.errors || {})

const profileForm = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
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

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const submitProfile = () => {
  profileForm.put(route('profile.update'), {
    preserveScroll: true,
  })
}

const submitPassword = () => {
  passwordForm.put(route('profile.update-password'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  })
}
</script>

<template>
  <div class="profile-edit-page">
    <!-- Header Section -->
    <div class="d-flex align-center justify-space-between mb-5 flex-wrap ga-3">
      <div class="d-flex align-center ga-3">
        <Link :href="route('profile.show')" class="text-decoration-none">
          <VBtn
            variant="tonal"
            size="small"
            prepend-icon="mdi-arrow-left"
          >
            Kembali ke Profil
          </VBtn>
        </Link>
        <div>
          <h4 class="text-h4 font-weight-bold mb-0">Ubah Profil & Keamanan</h4>
          <span class="text-caption text-medium-emphasis">
            Perbarui data diri, informasi kontak, dan kata sandi akun Anda.
          </span>
        </div>
      </div>
    </div>

    <!-- Error Alert if any -->
    <VAlert
      v-if="Object.keys(errors).length"
      type="error"
      variant="tonal"
      closable
      class="mb-5 border-error"
    >
      <div class="font-weight-bold mb-1">Terdapat beberapa kesalahan:</div>
      <ul class="ms-4 mb-0 text-caption">
        <li v-for="(msg, key) in errors" :key="key">{{ msg }}</li>
      </ul>
    </VAlert>

    <VRow>
      <!-- Left Column: Personal Information Form -->
      <VCol cols="12" lg="7">
        <VCard flat border class="pa-5 rounded-lg mb-5 mb-lg-0">
          <div class="d-flex align-center ga-2 mb-1">
            <VIcon icon="mdi-account-cog-outline" color="primary" size="22" />
            <h6 class="text-subtitle-1 font-weight-bold mb-0">Informasi Pribadi & Kontak</h6>
          </div>
          <span class="text-caption text-medium-emphasis mb-4 d-block">
            Data ini digunakan untuk identitas profil dan penerbitan sertifikat Anda.
          </span>

          <VForm @submit.prevent="submitProfile" class="d-flex flex-column ga-4">
            <VRow>
              <!-- Full Name -->
              <VCol cols="12" sm="6">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Nama Lengkap <span class="text-error">*</span>
                  </label>
                  <VTextField
                    v-model="profileForm.name"
                    placeholder="Nama lengkap Anda"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-account-outline"
                    :rules="[v => !!v || 'Nama wajib diisi']"
                    :error-messages="profileForm.errors.name"
                    hide-details="auto"
                  />
                </div>
              </VCol>

              <!-- Email Address -->
              <VCol cols="12" sm="6">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Alamat Email <span class="text-error">*</span>
                  </label>
                  <VTextField
                    v-model="profileForm.email"
                    type="email"
                    placeholder="email@domain.com"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-email-outline"
                    :rules="[v => !!v || 'Email wajib diisi']"
                    :error-messages="profileForm.errors.email"
                    hide-details="auto"
                  />
                </div>
              </VCol>

              <!-- Phone Number -->
              <VCol cols="12" sm="6">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Nomor Telepon / WhatsApp
                  </label>
                  <VTextField
                    v-model="profileForm.phone"
                    placeholder="0812-xxxx-xxxx"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-phone-outline"
                    :error-messages="profileForm.errors.phone"
                    hide-details="auto"
                  />
                </div>
              </VCol>

              <!-- Birthday -->
              <VCol cols="12" sm="6">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Tanggal Lahir
                  </label>
                  <VTextField
                    v-model="profileForm.birthday"
                    type="date"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-cake-variant-outline"
                    :error-messages="profileForm.errors.birthday"
                    hide-details="auto"
                  />
                </div>
              </VCol>

              <!-- Department -->
              <VCol cols="12" sm="6">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Departemen / Divisi
                  </label>
                  <VTextField
                    v-model="profileForm.department"
                    placeholder="Contoh: Divisi Agronomi / IT"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-briefcase-outline"
                    :error-messages="profileForm.errors.department"
                    hide-details="auto"
                  />
                </div>
              </VCol>

              <!-- Tenant (Read-only) -->
              <VCol cols="12" sm="6">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Organisasi / Tenant
                  </label>
                  <VTextField
                    :model-value="user.tenant?.name || 'Default Organisasi'"
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-domain"
                    readonly
                    disabled
                    hide-details="auto"
                  />
                </div>
              </VCol>

              <!-- Address -->
              <VCol cols="12">
                <div>
                  <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                    Alamat Lengkap
                  </label>
                  <VTextarea
                    v-model="profileForm.address"
                    rows="3"
                    placeholder="Masukkan alamat domisili atau kantor..."
                    variant="outlined"
                    density="compact"
                    prepend-inner-icon="mdi-map-marker-outline"
                    :error-messages="profileForm.errors.address"
                    hide-details="auto"
                  />
                </div>
              </VCol>
            </VRow>

            <!-- Submit Button -->
            <div class="d-flex justify-end pt-2 border-t">
              <VBtn
                type="submit"
                color="primary"
                variant="elevated"
                size="default"
                prepend-icon="mdi-content-save-check"
                :loading="profileForm.processing"
                :disabled="profileForm.processing || !profileForm.name?.trim() || !profileForm.email?.trim()"
              >
                Simpan Perubahan Profil
              </VBtn>
            </div>
          </VForm>
        </VCard>
      </VCol>

      <!-- Right Column: Password Change Form -->
      <VCol cols="12" lg="5">
        <VCard flat border class="pa-5 rounded-lg">
          <div class="d-flex align-center ga-2 mb-1">
            <VIcon icon="mdi-shield-key-outline" color="warning" size="22" />
            <h6 class="text-subtitle-1 font-weight-bold mb-0">Ganti Kata Sandi</h6>
          </div>
          <span class="text-caption text-medium-emphasis mb-4 d-block">
            Gunakan kombinasi minimal 8 karakter untuk keamanan maksimal.
          </span>

          <VForm @submit.prevent="submitPassword" class="d-flex flex-column ga-4">
            <!-- Current Password -->
            <div>
              <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                Kata Sandi Saat Ini <span class="text-error">*</span>
              </label>
              <VTextField
                v-model="passwordForm.current_password"
                :type="showCurrentPassword ? 'text' : 'password'"
                placeholder="Masukkan kata sandi lama"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-lock-outline"
                :append-inner-icon="showCurrentPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                @click:append-inner="showCurrentPassword = !showCurrentPassword"
                :rules="[v => !!v || 'Kata sandi saat ini wajib diisi']"
                :error-messages="passwordForm.errors.current_password"
                hide-details="auto"
              />
            </div>

            <!-- New Password -->
            <div>
              <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                Kata Sandi Baru <span class="text-error">*</span>
              </label>
              <VTextField
                v-model="passwordForm.password"
                :type="showNewPassword ? 'text' : 'password'"
                placeholder="Minimal 8 karakter"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-lock-plus-outline"
                :append-inner-icon="showNewPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                @click:append-inner="showNewPassword = !showNewPassword"
                :rules="[v => !!v || 'Kata sandi baru wajib diisi']"
                :error-messages="passwordForm.errors.password"
                hide-details="auto"
              />
            </div>

            <!-- Confirm New Password -->
            <div>
              <label class="text-subtitle-2 font-weight-medium d-block mb-1">
                Konfirmasi Kata Sandi Baru <span class="text-error">*</span>
              </label>
              <VTextField
                v-model="passwordForm.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="Ulangi kata sandi baru"
                variant="outlined"
                density="compact"
                prepend-inner-icon="mdi-lock-check-outline"
                :append-inner-icon="showConfirmPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                @click:append-inner="showConfirmPassword = !showConfirmPassword"
                :rules="[v => !!v || 'Konfirmasi kata sandi wajib diisi']"
                :error-messages="passwordForm.errors.password_confirmation"
                hide-details="auto"
              />
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-end pt-2 border-t">
              <VBtn
                type="submit"
                color="warning"
                variant="elevated"
                size="default"
                prepend-icon="mdi-lock-reset"
                :loading="passwordForm.processing"
                :disabled="passwordForm.processing || !passwordForm.current_password || !passwordForm.password"
              >
                Perbarui Kata Sandi
              </VBtn>
            </div>
          </VForm>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.border-t {
  border-top: 1px solid rgba(var(--v-border-color), 0.12);
}
</style>
