<script setup>
import { Link } from '@inertiajs/vue3';
import LogoLargo from '@/assets/img/LogoLargo.png';
</script>

<template>
    <Link :href="'/'">
        <img :src="LogoLargo" alt="LogoLargo" />
    </Link>
</template>
