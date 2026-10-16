<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const currentYear = new Date().getFullYear()

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Daftar Akun Peserta — KITsuluh" />

  <div class="auth-wrapper min-vh-100 d-flex flex-column align-center justify-center pa-4 bg-background">
    <!-- Brand Header -->
    <div class="text-center mb-6">
      <Link href="/" class="d-inline-flex align-center text-decoration-none ga-3">
        <img src="/img/logok.png" alt="KITsuluh" height="48" />
        <div class="text-left">
          <div class="text-h5 font-weight-bold text-primary lh-1">KITsuluh</div>
          <span class="text-caption text-medium-emphasis">Learning Management System</span>
        </div>
      </Link>
    </div>

    <!-- Register Card -->
    <VCard
      elevation="3"
      rounded="xl"
      class="w-100 pa-8 border"
      style="max-width: 480px;"
    >
      <header class="mb-6 text-center">
        <h2 class="text-h5 font-weight-bold mb-1">Daftar Akun Baru</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Mulai perjalanan belajar Anda dan raih sertifikat kompetensi.
        </p>
      </header>

      <VForm @submit.prevent="submit">
        <!-- Nama Lengkap -->
        <div class="mb-4">
          <VTextField
            id="register-name"
            v-model="form.name"
            label="Nama Lengkap *"
            placeholder="John Doe"
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-account-outline"
            :error-messages="form.errors.name"
            required
          />
        </div>

        <!-- Email -->
        <div class="mb-4">
          <VTextField
            id="register-email"
            v-model="form.email"
            label="Alamat Email *"
            placeholder="nama@email.com"
            type="email"
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-email-outline"
            :error-messages="form.errors.email"
            required
          />
        </div>

        <!-- Kata Sandi -->
        <div class="mb-4">
          <VTextField
            id="register-password"
            v-model="form.password"
            :type="isPasswordVisible ? 'text' : 'password'"
            label="Kata Sandi *"
            placeholder="Minimal 8 karakter"
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-lock-outline"
            :append-inner-icon="isPasswordVisible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
            :error-messages="form.errors.password"
            required
            @click:append-inner="isPasswordVisible = !isPasswordVisible"
          />
        </div>

        <!-- Konfirmasi Kata Sandi -->
        <div class="mb-4">
          <VTextField
            id="register-password-confirm"
            v-model="form.password_confirmation"
            :type="isConfirmPasswordVisible ? 'text' : 'password'"
            label="Konfirmasi Kata Sandi *"
            placeholder="Ketik ulang kata sandi"
            variant="outlined"
            density="comfortable"
            prepend-inner-icon="mdi-lock-check-outline"
            :append-inner-icon="isConfirmPasswordVisible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
            :error-messages="form.errors.password_confirmation"
            required
            @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
          />
        </div>

        <!-- Checkbox Syarat -->
        <div class="mb-6">
          <VCheckbox
            v-model="form.terms"
            density="compact"
            color="primary"
            hide-details
          >
            <template #label>
              <span class="text-caption text-medium-emphasis">
                Saya menyetujui Ketentuan Layanan & Kebijakan Privasi
              </span>
            </template>
          </VCheckbox>
        </div>

        <!-- Submit Button -->
        <VBtn
          block
          type="submit"
          size="large"
          color="primary"
          elevation="2"
          class="font-weight-bold mb-4"
          :loading="form.processing"
          :disabled="form.processing"
        >
          Daftar Sekarang
        </VBtn>
      </VForm>

      <!-- Footer Links inside Card -->
      <div class="text-center text-body-2 text-medium-emphasis">
        Sudah memiliki akun?
        <Link :href="route('login')" class="text-primary font-weight-bold text-decoration-none ml-1">
          Masuk di sini
        </Link>
      </div>

      <VDivider class="my-4" />

      <!-- Register as Tenant Link -->
      <div class="text-center text-caption text-medium-emphasis">
        Mewakili kampus, dinas, atau perusahaan?
        <br />
        <Link :href="route('register-tenant')" class="text-primary font-weight-bold text-decoration-none">
          Daftarkan Organisasi Anda sebagai Tenant &rarr;
        </Link>
      </div>
    </VCard>

    <footer class="mt-6 text-caption text-medium-emphasis">
      &copy; {{ currentYear }} KITsuluh — All rights reserved.
    </footer>
  </div>
</template>

<style scoped>
.auth-wrapper {
  background-color: #f8f7fa;
}
</style>
