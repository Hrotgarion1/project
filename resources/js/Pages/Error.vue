<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    message: {
        type: String,
        default: 'Ocurrió un error.',
    },
    status: {
        type: Number,
        default: 500,
    },
});
</script>

<template>
    <AppLayout title="Error">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <h1 class="text-2xl font-bold text-neutral-1 dark:text-neutral-0 mb-4">Error {{ status }}</h1>
            <p class="text-neutral-2 dark:text-neutral-0">{{ message }}</p>
            <button
                @click="$inertia.visit('/skyfall/area-global')"
                class="mt-4 px-4 py-2 bg-main-0 text-neutral-0 rounded-lg hover:bg-main-1"
            >
                Volver a Área Global
            </button>
        </div>
    </AppLayout>
</template>