<script setup>
import NombreApp from '@/Components/NombreApp.vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';
</script>

<template>
    <LandingLayout :title="$t('Welcome')">
            
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-white dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="flex items-center justify-center h-screen">
            <div>
            <NombreApp class="block h-200 w-auto" />
                <div class="text-center">
                    <h1 class="text-red-600 font-bold text-4xl sm:text-5xl md:text-6xl lg:text-7xl">
                        {{$t('Portal under construction!')}}
                    </h1>
                </div>
            </div>
        </div>                   
    </div>

    </LandingLayout>
</template>

<style>

</style>
