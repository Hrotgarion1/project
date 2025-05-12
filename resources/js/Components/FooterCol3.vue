<template>
    <div class="w-64 h-72 flex-col my-4 ml-4 justify-start items-start gap-6 inline-flex">
  <div class="w-64 text-gray-500 text-xl font-medium font-['Inter'] dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{$t('Services')}}</div>
  <div class="flex-col justify-start items-start gap-3 flex">
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Jobs')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Marketing')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Products')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Services')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Entrepreneurship')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Investments')}}</Link></div>
  </div>
</div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>

</style>