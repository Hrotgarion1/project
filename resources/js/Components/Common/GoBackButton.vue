<script setup>
import { router } from "@inertiajs/vue3";

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route("dahsboard"));
    }
};
</script>

<template>
    <button
        @click="goBack"
        class="flex items-center justify-center w-8 h-8 rounded-full bg-white border-2 border-blue-700 hover:bg-gray-100 transition"
    >
        <svg
            class="w-5 h-5 text-blue-700"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
            ></path>
        </svg>
    </button>
</template>