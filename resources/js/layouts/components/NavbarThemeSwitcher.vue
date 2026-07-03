<script setup>
import ThemeSwitcher from '@core/components/ThemeSwitcher.vue'

const themes = [
  {
    name: 'light',
    icon: 'mdi-weather-sunny',
  },
  {
    name: 'dark',
    icon: 'mdi-weather-night',
  },
  {
    name: 'system',
    icon: 'mdi-theme-light-dark',
  },
]
</script>

<template>
  <ThemeSwitcher :themes="themes" />
</template>
