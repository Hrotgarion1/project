<script setup>
import GoBackButton from '@/Components/Common/GoBackButton.vue';
import { getCurrentInstance } from 'vue';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  summaryData: {
    type: Object,
    default: () => ({}),
  },
  showBackButton: {
    type: Boolean,
    default: true,
  },
  actionButton: {
    type: Object,
    default: null, // Ejemplo: { label: 'Crear', onClick: () => {}, ariaLabel: 'Crear área' }
  },
});

// Inyecta i18n para traducciones
const instance = getCurrentInstance();
const $t = instance?.proxy?.$t ?? ((key) => key);

// Usar composable para formateo
const { formatNumber } = useFormat();

// Depuración de props
console.debug('HeaderSection props:', {
  title: props.title,
  summaryData: props.summaryData,
  showBackButton: props.showBackButton,
  actionButton: props.actionButton,
});
</script>

<template>
  <div class="flex items-center justify-between gap-2 mb-6 bg-main-0 dark:bg-main-0 p-4 rounded-lg">
    <!-- Sección izquierda: Título y botón de retroceso -->
    <div class="flex items-center gap-2">
      <GoBackButton v-if="showBackButton" />
      <h1 class="text-xl font-bold text-neutral-0 dark:text-neutral-0">
        {{ $t(title) }}
      </h1>
    </div>

    <!-- Sección derecha: Resumen y/o botón de acción -->
    <div class="flex items-center gap-4">
      <!-- Resumen (si se pasa summaryData con datos válidos) -->
      <div v-if="Object.keys(summaryData).length" class="flex items-center gap-4 text-sm text-neutral-0 dark:text-neutral-0">
        <span v-if="summaryData.propuestos !== undefined && summaryData.propuestos !== null">
          {{ $t('proposed') }}: <span class="text-secondary-2">{{ formatNumber(summaryData.propuestos) }}</span>
        </span>
        <span v-if="summaryData.verificados !== undefined && summaryData.verificados !== null">
          {{ $t('verified') }}: <span class="text-secondary-1">{{ formatNumber(summaryData.verificados) }}</span>
        </span>
        <span v-if="summaryData.total !== undefined && summaryData.total !== null">
          {{ $t('total') }}: <span class="text-main-1">{{ formatNumber(summaryData.total) }}</span>
        </span>
      </div>

      <!-- Botón de acción (si se pasa actionButton y tiene label) -->
      <button
        v-if="actionButton && actionButton.label"
        @click="actionButton.onClick ? actionButton.onClick() : null"
        class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors duration-200"
        :aria-label="actionButton.ariaLabel || $t(actionButton.label)"
      >
        {{ $t(actionButton.label) }}
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>