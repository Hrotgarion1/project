<template>
    <draggable v-model="items" item-key="id" class="flex gap-2">
      <template #item="{ element }">
        <div class="p-2 bg-gray-200">{{ element.name }}</div>
      </template>
    </draggable>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const items = ref([
    { id: 1, name: 'Item 1' },
    { id: 2, name: 'Item 2' },
    { id: 3, name: 'Item 3' },
  ]);
  </script>