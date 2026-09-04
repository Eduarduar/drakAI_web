<script setup>
import { useSummaryFlowStore } from '@/store/useSummaryFlowStore'
import { computed, ref } from 'vue'

const store = useSummaryFlowStore()

const ACCEPTED_EXTENSIONS = ['pdf', 'docx', 'txt']
const MAX_SIZE_BYTES = 15 * 1024 * 1024

const isDragging = ref(false)
const fileInputRef = ref(null)
const fileError = ref(null)

const fileIconByExtension = {
  pdf: 'bx-bxs-file-pdf',
  docx: 'bx-bxs-file-doc',
  txt: 'bx-bxs-file-txt',
}

const fileExtension = computed(() => store.file?.name.split('.').pop()?.toLowerCase())
const fileIcon = computed(() => fileIconByExtension[fileExtension.value] || 'bx-file-blank')

function formatBytes(bytes) {
  const kb = bytes / 1024

  return kb < 1024 ? `${kb.toFixed(0)} KB` : `${(kb / 1024).toFixed(1)} MB`
}

function validateAndSetFile(candidate) {
  fileError.value = null

  const extension = candidate.name.split('.').pop()?.toLowerCase()

  if (!ACCEPTED_EXTENSIONS.includes(extension)) {
    fileError.value = 'Formato no admitido. Sube un PDF, Word (.docx) o TXT.'

    return
  }

  if (candidate.size > MAX_SIZE_BYTES) {
    fileError.value = 'El archivo supera el límite de 15 MB.'

    return
  }

  store.setFile(candidate)
}

function openFileDialog() {
  fileInputRef.value?.click()
}

function onFileInputChange(event) {
  const candidate = event.target.files?.[0]
  if (candidate) validateAndSetFile(candidate)
  event.target.value = ''
}

function onDrop(event) {
  isDragging.value = false
  const candidate = event.dataTransfer?.files?.[0]
  if (candidate) validateAndSetFile(candidate)
}

function removeFile() {
  store.setFile(null)
  fileError.value = null
}

const charCount = computed(() => store.pastedText.length)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold">
        Sube tu documento
      </h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">
        Aceptamos PDF, Word (.docx) y texto plano — o puedes pegar el texto directamente.
      </p>
    </div>

    <!-- Selector de modo: archivo / texto pegado -->
    <div class="inline-flex p-1 rounded-lg bg-slate-100 dark:bg-darkmode-600">
      <button
        type="button"
        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors"
        :class="store.inputMode === 'file' ? 'bg-white dark:bg-darkmode-400 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'"
        @click="store.setInputMode('file')"
      >
        Subir archivo
      </button>
      <button
        type="button"
        class="px-4 py-1.5 text-sm font-medium rounded-md transition-colors"
        :class="store.inputMode === 'text' ? 'bg-white dark:bg-darkmode-400 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'"
        @click="store.setInputMode('text')"
      >
        Pegar texto
      </button>
    </div>

    <!-- Modo archivo -->
    <div v-if="store.inputMode === 'file'">
      <div
        v-if="!store.file"
        class="border-2 border-dashed rounded-lg py-10 px-6 text-center cursor-pointer transition-colors"
        :class="isDragging ? 'border-primary bg-primary/5' : 'border-slate-300/70 bg-slate-50 dark:bg-darkmode-600 dark:border-white/10'"
        @click="openFileDialog"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onDrop"
      >
        <input
          ref="fileInputRef"
          type="file"
          accept=".pdf,.docx,.txt"
          class="hidden"
          @change="onFileInputChange"
        >
        <VIcon
          icon="bx-cloud-upload"
          size="40"
          class="text-primary mb-2"
        />
        <p class="font-medium">
          Arrastra tu documento aquí o haz clic para elegirlo
        </p>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          PDF, Word (.docx) o TXT · máximo 15 MB
        </p>
      </div>

      <div
        v-else
        class="flex items-center gap-3 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-darkmode-500 p-4"
      >
        <VIcon
          :icon="fileIcon"
          size="32"
          class="text-primary shrink-0"
        />
        <div class="min-w-0 flex-1">
          <p class="font-medium truncate">
            {{ store.file.name }}
          </p>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ formatBytes(store.file.size) }}
          </p>
        </div>
        <button
          type="button"
          class="text-slate-400 hover:text-danger transition-colors shrink-0"
          aria-label="Quitar archivo"
          @click="removeFile"
        >
          <VIcon
            icon="bx-x-circle"
            size="22"
          />
        </button>
      </div>

      <p
        v-if="fileError"
        class="text-sm text-danger mt-2 flex items-center gap-1.5"
      >
        <VIcon
          icon="bx-error-circle"
          size="16"
        /> {{ fileError }}
      </p>
    </div>

    <!-- Modo texto pegado -->
    <div v-else>
      <FormTextarea
        :model-value="store.pastedText"
        rows="10"
        maxlength="30000"
        class="p-3"
        placeholder="Pega o escribe aquí el texto que quieres resumir…"
        @update:model-value="store.setPastedText($event)"
      />
      <p class="text-xs text-slate-400 mt-1 text-right">
        {{ charCount.toLocaleString('es') }} / 30.000 caracteres (mínimo 50)
      </p>
    </div>

    <div class="flex justify-end pt-2">
      <Button
        variant="primary"
        :disabled="!store.hasValidInput"
        @click="store.goToConfigure()"
      >
        Continuar
        <VIcon
          icon="bx-right-arrow-alt"
          size="18"
          class="ml-1"
        />
      </Button>
    </div>
  </div>
</template>
