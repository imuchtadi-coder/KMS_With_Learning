<template>
  <div class="auth-logo d-flex align-center gap-x-3 pa-6">
    <VNodeRenderer :nodes="themeConfig.app.logo" />
    <h1 class="auth-title">
      {{ themeConfig.app.title }}
    </h1>
  </div>

  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 6.25rem;"
        >
          <VImg
            max-width="550"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-6"
      >
        <VCardText class="text-center">
          <VImg
            src="/img/logokit.png"
            max-width="140"
            class="mx-auto mb-4"
          />
          <h4 class="text-h4 mb-1">
            Selamat Datang di <span class="text-capitalize">KITsuluh</span>!
          </h4>
          <p class="mb-0">
            Silakan masuk ke akun Anda dan mulai petualangan
          </p>
        </VCardText>

        <VCardText>
          <VAlert
            v-if="form.errors.email"
            type="error"
            variant="tonal"
            class="mb-4"
            density="compact"
            closable
            :text="form.errors.email"
            @click:close="form.errors.email = null"
          />

          <VForm @submit.prevent="submit">
            <VRow>
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  autofocus
                  label="Email or Username"
                  type="email"
                  placeholder="johndoe@email.com"
                  :error-messages="form.errors.email"
                />
              </VCol>

              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :error-messages="form.errors.password"
                  :append-inner-icon="isPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between my-6">
                  <VCheckbox
                    v-model="form.remember"
                    label="Remember me"
                  />
                  <a
                    href="#"
                    class="text-primary"
                    @click.prevent
                  >
                    Forgot Password?
                  </a>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="form.processing"
                  :disabled="form.processing"
                >
                  Login
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import AppTextField from '@core/components/app-form-elements/AppTextField.vue'
import { themeConfig } from '@themeConfig'
import boyWithLaptopLight from '@images/pages/boy-with-laptop-light.png'
import boyWithLaptopDark from '@images/pages/boy-with-laptop-dark.png'

const isPasswordVisible = ref(false)
const vuetifyTheme = useTheme()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const authThemeImg = computed(() => {
  return vuetifyTheme.global.name.value === 'dark' ? boyWithLaptopDark : boyWithLaptopLight
})

const submit = () => {
  form.post('/login')
}
</script>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
