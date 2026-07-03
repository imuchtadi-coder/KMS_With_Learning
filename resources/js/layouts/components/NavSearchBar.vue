<script setup>
import { router } from '@inertiajs/vue3'

defineOptions({
  inheritAttrs: false,
})

const goToSearch = () => {
  router.get(route('search.index'))
}
</script>

<template>
  <div
    class="d-flex align-center cursor-pointer"
    v-bind="$attrs"
    style="user-select: none;"
  >
    <IconBtn @click="goToSearch">
      <VIcon icon="mdi-magnify" />
    </IconBtn>

    <span
      class="d-none d-md-flex align-center text-disabled ms-2"
      @click="goToSearch"
    >
      <span class="me-2">Search</span>
      <span class="meta-key">&#8984;K</span>
    </span>
  </div>
</template>

<style lang="scss">
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  font-size: 0.8125rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}
</style>
