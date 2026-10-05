<script setup>
import { useLanguage } from '@/composables/language'

const { locale, languageOptions, setLanguage } = useLanguage()
</script>

<template>
  <VMenu location="bottom end" offset="8">
    <template #activator="{ props }">
      <VBtn
        v-bind="props"
        variant="text"
        size="small"
        class="language-switcher"
        :aria-label="locale === 'id' ? 'Pilih bahasa' : 'Choose language'"
      >
        <VIcon icon="mdi-translate" class="me-1" />
        {{ locale === 'id' ? '🇮🇩 ID' : '🇬🇧 EN' }}
      </VBtn>
    </template>
    <VList density="compact" :selected="[locale]" @update:selected="values => values[0] && setLanguage(values[0])">
      <VListItem v-for="language in languageOptions" :key="language.code" :value="language.code">
        <VListItemTitle>{{ language.label }}</VListItemTitle>
      </VListItem>
    </VList>
  </VMenu>
</template>
