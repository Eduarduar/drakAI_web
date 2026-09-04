import { requestPost } from '@/services/requests'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

const defaultOptions = () => ({
  grouping: 'section', // 'section' | 'general'
  format: 'bullets', // 'bullets' | 'narrative'
  glossary: true,
})

// Estado central del flujo de 4 pasos (Documento → Configuración →
// Procesando → Resultado). Vive en un store en vez de en la página para que
// los 4 componentes de paso (hermanos entre sí) lo compartan sin prop
// drilling. No persiste entre recargas a propósito: la app es stateless
// (ver CLAUDE.md), así que este store tampoco debería serlo.
export const useSummaryFlowStore = defineStore('summaryFlow', () => {
  const currentStep = ref(1)
  const maxStepReached = ref(1)

  const inputMode = ref('file') // 'file' | 'text'
  const file = ref(null)
  const pastedText = ref('')

  const options = ref(defaultOptions())

  const loading = ref(false)
  const errorMessage = ref(null)
  const result = ref(null)

  const hasValidInput = computed(() => {
    return inputMode.value === 'file'
      ? !!file.value
      : pastedText.value.trim().length >= 50
  })

  // Espejo en español de lo que arma GeminiClient::buildPrompt() en el
  // backend — mismo criterio, para que el usuario sepa qué se le va a pedir
  // a la IA antes de generar el resumen (ver paso 2).
  const promptPreview = computed(() => {
    const grouping = options.value.grouping === 'section'
      ? 'Resume este documento organizando el resultado por sección'
      : 'Genera un resumen general único de este documento'

    const format = options.value.format === 'bullets'
      ? 'en formato de puntos clave'
      : 'en formato narrativo'

    let text = `${grouping}, ${format}.`

    if (options.value.glossary) {
      text += ' Agrega un glosario con los términos técnicos más importantes.'
    }

    return text
  })

  function goToStep(step) {
    if (step < 1 || step > maxStepReached.value) return
    currentStep.value = step
  }

  function setFile(newFile) {
    file.value = newFile
    if (newFile) inputMode.value = 'file'
  }

  function setPastedText(text) {
    pastedText.value = text
  }

  function setInputMode(mode) {
    inputMode.value = mode
  }

  function goToConfigure() {
    if (!hasValidInput.value) return
    maxStepReached.value = Math.max(maxStepReached.value, 2)
    currentStep.value = 2
  }

  function editOptions() {
    errorMessage.value = null
    currentStep.value = 2
  }

  async function generateSummary() {
    errorMessage.value = null
    loading.value = true
    maxStepReached.value = Math.max(maxStepReached.value, 3)
    currentStep.value = 3

    const formData = new FormData()

    if (inputMode.value === 'file' && file.value) {
      formData.append('file', file.value)
    } else {
      formData.append('text', pastedText.value.trim())
    }

    formData.append('grouping', options.value.grouping)
    formData.append('format', options.value.format)
    formData.append('glossary', options.value.glossary ? '1' : '0')

    try {
      const response = await requestPost({ url: 'summarize', data: formData, formData: true })

      if (response.success) {
        result.value = response.data
        maxStepReached.value = Math.max(maxStepReached.value, 4)
        currentStep.value = 4
      } else {
        errorMessage.value = response.message || 'No se pudo generar el resumen.'
      }
    } catch {
      errorMessage.value = 'No se pudo conectar con el servidor. Revisa tu conexión e intenta de nuevo.'
    } finally {
      loading.value = false
    }
  }

  function retry() {
    return generateSummary()
  }

  function reset() {
    currentStep.value = 1
    maxStepReached.value = 1
    inputMode.value = 'file'
    file.value = null
    pastedText.value = ''
    options.value = defaultOptions()
    loading.value = false
    errorMessage.value = null
    result.value = null
  }

  return {
    currentStep,
    maxStepReached,
    inputMode,
    file,
    pastedText,
    options,
    loading,
    errorMessage,
    result,
    hasValidInput,
    promptPreview,
    goToStep,
    setFile,
    setPastedText,
    setInputMode,
    goToConfigure,
    editOptions,
    generateSummary,
    retry,
    reset,
  }
})
