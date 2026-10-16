import { h } from 'vue'
import { breakpointsVuetifyV3 } from '@vueuse/core'
import { VIcon } from 'vuetify/components/VIcon'
import { defineThemeConfig } from '@core'
import { Skins } from '@core/enums'

import { AppContentLayoutNav, ContentWidth, FooterType, NavbarType } from '@layouts/enums'

export const { themeConfig, layoutConfig } = defineThemeConfig({
  app: {
    title: 'KITsuluh',
    logo: h('img', { src: '/img/logok.png', style: 'height: 40px; width: auto; line-height: 0;' }),
    contentWidth: ContentWidth.Boxed,
    contentLayoutNav: AppContentLayoutNav.Vertical,
    overlayNavFromBreakpoint: breakpointsVuetifyV3.lg - 1, // 1 for matching with vuetify breakpoint. Docs: https://next.vuetifyjs.com/en/features/display-and-platform/
    i18n: {
      enable: true,
      defaultLocale: 'id',
      langConfig: [
        {
          label: 'Bahasa Indonesia',
          i18nLang: 'id',
          isRTL: false,
        },
        {
          label: 'English',
          i18nLang: 'en',
          isRTL: false,
        },
      ],
    },
    theme: 'system',
    skin: Skins.Bordered,
    iconRenderer: VIcon,
  },
  navbar: {
    type: NavbarType.Sticky,
    navbarBlur: true,
  },
  footer: { type: FooterType.Static },
  verticalNav: {
    isVerticalNavCollapsed: false,
    defaultNavItemIconProps: { icon: 'mdi-circle-outline' },
    isVerticalNavSemiDark: false,
  },
  horizontalNav: {
    type: 'sticky',
    transition: 'slide-y-reverse-transition',
    popoverOffset: 6,
  },

  /*
    // ℹ️  In below Icons section, you can specify icon for each component. Also you can use other props of v-icon component like `color` and `size` for each icon.
    // Such as: chevronDown: { icon: 'mdi-chevron-down', color:'primary', size: '24' },
    */
  icons: {
    chevronDown: { icon: 'mdi-chevron-down' },
    chevronRight: { icon: 'mdi-chevron-right', size: 20 },
    close: { icon: 'mdi-close', size: 20 },
    verticalNavPinned: { icon: 'mdi-radiobox-marked', size: 20 },
    verticalNavUnPinned: { icon: 'mdi-radiobox-blank', size: 20 },
    sectionTitlePlaceholder: { icon: 'mdi-minus' },
  },
})
