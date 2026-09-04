<script setup>
import SummarizerStepper from './components/SummarizerStepper.vue'
import StepConfigure from './components/StepConfigure.vue'
import StepProcessing from './components/StepProcessing.vue'
import StepResult from './components/StepResult.vue'
import StepUpload from './components/StepUpload.vue'
import { useSummaryFlowStore } from '@/store/useSummaryFlowStore'

const store = useSummaryFlowStore()

const stepComponents = {
  1: StepUpload,
  2: StepConfigure,
  3: StepProcessing,
  4: StepResult,
}
</script>

<template>
  <div class="w-full h-full py-8 px-4 sm:px-8 space-y-8">
    <div>
      <p class="text-xs font-extrabold uppercase tracking-widest text-primary mb-2">
        Resumen asistido por IA
      </p>
      <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">
        Resume tu documento
      </h1>
      <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 mt-2 max-w-2xl">
        Sube un documento extenso y deja que la IA te entregue un resumen claro, en unos pocos pasos.
      </p>
    </div>

    <SummarizerStepper />

    <VCard class="p-6 sm:p-8">
      <Component :is="stepComponents[store.currentStep]" />
    </VCard>
  </div>
</template>
