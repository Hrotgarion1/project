<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import NombreApp from '@/Components/NombreApp.vue';
import IdentityRequestButton from '@/Components/Common/IdentityRequestButton.vue';
</script>

<template>
    <AppLayout :title="$t('Social Entities')">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-white dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            <div class="flex items-center justify-center h-screen">
                <div>
                <NombreApp class="block h-200 w-auto" />
                    <div class="text-center">
                        <h1 class="text-red-600 font-bold text-4xl sm:text-5xl md:text-6xl lg:text-7xl">
                            {{$t('Social Entities')}}
                        </h1>
                        <IdentityRequestButton type="tipo C" label="Solicitar Identidad tipo C" class="mt-5" />
                    </div>
                </div>
            </div>                   
        </div>
    </AppLayout>
</template>

<style scoped>

</style>