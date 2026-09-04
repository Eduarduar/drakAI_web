<script setup>
import { useSummaryFlowStore } from '@/store/useSummaryFlowStore'
import { useToastStore } from '@/store/useToastStore'
import { computed } from 'vue'

const store = useSummaryFlowStore()
const toastStore = useToastStore()

const sections = computed(() => store.result?.sections ?? [])
const glossary = computed(() => store.result?.glossary ?? [])

// Un resumen "general" siempre viene como una única sección sin título —
// en ese caso no tiene sentido mostrar un encabezado de sección vacío.
const showSectionTitles = computed(() => store.options.grouping === 'section')

function buildPlainText() {
  const lines = []

  sections.value.forEach(section => {
    if (showSectionTitles.value && section.title) {
      lines.push(section.title.toUpperCase())
      lines.push('-'.repeat(section.title.length))
    }

    section.content?.forEach(item => {
      lines.push(store.options.format === 'bullets' ? `- ${item}` : item)
    })

    lines.push('')
  })

  if (glossary.value.length) {
    lines.push('GLOSARIO')
    lines.push('--------')
    glossary.value.forEach(({ term, definition }) => lines.push(`${term}: ${definition}`))
  }

  return lines.join('\n').trim()
}

async function copyToClipboard() {
  try {
    await navigator.clipboard.writeText(buildPlainText())
    toastStore.showToast({ message: 'Resumen copiado al portapapeles.', tipo: 'success' })
  } catch {
    toastStore.showToast({ message: 'No se pudo copiar el resumen.', tipo: 'error' })
  }
}

function downloadAsTxt() {
  const blob = new Blob([buildPlainText()], { type: 'text/plain;charset=utf-8' })
  const url = URL.createObjectURL(blob)

  const link = document.createElement('a')
  link.href = url
  link.download = 'resumen-drakai.txt'
  link.click()

  URL.revokeObjectURL(url)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
      <div>
        <h2 class="text-xl font-semibold flex items-center gap-2">
          <VIcon
            icon="bx-bxs-check-circle"
            size="22"
            class="text-success"
          /> Tu resumen está listo
        </h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">
          Revísalo, cópialo o descárgalo como archivo de texto.
        </p>
      </div>

      <div class="flex gap-2">
        <Button
          variant="outline-secondary"
          size="sm"
          @click="copyToClipboard"
        >
          <VIcon
            icon="bx-copy"
            size="16"
            class="mr-1"
          /> Copiar
        </Button>
        <Button
          variant="outline-primary"
          size="sm"
          @click="downloadAsTxt"
        >
          <VIcon
            icon="bx-download"
            size="16"
            class="mr-1"
          /> Descargar .txt
        </Button>
      </div>
    </div>

    <div class="space-y-5">
      <div
        v-for="(section, index) in sections"
        :key="index"
        class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-darkmode-500 p-5"
      >
        <h3
          v-if="showSectionTitles && section.title"
          class="font-semibold mb-2"
        >
          {{ section.title }}
        </h3>

        <ul
          v-if="store.options.format === 'bullets'"
          class="list-disc pl-5 space-y-1 text-sm text-slate-700 dark:text-slate-200"
        >
          <li
            v-for="(item, itemIndex) in section.content"
            :key="itemIndex"
          >
            {{ item }}
          </li>
        </ul>

        <div
          v-else
          class="space-y-2 text-sm text-slate-700 dark:text-slate-200"
        >
          <p
            v-for="(paragraph, paragraphIndex) in section.content"
            :key="paragraphIndex"
          >
            {{ paragraph }}
          </p>
        </div>
      </div>
    </div>

    <div
      v-if="glossary.length"
      class="rounded-lg border border-warning/30 bg-warning/5 p-5"
    >
      <h3 class="font-semibold mb-3 flex items-center gap-2">
        <VIcon
          icon="bx-book-content"
          size="18"
          class="text-warning"
        /> Glosario
      </h3>
      <dl class="space-y-2 text-sm">
        <div
          v-for="(entry, index) in glossary"
          :key="index"
        >
          <dt class="font-medium inline">
            {{ entry.term }}:
          </dt>
          <dd class="inline text-slate-600 dark:text-slate-300 ml-1">
            {{ entry.definition }}
          </dd>
        </div>
      </dl>
    </div>

    <div class="flex justify-between pt-2">
      <Button
        variant="outline-secondary"
        @click="store.editOptions()"
      >
        <VIcon
          icon="bx-edit-alt"
          size="18"
          class="mr-1"
        /> Editar opciones y regenerar
      </Button>
      <Button
        variant="primary"
        @click="store.reset()"
      >
        <VIcon
          icon="bx-file-blank"
          size="18"
          class="mr-1"
        /> Resumir otro documento
      </Button>
    </div>
  </div>
</template>
