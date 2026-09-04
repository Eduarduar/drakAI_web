<script setup>
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'

// Menú mínimo de arranque — agregar aquí las secciones reales de la app
// (por ejemplo, "Documentos", "Resúmenes", etc.) a medida que se construyan.
const menu = [
  {
    title: 'Resumir documento',
    icon: 'bx-file-blank',
    to: { name: 'summarizer' },
  },
]
</script>

<template>
  <VerticalNavLink
    v-for="(item, index) in menu"
    :key="index"
    :item="item"
  />
</template>
