<script setup>
import { computed } from 'vue'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

const authUser = computed(() => page.props.auth?.user)

const whatsappCustomerService = computed(
  () => page.props.whatsappCustomerService
)

const whatsappNumber = computed(
  () => whatsappCustomerService.value?.phone_number || ''
)

const logout = () => {
  router.post(route('logout'))
}

const openCustomerService = () => {
  if (!whatsappNumber.value) {
    return
  }

  const url = `https://wa.me/${whatsappNumber.value}`

  window.open(url, '_blank', 'noopener,noreferrer')
}

const userProfileList = [
  { type: 'divider' },

  {
    type: 'navItem',
    icon: 'mdi-account-outline',
    title: 'Profile',
    href: route('profile.show'),
  },

  {
    type: 'navItem',
    icon: 'mdi-cog-outline',
    title: 'Settings',
    href: route('profile.edit'),
  },

  {
    type: 'navItem',
    icon: 'mdi-whatsapp',
    title: 'Customer Service',
    action: 'customerService',
  },
]
</script>

<template>
  <VBadge
    v-if="authUser"
    dot
    bordered
    location="bottom right"
    offset-x="1"
    offset-y="2"
    color="success"
  >
    <VAvatar
      size="38"
      class="cursor-pointer"
      color="primary"
      variant="tonal"
    >
      <VIcon icon="mdi-account-circle" />

      <!-- SECTION Menu -->
      <VMenu
        activator="parent"
        width="240"
        location="bottom end"
        offset="12px"
      >
        <VList>
          <VListItem>
            <div class="d-flex gap-2 align-center">
              <VListItemAction>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                  bordered
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VIcon icon="mdi-account-circle" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>

              <div>
                <h6 class="text-h6 font-weight-medium">
                  {{ authUser.name }}
                </h6>
                <VListItemSubtitle class="text-capitalize text-disabled">
                  {{ authUser.roles?.[0] }}
                </VListItemSubtitle>
              </div>
            </div>
          </VListItem>

          <PerfectScrollbar :options="{ wheelPropagation: false }">
            <template
              v-for="item in userProfileList"
              :key="item.title"
            >
              <VListItem
                v-if="item.type === 'navItem' && item.action === 'customerService'"
                :disabled="!whatsappCustomerService"
                @click="openCustomerService"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                    color="success"
                  />
                </template>

                <VListItemTitle>
                  {{ item.title }}
                </VListItemTitle>
              </VListItem>

              <VListItem
                v-else-if="item.type === 'navItem'"
                :href="item.href"
              >
                <template #prepend>
                  <VIcon
                    :icon="item.icon"
                    size="22"
                  />
                </template>

                <VListItemTitle>
                  {{ item.title }}
                </VListItemTitle>
              </VListItem>

              <VDivider
                v-else
                class="my-2"
              />
            </template>

            <div class="px-4 py-2">
              <VBtn
                block
                size="small"
                color="error"
                append-icon="mdi-logout"
                @click="logout"
              >
                Logout
              </VBtn>
            </div>
          </PerfectScrollbar>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
