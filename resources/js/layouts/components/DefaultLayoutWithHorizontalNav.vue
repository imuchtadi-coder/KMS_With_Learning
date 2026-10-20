<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

import navItems from '@/navigation/horizontal'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
import LanguageSwitcher from '@/layouts/components/LanguageSwitcher.vue'

import { HorizontalNavLayout } from '@layouts'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'

const page = usePage()

const isSuperAdmin = computed(() =>
  page.props.auth?.user?.roles?.includes('superadmin')
)

const tenantBranding = computed(() =>
  page.props.tenantBranding
)

const appTitle = computed(() => {
  return tenantBranding.value?.agency_name || themeConfig.app.title || 'KMS Learning'
})

const appLogoUrl = computed(() => {
  if (
    isSuperAdmin.value ||
    !tenantBranding.value?.logo_url
  ) {
    return null
  }

  return tenantBranding.value.logo_url
})
</script>

<template>
  <HorizontalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar>
      <RouterLink
        to="/"
        class="app-logo d-flex align-center gap-x-3"
      >
        <!-- Tenant Logo -->
        <img
          v-if="appLogoUrl"
          :src="appLogoUrl"
          :alt="appTitle"
          class="app-logo-img"
        />

        <!-- KITSuluh Logo untuk Super Admin / fallback -->
        <VNodeRenderer
          v-else
          :nodes="themeConfig.app.logo"
        />

        <h1
          class="app-title font-weight-bold leading-normal text-xl text-capitalize"
        >
          {{ appTitle }}
        </h1>
      </RouterLink>

      <VSpacer />

      <NavSearchBar trigger-btn-class="ms-lg-n3" />

      <NavbarThemeSwitcher />
      <LanguageSwitcher />
      <NavbarShortcuts />
      <NavBarNotifications class="me-2" />
      <UserProfile />
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </HorizontalNavLayout>
</template>

<style scoped>
.app-logo-img {
  width: 36px;
  height: 36px;
  object-fit: contain;
}
</style>
