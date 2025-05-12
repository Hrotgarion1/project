<script setup>
  import { ref, onMounted } from 'vue';
  import { Link } from '@inertiajs/vue3';

  const currentYear = ref(new Date().getFullYear());
  
  // Actualizar el año cada vez que el componente se monta
  onMounted(() => {
    updateYear();
  });
  
  // Función para actualizar el año
  const updateYear = () => {
    currentYear.value = new Date().getFullYear();
  };
  </script>
  
<template>
    <div class="grid lg:grid-cols-2 md:grid-cols-2 sm:grid-cols-1">
      
      <div class="ml-4 grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1">
        <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
          <Link >{{$t('Terms of Service')}}</Link>
        </div>
        
        <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
          <Link >{{$t('Privacy Policy')}}</Link>
        </div>
        
        <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
          <Link >{{$t('Platform rules')}}</Link>  
        </div>
        
        <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
          <Link >{{$t('About')}}</Link>
        </div>
      </div>

      <div class=" ml-4 mb-4">
        <span class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
          ©{{ currentYear }}
        </span>
        <span class="text-blue-700 text-sm font-normal font-['Inter'] ">
          Win to Win
        </span>
        <span class="text-red-600 text-sm font-normal font-['Inter'] ">
          jobs
        </span>
        <span class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
          . All rights reserved
        </span>
      </div>
    </div>
  </template>

<style scoped>

</style>