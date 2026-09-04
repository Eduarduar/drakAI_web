<script setup>
import { useSummaryFlowStore } from '@/store/useSummaryFlowStore'

const store = useSummaryFlowStore()

const groupingOptions = [
  { value: 'section', title: 'Por sección', description: 'Un bloque de resumen por cada sección o tema del documento.' },
  { value: 'general', title: 'Resumen general', description: 'Un único resumen que condensa todo el documento.' },
]

const formatOptions = [
  { value: 'bullets', title: 'Puntos clave', description: 'Viñetas cortas y directas, fáciles de escanear.' },
  { value: 'narrative', title: 'Narrativo', description: 'Párrafos breves con redacción más fluida.' },
]
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold">
        Configura tu resumen
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Elige cómo quieres que la IA organice el resultado.
      </p>
    </div>

    <fieldset>
      <legend class="text-sm font-semibold mb-2">
        Agrupación del contenido
      </legend>
      <div class="grid sm:grid-cols-2 gap-3">
        <label
          v-for="option in groupingOptions"
          :key="option.value"
          class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-colors"
          :class="store.options.grouping === option.value ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'"
        >
          <input
            v-model="store.options.grouping"
            type="radio"
            :value="option.value"
            class="mt-1 shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 checked:bg-primary checked:border-primary dark:bg-darkmode-800 dark:border-transparent"
          >
          <span>
            <span class="block font-medium">{{ option.title }}</span>
            <span class="block text-sm text-slate-500 dark:text-slate-400">{{ option.description }}</span>
          </span>
        </label>
      </div>
    </fieldset>

    <fieldset>
      <legend class="text-sm font-semibold mb-2">
        Formato del resumen
      </legend>
      <div class="grid sm:grid-cols-2 gap-3">
        <label
          v-for="option in formatOptions"
          :key="option.value"
          class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-colors"
          :class="store.options.format === option.value ? 'border-primary bg-primary/5' : 'border-slate-200 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'"
        >
          <input
            v-model="store.options.format"
            type="radio"
            :value="option.value"
            class="mt-1 shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 checked:bg-primary checked:border-primary dark:bg-darkmode-800 dark:border-transparent"
          >
          <span>
            <span class="block font-medium">{{ option.title }}</span>
            <span class="block text-sm text-slate-500 dark:text-slate-400">{{ option.description }}</span>
          </span>
        </label>
      </div>
    </fieldset>

    <label class="flex items-center gap-3 cursor-pointer w-fit">
      <span
        class="w-[38px] h-[24px] p-px rounded-full relative transition-colors shrink-0"
        :class="store.options.glossary ? 'bg-primary' : 'bg-slate-300 dark:bg-darkmode-400'"
      >
        <input
          v-model="store.options.glossary"
          type="checkbox"
          class="sr-only"
        >
        <span
          class="block w-[20px] h-[20px] rounded-full bg-white shadow transition-transform"
          :class="store.options.glossary ? 'translate-x-[14px]' : 'translate-x-0'"
        />
      </span>
      <span class="text-sm font-medium">Incluir glosario de términos técnicos</span>
    </label>

    <div class="rounded-lg bg-slate-50 dark:bg-darkmode-600 border border-slate-200 dark:border-white/10 p-4">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1.5 flex items-center gap-1.5">
        <VIcon
          icon="bx-terminal"
          size="15"
        /> Lo que se le pedirá a la IA
      </p>
      <p class="text-sm text-slate-700 dark:text-slate-200 italic">
        "{{ store.promptPreview }}"
      </p>
    </div>

    <div class="flex justify-between pt-2">
      <Button
        variant="outline-secondary"
        @click="store.goToStep(1)"
      >
        <VIcon
          icon="bx-left-arrow-alt"
          size="18"
          class="mr-1"
        /> Atrás
      </Button>
      <Button
        variant="primary"
        @click="store.generateSummary()"
      >
        Generar resumen
        <VIcon
          icon="bx-right-arrow-alt"
          size="18"
          class="ml-1"
        />
      </Button>
    </div>
  </div>
</template>
