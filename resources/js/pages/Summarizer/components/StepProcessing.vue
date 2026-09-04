<script setup>
import { useSummaryFlowStore } from '@/store/useSummaryFlowStore'
import { onMounted, onUnmounted, ref } from 'vue'

const store = useSummaryFlowStore()

// Refleja el pipeline real descrito en la presentación (detectar
// estructura → analizar secciones → extraer términos → redactar) para que
// la espera se sienta informativa en vez de un spinner genérico.
const messages = [
  'Detectando la estructura del documento…',
  'Analizando cada sección…',
  'Extrayendo términos técnicos clave…',
  'Redactando el resumen…',
]

const messageIndex = ref(0)
let intervalId = null

onMounted(() => {
  intervalId = setInterval(() => {
    messageIndex.value = (messageIndex.value + 1) % messages.length
  }, 2200)
})

onUnmounted(() => {
  clearInterval(intervalId)
})
</script>

<template>
  <div class="py-10 flex flex-col items-center text-center">
    <template v-if="store.loading">
      <LoadingIcon
        icon="tail-spin"
        custom-class="w-12 h-12"
      />
      <p class="mt-6 font-medium">
        {{ messages[messageIndex] }}
      </p>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Esto puede tardar unos segundos según el tamaño del documento.
      </p>
    </template>

    <template v-else-if="store.errorMessage">
      <VIcon
        icon="bx-error-circle"
        size="44"
        class="text-danger mb-3"
      />
      <p class="font-medium">
        No se pudo generar el resumen
      </p>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-md">
        {{ store.errorMessage }}
      </p>

      <div class="flex gap-3 mt-6">
        <Button
          variant="outline-secondary"
          @click="store.editOptions()"
        >
          <VIcon
            icon="bx-left-arrow-alt"
            size="18"
            class="mr-1"
          /> Editar opciones
        </Button>
        <Button
          variant="primary"
          @click="store.retry()"
        >
          <VIcon
            icon="bx-refresh"
            size="18"
            class="mr-1"
          /> Reintentar
        </Button>
      </div>
    </template>
  </div>
</template>
