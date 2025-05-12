<!-- DarkMode.vue -->

<template>
  <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-500 dark:text-gray-400 hover:text-blue-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150" @click="toggleDarkMode" :aria-label="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
    <span class=" text-blue-500 hover:text-blue-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" v-if="!isDarkMode">{{$t('Change')}}</span>
    <span v-else>Dark</span>
  </button>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue';

const { isDarkMode } = defineProps(['isDarkMode']);
const emit = defineEmits();

const toggleDarkMode = () => {
  emit('toggle-dark-mode');
};
</script>

<style scoped>
button {
  color: #737070; /* Cambiado a negro para mejorar la visibilidad del emoji */
  border: none;
  padding: 10px;
  cursor: pointer;
  border-radius: 5px;
}
</style>
