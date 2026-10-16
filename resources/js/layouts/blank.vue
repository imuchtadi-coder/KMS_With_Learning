<script setup>
import { useSkins } from '@core/composable/useSkins'
import { initConfigStore } from '@core/stores/config'

initConfigStore()

const { injectSkinClasses } = useSkins()

injectSkinClasses()
</script>

<template>
  <div
    class="layout-wrapper layout-blank"
    data-allow-mismatch
  >
    <slot />
  </div>
</template>

<style>
.layout-wrapper.layout-blank {
  flex-direction: column;
}
</style>
