<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

import navItems from '@/navigation/vertical'
import { themeConfig } from '@themeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavSearchBar from '@/layouts/components/NavSearchBar.vue'
import NavbarShortcuts from '@/layouts/components/NavbarShortcuts.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'

const page = usePage()

/*
 * ============================================================
 * AUTH
 * ============================================================
 */
const isSuperAdmin = computed(() =>
  page.props.auth?.user?.roles?.includes('superadmin') ?? false
)

/*
 * ============================================================
 * TENANT BRANDING
 * ============================================================
 */
const tenantBranding = computed(() =>
  page.props.tenantBranding ?? null
)

/*
 * ============================================================
 * APPLICATION TITLE
 * ============================================================
 *
 * Super Admin:
 *   KITSuluh
 *
 * Tenant Admin / Instructor / Student:
 *   Nama agency/tenant dari master branding
 */
const appTitle = computed(() => {
  if (
    isSuperAdmin.value ||
    !tenantBranding.value?.agency_name
  ) {
    return themeConfig.app.title
  }

  return tenantBranding.value.agency_name
})

/*
 * ============================================================
 * APPLICATION LOGO
 * ============================================================
 *
 * Super Admin:
 *   Logo KITSuluh dari themeConfig
 *
 * Tenant:
 *   Logo tenant dari master branding
 */
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
  <VerticalNavLayout :nav-items="navItems">

    <!-- ======================================================
         👉 Vertical Navigation Header / Logo
         ====================================================== -->
    <template #vertical-nav-header="{ isVerticalNavMini }">
      <RouterLink
        to="/"
        class="app-logo app-title-wrapper"
      >
        <!-- Tenant Logo -->
        <img
          v-if="appLogoUrl"
          :src="appLogoUrl"
          :alt="appTitle"
          class="app-logo-img"
        />

        <!-- KITSuluh Logo untuk Super Admin -->
        <VNodeRenderer
          v-else
          :nodes="themeConfig.app.logo"
        />

        <!-- Application / Tenant Name -->
        <Transition name="vertical-nav-app-title">
          <h1
            v-if="!isVerticalNavMini"
            class="app-logo-title"
          >
            {{ appTitle }}
          </h1>
        </Transition>
      </RouterLink>
    </template>

    <!-- ======================================================
         👉 Navbar
         ====================================================== -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">

        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            size="26"
            icon="mdi-menu"
          />
        </IconBtn>

        <NavSearchBar class="ms-lg-n3" />

        <VSpacer />

        <NavbarThemeSwitcher />
        <NavbarShortcuts />
        <NavBarNotifications class="me-1" />
        <UserProfile />

      </div>
    </template>

    <!-- ======================================================
         👉 Pages
         ====================================================== -->
    <slot />

    <!-- ======================================================
         👉 Footer
         ====================================================== -->
    <template #footer>
      <Footer />
    </template>

  </VerticalNavLayout>
</template>

<style scoped>
.app-logo-img {
  width: 36px;
  height: 36px;
  object-fit: contain;
}
</style>