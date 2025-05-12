<template>
    <div class="flex justify-between">
    <Link class="text-blue-500 hover:text-blue-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" :href="route('login')">{{$t('Log in')}}</Link>
    <Link class="text-green-500 hover:text-green-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" :href="route('register')">{{$t('Register')}}</Link>
  </div>
    
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped >

</style>