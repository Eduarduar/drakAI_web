<script setup>
import { useSummaryFlowStore } from '@/store/useSummaryFlowStore'
import { computed } from 'vue'

const store = useSummaryFlowStore()

const steps = [
  { number: 1, label: 'Documento', icon: 'bx-upload' },
  { number: 2, label: 'Configuración', icon: 'bx-slider-alt' },
  { number: 3, label: 'Generando', icon: 'bx-loader-alt' },
  { number: 4, label: 'Resultado', icon: 'bx-check-circle' },
]

// Solo se puede volver a "Documento" y "Configuración" con un clic: son los
// únicos pasos con input del usuario. "Generando" y "Resultado" se llegan
// siguiendo el flujo (botón "Generar resumen"), no saltando directamente.
function isClickable(step) {
  return step.number <= 2 && step.number <= store.maxStepReached
}

function stateOf(step) {
  if (step.number < store.currentStep) return 'done'
  if (step.number === store.currentStep) return 'active'

  return 'pending'
}

const progressPercent = computed(() => ((store.currentStep - 1) / (steps.length - 1)) * 100)
</script>

<template>
  <nav aria-label="Progreso del resumen">
    <ol class="relative grid grid-cols-4 gap-2">
      <div class="absolute top-4 left-0 right-0 h-0.5 bg-slate-200 dark:bg-white/10 mx-8">
        <div
          class="h-full bg-primary transition-all duration-300"
          :style="{ width: `${progressPercent}%` }"
        />
      </div>

      <li
        v-for="step in steps"
        :key="step.number"
        class="relative flex flex-col items-center gap-1.5 text-center"
      >
        <button
          type="button"
          class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors relative z-10 shrink-0"
          :class="[
            stateOf(step) === 'done' && 'bg-success border-success text-white',
            stateOf(step) === 'active' && 'bg-primary border-primary text-white',
            stateOf(step) === 'pending' && 'bg-white dark:bg-darkmode-600 border-slate-300 dark:border-white/10 text-slate-400',
            isClickable(step) ? 'cursor-pointer' : 'cursor-default',
          ]"
          :disabled="!isClickable(step)"
          @click="isClickable(step) && store.goToStep(step.number)"
        >
          <VIcon
            v-if="stateOf(step) === 'done'"
            icon="bx-check"
            size="18"
          />
          <span
            v-else
            class="text-xs font-semibold"
          >{{ step.number }}</span>
        </button>

        <span
          class="text-xs font-medium leading-tight hidden sm:block"
          :class="stateOf(step) === 'pending' ? 'text-slate-400' : 'text-slate-700 dark:text-slate-200'"
        >{{ step.label }}</span>
      </li>
    </ol>
  </nav>
</template>
