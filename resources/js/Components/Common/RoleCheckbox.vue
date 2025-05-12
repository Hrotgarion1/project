<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  roles: { type: Array, required: true },
  modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

// Usamos una copia local para manejar los cambios
const selectedRoles = ref([...props.modelValue]);

// Sincronizar con el valor externo cuando cambie
watch(
  () => props.modelValue,
  (newValue) => {
    selectedRoles.value = [...newValue];
  },
  { deep: true }
);

// Emitir cambios al padre cuando el usuario interactúa
watch(
  selectedRoles,
  (newRoles) => {
    if (JSON.stringify(newRoles) !== JSON.stringify(props.modelValue)) {
      emit('update:modelValue', [...newRoles]);
    }
  },
  { deep: true }
);
</script>

<template>
  <div class="space-y-2 max-h-40 overflow-y-auto border rounded p-2">
    <label v-for="role in roles" :key="role.name" class="flex items-center space-x-2">
      <input
        type="checkbox"
        :value="role.name"
        v-model="selectedRoles"
        class="rounded text-blue-600 focus:ring-blue-500"
      />
      <span>{{ role.name }}</span>
    </label>
  </div>
</template>