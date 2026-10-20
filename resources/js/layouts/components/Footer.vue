<script setup>
import { useLanguage } from '@/composables/language'

const { t } = useLanguage()
</script>

<template>
  <div class="footer-wrap h-100 d-flex align-center justify-md-space-between justify-center text-body-2">
    <span class="d-flex align-center text-medium-emphasis footer-text">
      &copy; {{ new Date().getFullYear() }} PT Krakatau Sarana Infrastruktur
    </span>
    <span class="d-md-flex gap-x-4 text-primary d-none footer-links">
      <a
        :href="route('dashboard')"
        class="text-primary ms-1 footer-link"
      >{{ t('Dashboard') }}</a>
      <a
        :href="route('notifications.index')"
        class="text-primary ms-1 footer-link"
      >{{ t('Notifications') }}</a>
    </span>
  </div>
</template>

<style scoped>
.footer-wrap {
  font-family: inherit;
  font-size: 0.8125rem;
}

.footer-text {
  font-family: inherit;
  font-size: 0.8125rem;
}

.footer-links .footer-link {
  font-family: inherit;
  font-size: 0.8125rem;
  font-weight: 500;
  text-decoration: none;
}

.footer-links .footer-link:hover {
  text-decoration: underline;
}
</style>

