<template>
  <main class="auth-page">
    <section
      class="auth-hero d-none d-md-flex"
      aria-labelledby="hero-title"
    >
      <img
        class="auth-hero__photo"
        src="/img/auth/lms-login-hero.webp"
        alt=""
      >
      <div class="auth-hero__overlay" />

      <div class="auth-hero__content">
        <div class="auth-brand">
          <span class="auth-brand__mark">
            <img src="/img/logok.png" alt="">
          </span>
          <span>KITsuluh</span>
        </div>

        <div class="auth-hero__heading">
          <span class="auth-kicker">Learning Management System</span>
          <h1 id="hero-title">
            Belajar, berkembang, dan raih kompetensi bersama KITsuluh
          </h1>
        </div>

        <div class="auth-feature-cards" aria-hidden="true">
          <article class="feature-card feature-card--module">
            <span class="feature-card__icon">
              <VIcon icon="mdi-book-open-page-variant-outline" />
            </span>
            <span>
              <strong>Modul Pembelajaran</strong>
              <small>Materi tersusun sistematis</small>
            </span>
          </article>

          <article class="feature-card feature-card--progress">
            <span class="feature-progress">78%</span>
            <span>
              <strong>Progres Belajar</strong>
              <small>Pantau setiap pencapaian</small>
            </span>
          </article>

          <article class="feature-card feature-card--certificate">
            <span class="feature-card__icon">
              <VIcon icon="mdi-certificate-outline" />
            </span>
            <span>
              <strong>Sertifikat Kompetensi</strong>
              <small>Validasi hasil belajar</small>
            </span>
          </article>
        </div>

        <div class="auth-benefits">
          <div v-for="benefit in benefits" :key="benefit.label">
            <span><VIcon :icon="benefit.icon" /></span>
            {{ benefit.label }}
          </div>
        </div>
      </div>
    </section>

    <section class="auth-form-panel">
      <div class="mobile-brand d-flex d-md-none">
        <span class="mobile-brand__mark">
          <img src="/img/logok.png" alt="">
        </span>
        <span>KITsuluh</span>
      </div>

      <div class="auth-form-card">
        <div class="form-brand d-none d-md-flex">
          <span class="form-brand__mark">
            <img src="/img/logok.png" alt="">
          </span>
          <span>KITsuluh</span>
        </div>

        <header class="form-header">
          <span class="form-kicker">Selamat datang kembali</span>
          <h2>Masuk ke KITsuluh</h2>
          <p>
            Masuk untuk melanjutkan pembelajaran dan memantau progres Anda.
          </p>
        </header>

        <VAlert
          v-if="form.errors.email"
          type="error"
          variant="tonal"
          density="comfortable"
          closable
          title="Tidak dapat masuk"
          :text="form.errors.email"
          class="mb-6"
          @click:close="form.clearErrors('email')"
        />

        <VForm @submit.prevent="submit">
          <div class="auth-field">
            <label for="login-email">Email</label>
            <AppTextField
              id="login-email"
              v-model="form.email"
              autofocus
              type="email"
              inputmode="email"
              autocomplete="email"
              placeholder="nama@instansi.go.id"
              prepend-inner-icon="mdi-email-outline"
              :error-messages="form.errors.email"
              @update:model-value="form.clearErrors('email')"
            />
          </div>

          <div class="auth-field">
            <label for="login-password">Kata sandi</label>
            <AppTextField
              id="login-password"
              v-model="form.password"
              :type="isPasswordVisible ? 'text' : 'password'"
              autocomplete="current-password"
              placeholder="Masukkan kata sandi"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="isPasswordVisible ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
              :error-messages="form.errors.password"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
              @update:model-value="form.clearErrors('password')"
            />
          </div>

          <VCheckbox
            v-model="form.remember"
            color="success"
            label="Ingat saya"
            density="comfortable"
            hide-details
            class="auth-remember"
          />

          <VBtn
            block
            type="submit"
            size="large"
            color="success"
            class="auth-submit"
            :loading="form.processing"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Sedang masuk...' : 'Masuk ke KITsuluh' }}
          </VBtn>
        </VForm>

        <div class="auth-support">
          <VIcon icon="mdi-headset" />
          <span>Butuh bantuan? Hubungi administrator Anda.</span>
        </div>
      </div>

      <footer>© {{ currentYear }} KITsuluh</footer>
    </section>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppTextField from '@core/components/app-form-elements/AppTextField.vue'

const isPasswordVisible = ref(false)
const currentYear = new Date().getFullYear()

const benefits = [
  { icon: 'mdi-book-open-page-variant-outline', label: 'Materi terstruktur' },
  { icon: 'mdi-chart-line', label: 'Progres terukur' },
  { icon: 'mdi-certificate-outline', label: 'Sertifikat kompetensi' },
]

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
  })
}
</script>

<style lang="scss">
.auth-page {
  --auth-green: #1f6b45;
  --auth-green-dark: #185637;
  --auth-green-soft: #e9f3ed;
  --auth-gold: #d9a441;
  --auth-ink: #17342b;
  --auth-muted: #687a73;
  display: grid;
  grid-template-columns: minmax(0, 55%) minmax(420px, 45%);
  min-block-size: 100dvh;
  block-size: 100dvh;
  overflow: hidden;
  background: #f4f8f3;
  font-family: Inter, "Public Sans", sans-serif;
}

.auth-hero {
  position: relative;
  block-size: 100dvh;
  min-block-size: 100dvh;
  overflow: hidden;
  border-end-end-radius: 7rem;
  color: #fff;
  background: var(--auth-green-dark);
}

.auth-hero__photo,
.auth-hero__overlay {
  position: absolute;
  inset: 0;
  inline-size: 100%;
  block-size: 100%;
}

.auth-hero__photo {
  object-fit: cover;
  object-position: 55% center;
}

.auth-hero__overlay {
  background:
    linear-gradient(90deg, rgba(16, 65, 43, 0.96) 0%, rgba(20, 76, 50, 0.83) 42%, rgba(16, 67, 44, 0.24) 100%),
    linear-gradient(0deg, rgba(12, 50, 33, 0.74) 0%, transparent 48%);
}

.auth-hero__content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  inline-size: 100%;
  min-block-size: 100%;
  block-size: 100%;
  box-sizing: border-box;
  padding: clamp(2rem, 4vw, 4.5rem);
}

.auth-brand,
.form-brand,
.mobile-brand {
  align-items: center;
  gap: 0.75rem;
  font-weight: 800;
  letter-spacing: -0.04em;
}

.auth-brand {
  display: flex;
  color: #fff;
  font-size: 1.75rem;
}

.auth-brand__mark,
.form-brand__mark,
.mobile-brand__mark {
  display: grid;
  flex: 0 0 auto;
  place-items: center;
  overflow: hidden;
  border-radius: 0.75rem;
  background: #fff;
}

.auth-brand__mark {
  inline-size: 3rem;
  block-size: 3rem;
}

.auth-brand__mark img,
.form-brand__mark img,
.mobile-brand__mark img {
  display: block;
  inline-size: 72%;
  block-size: 72%;
  object-fit: contain;
}

.auth-hero__heading {
  max-inline-size: 680px;
  margin-block-start: clamp(3.2rem, 9vh, 7rem);
}

.auth-kicker,
.form-kicker {
  display: block;
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.auth-kicker {
  color: #d7ebde;
}

.auth-hero__heading h1 {
  margin: 0.85rem 0 0 !important;
  color: #fff !important;
  font-size: clamp(2rem, 3.1vw, 3.6rem) !important;
  font-weight: 750 !important;
  line-height: 1.08 !important;
  letter-spacing: -0.045em !important;
}

.auth-feature-cards {
  position: relative;
  flex: 1;
  min-block-size: 300px;
  margin-block-start: 1.5rem;
}

.feature-card {
  position: absolute;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-inline-size: 210px;
  padding: 0.9rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.68);
  border-radius: 0.9rem;
  color: var(--auth-ink);
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 16px 34px rgba(12, 46, 31, 0.2);
  backdrop-filter: blur(9px);
}

.feature-card--module {
  inset-block-start: 9%;
  inset-inline-start: 2%;
}

.feature-card--progress {
  inset-block-start: 44%;
  inset-inline-end: 2%;
}

.feature-card--certificate {
  inset-block-end: 5%;
  inset-inline-start: 17%;
}

.feature-card__icon,
.feature-progress {
  display: grid;
  flex: 0 0 auto;
  place-items: center;
  inline-size: 2.75rem;
  block-size: 2.75rem;
  border-radius: 0.75rem;
  color: var(--auth-green);
  background: var(--auth-green-soft);
}

.feature-progress {
  border-radius: 50%;
  background: conic-gradient(var(--auth-green) 78%, #d9e7de 0);
  box-shadow: inset 0 0 0 7px #fff;
  font-size: 0.62rem;
  font-weight: 800;
}

.feature-card strong,
.feature-card small {
  display: block;
}

.feature-card strong {
  font-size: 0.78rem;
}

.feature-card small {
  margin-block-start: 0.15rem;
  color: var(--auth-muted);
  font-size: 0.66rem;
}

.auth-benefits {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  padding-block-start: 1.25rem;
  border-block-start: 1px solid rgba(255, 255, 255, 0.36);
  font-size: 0.72rem;
  font-weight: 700;
}

.auth-benefits > div {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.auth-benefits span {
  display: grid;
  place-items: center;
  inline-size: 2rem;
  block-size: 2rem;
  border-radius: 0.55rem;
  color: var(--auth-green);
  background: #fff;
}

.auth-form-panel {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  block-size: 100dvh;
  min-block-size: 100dvh;
  box-sizing: border-box;
  overflow: hidden;
  padding: clamp(2rem, 5vw, 5.5rem);
  background:
    radial-gradient(circle at 90% 5%, rgba(217, 164, 65, 0.1), transparent 25%),
    #f4f8f3;
}

.auth-form-card {
  inline-size: min(100%, 490px);
  padding: clamp(2rem, 4vw, 3.5rem);
  border: 1px solid #dae5dd;
  border-radius: 1.5rem;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 24px 64px rgba(23, 52, 43, 0.1);
}

.form-brand {
  display: flex;
  justify-content: center;
  color: var(--auth-green-dark);
  font-size: 1.75rem;
}

.form-brand__mark,
.mobile-brand__mark {
  inline-size: 2.8rem;
  block-size: 2.8rem;
  border: 1px solid #dce7df;
}

.form-header {
  margin-block: 2.25rem 2rem;
  text-align: center;
}

.form-kicker {
  color: var(--auth-green);
}

.form-header h2 {
  margin: 0.75rem 0 0 !important;
  color: var(--auth-ink) !important;
  font-size: clamp(1.8rem, 2.5vw, 2.2rem) !important;
  line-height: 1.18 !important;
  letter-spacing: -0.035em !important;
}

.form-header p {
  max-inline-size: 380px;
  margin: 0.75rem auto 0;
  color: var(--auth-muted);
  font-size: 0.91rem;
  line-height: 1.6;
}

.auth-field {
  margin-block-end: 1.15rem;
}

.auth-field > label {
  display: inline-block;
  margin-block-end: 0.5rem;
  color: var(--auth-ink);
  font-size: 0.83rem;
  font-weight: 700;
}

.auth-field .v-field {
  min-block-size: 3.25rem;
  border-radius: 0.75rem !important;
}

.auth-field .v-field--focused {
  --v-field-border-opacity: 1;
  color: var(--auth-green) !important;
}

.auth-remember {
  margin-block: 0.2rem 1.4rem;
}

.auth-submit {
  min-block-size: 3.25rem;
  border-radius: 0.75rem !important;
  color: #fff !important;
  background: var(--auth-green) !important;
  box-shadow: 0 10px 24px rgba(31, 107, 69, 0.24) !important;
  font-weight: 750 !important;
  letter-spacing: 0 !important;
  text-transform: none !important;
}

.auth-submit:hover {
  background: var(--auth-green-dark) !important;
}

.auth-support {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  margin-block-start: 1.65rem;
  padding-block-start: 1.4rem;
  border-block-start: 1px solid #e8eeea;
  color: var(--auth-muted);
  font-size: 0.76rem;
  text-align: center;
}

.auth-support .v-icon {
  color: var(--auth-green);
}

.mobile-brand {
  position: absolute;
  inset-block-start: 1.5rem;
  inset-inline-start: 1.5rem;
  color: var(--auth-green-dark);
  font-size: 1.4rem;
}

.auth-form-panel footer {
  position: absolute;
  inset-block-end: 1rem;
  color: #87978f;
  font-size: 0.7rem;
}

@media (max-width: 1200px) {
  .auth-page {
    grid-template-columns: minmax(0, 52%) minmax(410px, 48%);
  }

  .feature-card--certificate {
    display: none;
  }

  .auth-benefits {
    grid-template-columns: 1fr;
  }

  .auth-benefits > div:nth-child(n + 2) {
    display: none;
  }
}

@media (max-width: 959px) {
  .auth-page {
    display: block;
    block-size: auto;
    min-block-size: 100dvh;
    overflow: auto;
  }

  .auth-form-panel {
    block-size: auto;
    padding: 6rem 1.25rem 3.5rem;
    overflow: visible;
  }
}

@media (min-width: 960px) and (max-height: 860px) {
  .auth-hero__content {
    padding: 1.75rem 3rem;
  }

  .auth-hero__heading {
    margin-block-start: 2.25rem;
  }

  .auth-hero__heading h1 {
    font-size: clamp(1.9rem, 2.7vw, 3rem) !important;
  }

  .auth-feature-cards {
    min-block-size: 220px;
    margin-block-start: 0.75rem;
  }

  .feature-card--module {
    inset-block-start: 5%;
  }

  .feature-card--progress {
    inset-block-start: 40%;
  }

  .feature-card--certificate {
    display: none;
  }

  .auth-form-panel {
    padding: 1.5rem clamp(2rem, 4vw, 4rem);
  }

  .auth-form-card {
    padding: 2rem 2.5rem;
  }

  .form-brand__mark {
    inline-size: 2.4rem;
    block-size: 2.4rem;
  }

  .form-brand {
    font-size: 1.5rem;
  }

  .form-header {
    margin-block: 1.35rem 1.4rem;
  }

  .form-header h2 {
    font-size: 1.8rem !important;
  }

  .form-header p {
    margin-block-start: 0.45rem;
    line-height: 1.45;
  }

  .auth-field {
    margin-block-end: 0.85rem;
  }

  .auth-remember {
    margin-block: 0 0.9rem;
  }

  .auth-support {
    margin-block-start: 1.1rem;
    padding-block-start: 1rem;
  }

  .auth-form-panel footer {
    display: none;
  }
}

@media (min-width: 960px) and (max-height: 700px) {
  .form-brand {
    display: none !important;
  }

  .form-header {
    margin-block: 0 1rem;
  }

  .auth-support {
    display: none;
  }
}

@media (max-width: 500px) {
  .auth-form-panel {
    align-items: flex-start;
    background: #fff;
  }

  .auth-form-card {
    padding: 1rem 0;
    border: 0;
    box-shadow: none;
  }

  .form-header {
    text-align: start;
  }

  .form-header p {
    margin-inline: 0;
  }
}
</style>
