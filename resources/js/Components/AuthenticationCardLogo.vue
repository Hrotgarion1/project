<script setup>
import { Link } from '@inertiajs/vue3';
const logoUrl = '/assets/img/LogoLargo.png'; // apunta a public/assets/img
</script>

<template>
    <Link :href="'/'">
        <img :src="logoUrl" alt="Logo" />
    </Link>
</template>
