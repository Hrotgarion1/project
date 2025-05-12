<template>
<div class="w-64 h-56 my-4 ml-4 flex-col justify-start items-start gap-6 inline-flex ">
  <div class="w-64 text-gray-500 text-xl font-normal font-['Arial'] dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{$t('General')}}</div>
  <div class="flex-col justify-start items-start gap-3 flex">
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Help Center')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Blog')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Countries')}}</Link></div>
    <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
      <Link >{{$t('Press')}}</Link></div>
    <div class="justify-start items-start gap-2 inline-flex">
      <div class="w-56 base-text base-link focus:outline focus:rounded-sm base-focus">
        <Link >{{$t('Jobs')}}</Link></div>
      <!-- <div class="text-red-600 text-sm font-normal font-['Nanum Pen']"> {{$t('!We are hiring!')}}</div> -->
    </div>
  </div>
</div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>

</style>