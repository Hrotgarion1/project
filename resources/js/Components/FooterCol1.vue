<template>
<div class="w-[255px] h-[166px] my-4 ml-4 flex-col gap-6 inline-flex">
  <div class="w-44 h-[34px] relative">
    <img :src="logoUrl" alt="Logo" class="w-[237px] h-[42px] left-0 top-[-3.5px] absolute"/>
    
  </div>
  <div class="w-64 mt-4">
    <span class="text-gray-500 text-sm font-normal font-['Arial'] leading-tight dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{$t('At ')}} </span>
    <span class="text-blue-700 text-sm font-normal font-['Arial'] leading-tight">Win to Win</span>
    <span class="text-red-600 text-sm font-normal font-['Arial'] leading-tight"> jobs</span>
    <span class="text-gray-500 text-sm font-normal font-['Arial'] leading-tight dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{$t(' we are delighted that you want to share us with your friends and acquaintances.')}}</span>
  </div>
  <div class="justify-start items-start gap-4 inline-flex">
    <SocialLinks />
  </div>
</div>
</template>

<script setup>
import SocialLinks from '@/Components/SocialLinks.vue';
const logoUrl = '/assets/img/LogoLargo.png'; // apunta a public/assets/img
</script>

<style>

</style>
