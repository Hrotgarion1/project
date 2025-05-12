<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    buttonText: String,
    menuItems: Array,
});

const isOpen = ref(false);

const toggle = () => {
    isOpen.value = !isOpen.value;
};
</script>

<template>
    <div class="mb-2 border-neutral-4 dark:border-neutral-2">
        <button
            @click="toggle"
            class="w-full flex items-center px-4 py-2 text-sm sm:text-base font-medium text-neutral-1 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 hover:bg-main-0 hover:text-neutral-0 dark:hover:bg-main-0 dark:hover:text-neutral-0 rounded-lg border border-neutral-4 dark:border-neutral-2 transition-colors duration-200"
        >
            <i class="fa fa-bars"></i>
            <span class="ms-3 truncate">{{ buttonText }}</span>
        </button>
        <ul v-if="isOpen" class="space-y-1 mt-1 pl-4">
            <li v-for="(item, index) in menuItems" :key="index">
                <Link
                    :href="route(item.route)"
                    :class="[
                        'flex items-center px-4 py-2 text-sm sm:text-base truncate text-main-1 dark:text-main-1 hover:bg-neutral-3 dark:hover:bg-neutral-1 hover:text-neutral-1 dark:hover:text-neutral-0 rounded-md transition-colors duration-200',
                        { 'bg-main-1 text-neutral-0 dark:bg-main-1 dark:text-neutral-0': route().current(item.route) }
                    ]"
                >
                    {{ item.label }}
                </Link>
            </li>
        </ul>
    </div>
</template>